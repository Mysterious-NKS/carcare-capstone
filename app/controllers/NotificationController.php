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

        if ($user_id <= 0 || $title === '' || $body === '') {
            $_SESSION['flash'] = ['err' => 'Please fill all fields.'];
            return $this->redirect('staff/interactions');
        }

        $ins = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, is_read, created_at)
                              VALUES (?,?,?,?,0,NOW())");
        $ins->execute([$user_id, $type, $title, $body]);

        $_SESSION['flash'] = ['ok' => 'Message sent.'];
        return $this->redirect('staff/interactions');
    }
}
