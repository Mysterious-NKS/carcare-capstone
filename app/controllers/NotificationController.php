<?php
class NotificationController extends Controller
{
    // GET /notifications
    public function index()
    {
        Auth::requireLogin();
        $uid = Auth::id();
        $pdo = DB::pdo();

        $sql = "SELECT id, title, body, type, is_read, created_at
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$uid]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        $this->renderAny(['customer/notifications.php', 'notifications.php'], ['items' => $items]);
    }

    // POST /notifications/mark-read
    public function markRead()
    {
        Auth::requireLogin();
        $uid = Auth::id();
        $pdo = DB::pdo();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
            $st->execute([$id, $uid]);
        } else {
            $st = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
            $st->execute([$uid]);
        }

        $_SESSION['flash'] = ['ok' => $id ? 'Notification marked read.' : 'All notifications marked read.'];
        $this->redirect('notifications');
    }

    // POST /notifications/custom  (generic: in-app, feedback reply, maintenance reminder)
    public function custom()
    {
        Auth::requireRole('STAFF');
        $pdo = DB::pdo();

        // table guard
        $chk = $pdo->prepare("SELECT 1 FROM information_schema.tables
                              WHERE table_schema=DATABASE() AND table_name='notifications' LIMIT 1");
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $_SESSION['flash'] = ['err' => 'Notifications are not enabled.'];
            return $this->redirect('staff/interactions');
        }

        $user_id = (int)($_POST['user_id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $body    = trim($_POST['body'] ?? '');

        // optional, but safely whitelisted
        $type = strtoupper(trim($_POST['type'] ?? 'IN_APP'));
        $allowed = ['IN_APP','FEEDBACK_REPLY','MAINTENANCE_REMINDER'];
        if (!in_array($type, $allowed, true)) $type = 'IN_APP';

        // common validation
        if ($user_id <= 0 || $title === '' || $body === '') {
            $_SESSION['flash'] = ['err' => 'Please fill all fields.'];
            return $this->redirect('staff/interactions');
        }
        if (mb_strlen($body) > 2000) {
            $_SESSION['flash'] = ['err' => 'Message is too long (max 2000 characters).'];
            return $this->redirect('staff/interactions');
        }

        // Extra guardrails for FEEDBACK_REPLY
        if ($type === 'FEEDBACK_REPLY') {
            $apptId = (int)($_POST['appointment_id'] ?? 0);
            if ($apptId <= 0) {
                $_SESSION['flash'] = ['err' => 'Missing appointment id for feedback reply.'];
                return $this->redirect('staff/interactions');
            }

            // Ensure: current staff owns that appointment, and the user_id matches the appointment's customer
            $st = $pdo->prepare("SELECT a.customer_id
                                 FROM appointments a
                                 WHERE a.id = ? AND a.staff_id = ? LIMIT 1");
            $st->execute([$apptId, Auth::id()]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $_SESSION['flash'] = ['err' => 'You can only reply to feedback on your own appointments.'];
                return $this->redirect('staff/interactions');
            }
            if ((int)$row['customer_id'] !== $user_id) {
                $_SESSION['flash'] = ['err' => 'Selected customer does not match the appointment.'];
                return $this->redirect('staff/interactions');
            }

            // Normalize title so customer/metrics queries remain fast & consistent
            $title = "Reply for appointment #".$apptId;

            // Optional de-dupe (avoid accidental double posts on fast clicks)
            $dupe = $pdo->prepare("
                SELECT 1 FROM notifications
                WHERE user_id=? AND type='FEEDBACK_REPLY'
                  AND title LIKE CONCAT('%#', ?, '%')
                  AND body = ?
                ORDER BY id DESC LIMIT 1
            ");
            $dupe->execute([$user_id, $apptId, $body]);
            if ($dupe->fetchColumn()) {
                $_SESSION['flash'] = ['ok' => 'Reply already sent.'];
                return $this->redirect('staff/interactions');
            }
        }

        $ins = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, is_read, created_at)
                              VALUES (?,?,?,?,0,NOW())");
        $ins->execute([$user_id, $type, $title, $body]);

        $_SESSION['flash'] = ['ok' => 'Message sent.'];
        return $this->redirect('staff/interactions');
    }
}
