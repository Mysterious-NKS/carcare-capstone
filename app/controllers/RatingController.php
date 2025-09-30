<?php

class RatingController extends Controller
{
    // GET /feedback
    public function index()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $hasNotif = $pdo->prepare("SELECT 1 FROM information_schema.tables
                                   WHERE table_schema=DATABASE() AND table_name='notifications' LIMIT 1");
        $hasNotif->execute();
        $notifOk = (bool)$hasNotif->fetchColumn();

        $sql = "SELECT a.id,
                       a.scheduled_at,
                       s.name AS service_name,
                       v.make, v.model, v.year, v.plate_no,
                       r.stars, r.comment
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                LEFT JOIN ratings r ON r.appointment_id=a.id
                WHERE a.customer_id=? AND a.status='COMPLETED'
                ORDER BY a.scheduled_at DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$uid]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Attach latest staff reply (if notifications table exists)
        if ($notifOk && $rows) {
            $ns = $pdo->prepare("
                SELECT body FROM notifications
                WHERE user_id = ? AND type='FEEDBACK_REPLY' AND title LIKE ?
                ORDER BY created_at DESC
                LIMIT 1
            ");
            foreach ($rows as &$row) {
                $like = "%#".$row['id']."%";
                $ns->execute([$uid, $like]);
                $row['staff_reply'] = (string)($ns->fetchColumn() ?: '');
            }
            unset($row);
        }

        $this->renderAny(['customer/feedback.php', 'feedback/index.php'], ['rows' => $rows]);
    }

    // POST /feedback
    public function store()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        $stars = max(1, min(5, (int)($_POST['stars'] ?? 0)));
        $comment = trim($_POST['comment'] ?? '');
        $returnTo = trim($_POST['return_to'] ?? '');

        $check = $pdo->prepare("SELECT id FROM appointments WHERE id=? AND customer_id=? AND status='COMPLETED'");
        $check->execute([$appointment_id, $uid]);
        if (!$check->fetch()) {
            $_SESSION['flash'] = ['err' => 'Invalid appointment.'];
            return $this->redirect($returnTo !== '' ? $returnTo : 'feedback');
        }

        $exists = $pdo->prepare("SELECT id FROM ratings WHERE appointment_id=?");
        $exists->execute([$appointment_id]);
        if ($exists->fetchColumn()) {
            $up = $pdo->prepare("UPDATE ratings SET stars=?, comment=?, created_at=NOW() WHERE appointment_id=?");
            $up->execute([$stars, $comment, $appointment_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO ratings (appointment_id, staff_id, stars, comment, created_at)
                                  VALUES (?, NULL, ?, ?, NOW())");
            $ins->execute([$appointment_id, $stars, $comment]);
        }

        $_SESSION['flash'] = ['ok' => 'Thanks for your feedback!'];
        $this->redirect($returnTo !== '' ? $returnTo : 'feedback');
    }
}
