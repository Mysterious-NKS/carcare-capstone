<?php

class StaffController extends Controller
{
    /** quick helpers */
    private function pdo(): PDO { return DB::pdo(); }
    private function uid(): int  { return (int)Auth::id(); }

    /** flexible helpers */
    private function tableExists(PDO $pdo, string $table): bool {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1");
        $q->execute([$table]);
        return (bool)$q->fetchColumn();
    }
    private function colExists(PDO $pdo, string $table, string $col): bool {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
                            LIMIT 1");
        $q->execute([$table, $col]);
        return (bool)$q->fetchColumn();
    }

    /** GET /staff (Dashboard) */
    public function dashboard()
    {
        Auth::requireRole('STAFF');
        $pdo = $this->pdo();
        $sid = $this->uid();

        // today's range
        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');

        // Appointments for this staff today (list for "Today's Tasks")
        $today = [];
        if ($this->colExists($pdo, 'appointments', 'staff_id')) {
            $st = $pdo->prepare("
                SELECT a.id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles  v ON v.id=a.vehicle_id
                WHERE a.staff_id = ? AND a.scheduled_at BETWEEN ? AND ?
                ORDER BY a.scheduled_at ASC
            ");
            $st->execute([$sid, $start, $end]);
            $today = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Recent notifications for this staff (tiny list)
        $notes = [];
        if ($this->tableExists($pdo, 'notifications')) {
            $st = $pdo->prepare("SELECT id,title,body,is_read,created_at
                                 FROM notifications
                                 WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
            $st->execute([$sid]);
            $notes = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Recent customer feedback (tiny list)
        $feedback = [];
        if ($this->colExists($pdo, 'appointments', 'staff_id') && $this->tableExists($pdo, 'ratings')) {
            $st = $pdo->prepare("
                SELECT r.stars, r.comment, a.id AS appointment_id,
                       s.name AS service_name, a.scheduled_at,
                       v.year, v.make, v.model, v.plate_no
                FROM ratings r
                JOIN appointments a ON a.id=r.appointment_id
                JOIN services     s ON s.id=a.service_id
                JOIN vehicles     v ON v.id=a.vehicle_id
                WHERE a.staff_id=? ORDER BY r.created_at DESC LIMIT 5
            ");
            $st->execute([$sid]);
            $feedback = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Metrics
        $metrics = ['todayTasks'=>0,'upcoming'=>0,'feedback'=>0,'urgent'=>0];

        // Also gather a tiny "next up" list for the dashboard (max 3)
        $upcomingMini = [];
        if ($this->colExists($pdo, 'appointments', 'staff_id')) {
            // today count
            $c = $pdo->prepare("SELECT COUNT(*) FROM appointments a
                                WHERE a.staff_id=? AND a.scheduled_at BETWEEN ? AND ?");
            $c->execute([$sid, $start, $end]);
            $metrics['todayTasks'] = (int)$c->fetchColumn();

            // upcoming count + list
            $c = $pdo->prepare("SELECT COUNT(*) FROM appointments a
                                WHERE a.staff_id=? AND a.scheduled_at > ?");
            $c->execute([$sid, $end]);
            $metrics['upcoming'] = (int)$c->fetchColumn();

            $mini = $pdo->prepare("
                SELECT a.id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.staff_id=? AND a.scheduled_at > NOW()
                ORDER BY a.scheduled_at ASC
                LIMIT 3
            ");
            $mini->execute([$sid]);
            $upcomingMini = $mini->fetchAll(PDO::FETCH_ASSOC);

            // urgent (today only)
            $c = $pdo->prepare("SELECT COUNT(*) FROM appointments a
                                WHERE a.staff_id=? 
                                  AND a.scheduled_at BETWEEN ? AND ?
                                  AND a.status IN ('DELAYED','PENDING')");
            $c->execute([$sid, $start, $end]);
            $metrics['urgent'] = (int)$c->fetchColumn();
        }

        // Pending feedback = ratings without a FEEDBACK_REPLY notification that references the appt id
        if ($this->tableExists($pdo,'ratings') && $this->colExists($pdo,'appointments','staff_id')
            && $this->tableExists($pdo,'notifications')) {
            $q = $pdo->prepare("
                SELECT COUNT(*)
                FROM ratings r
                JOIN appointments a ON a.id = r.appointment_id
                WHERE a.staff_id = ?
                  AND NOT EXISTS (
                    SELECT 1 FROM notifications n
                    WHERE n.user_id = a.customer_id
                      AND n.type = 'FEEDBACK_REPLY'
                      AND n.title LIKE CONCAT('%#', a.id, '%')
                  )
            ");
            $q->execute([$sid]);
            $metrics['feedback'] = (int)$q->fetchColumn();
        } else {
            // fallback to previous behaviour
            $metrics['feedback'] = count($feedback);
        }

        // Tile targets
        $links = [
            'todayTasks' => url('staff/schedule'),
            'feedback'   => url('staff/interactions'),
            'upcoming'   => url('staff/schedule'),
            'urgent'     => url('staff/schedule'),
        ];

        $this->render('staff/dashboard.php', [
            'today'        => $today,
            'notes'        => $notes,
            'feedback'     => $feedback,
            'metrics'      => $metrics,
            'links'        => $links,
            'upcomingMini' => $upcomingMini,
        ]);
    }

    /** GET /staff/interactions (Customer Interaction) */
    public function interactions()
    {
        Auth::requireRole('STAFF');
        $pdo = $this->pdo();
        $sid = $this->uid();

        // Ratings for appointments handled by this staff
        $rows = [];
        if ($this->tableExists($pdo, 'ratings') && $this->colExists($pdo,'appointments','staff_id')) {
            $st = $pdo->prepare("
                SELECT
                    r.stars,
                    r.comment,
                    r.created_at,
                    a.id AS appointment_id,
                    a.scheduled_at,
                    a.customer_id,
                    s.name AS service_name,
                    v.year, v.make, v.model, v.plate_no,
                    /* latest staff reply body, if any */
                    (
                      SELECT n.body
                      FROM notifications n
                      WHERE n.user_id = a.customer_id
                        AND n.type = 'FEEDBACK_REPLY'
                        AND n.title LIKE CONCAT('%#', a.id, '%')
                      ORDER BY n.created_at DESC
                      LIMIT 1
                    ) AS staff_reply
                FROM ratings r
                JOIN appointments a ON a.id=r.appointment_id
                JOIN services     s ON s.id=a.service_id
                JOIN vehicles     v ON v.id=a.vehicle_id
                WHERE a.staff_id=?
                ORDER BY r.created_at DESC
            ");
            $st->execute([$sid]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Customers to send reminders to
        $customers = [];
        if ($this->colExists($pdo, 'appointments', 'staff_id')) {
            $nameCol = $this->colExists($pdo, 'users', 'full_name')
                      ? 'u.full_name'
                      : ($this->colExists($pdo, 'users', 'name') ? 'u.name' : "CONCAT('User ', u.id)");
            $sql = "
                SELECT DISTINCT u.id, $nameCol AS name
                FROM appointments a
                JOIN users u ON u.id = a.customer_id
                WHERE a.staff_id = ?
                ORDER BY name
            ";
            $st = $pdo->prepare($sql);
            $st->execute([$sid]);
            $customers = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Due maintenance reminders (optional)
        $dueReminders = [];
        if ($this->tableExists($pdo, 'reminders') && $this->colExists($pdo,'appointments','staff_id')) {
            $nameCol = $this->colExists($pdo, 'users', 'full_name')
                      ? 'u.full_name'
                      : ($this->colExists($pdo, 'users', 'name') ? 'u.name' : "CONCAT('User ', u.id)");

            $sql = "
                SELECT r.id AS reminder_id, r.status, r.due_date, r.due_mileage,
                       v.id AS vehicle_id, v.make, v.model, v.year, v.plate_no, v.mileage,
                       u.id AS user_id, $nameCol AS user_name
                FROM reminders r
                JOIN vehicles v ON v.id = r.vehicle_id
                JOIN users u    ON u.id = v.user_id
                WHERE r.status = 'DUE'
                  AND (
                        (r.due_date    IS NOT NULL AND r.due_date    <= CURDATE())
                     OR (r.due_mileage IS NOT NULL AND v.mileage IS NOT NULL AND r.due_mileage <= v.mileage)
                  )
                  AND EXISTS (
                    SELECT 1 FROM appointments a
                    WHERE a.customer_id = u.id AND a.staff_id = ?
                  )
                ORDER BY COALESCE(r.due_date, '9999-12-31') ASC, r.id ASC
                LIMIT 25
            ";
            $st = $pdo->prepare($sql);
            $st->execute([$sid]);
            $dueReminders = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('staff/interactions.php', [
            'rows'         => $rows,
            'customers'    => $customers,
            'dueReminders' => $dueReminders,
        ]);
    }

    /** GET /staff/workflow (Service Workflow Management) */
    public function workflow()
    {
        Auth::requireRole('STAFF');
        $pdo = $this->pdo();
        $sid = $this->uid();

        $apps = [];
        if ($this->colExists($pdo, 'appointments', 'staff_id')) {
            $st = $pdo->prepare("
                SELECT a.id, a.status, a.scheduled_at,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.staff_id=? AND a.status IN ('PENDING','CONFIRMED','IN_PROGRESS','DELAYED')
                ORDER BY a.scheduled_at ASC
            ");
            $st->execute([$sid]);
            $apps = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        $selected = null; $record = null;
        $selId = (int)($_GET['appointment_id'] ?? 0);
        if ($selId > 0) {
            $st = $pdo->prepare("
                SELECT a.id, a.status, a.scheduled_at, a.customer_id,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.id=? AND a.staff_id=?
            ");
            $st->execute([$selId, $sid]);
            $selected = $st->fetch(PDO::FETCH_ASSOC);

            if ($selected && $this->tableExists($pdo, 'service_records')) {
                $st = $pdo->prepare("SELECT * FROM service_records WHERE appointment_id=? LIMIT 1");
                $st->execute([$selId]);
                $record = $st->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        }

        $this->render('staff/workflow.php', [
            'apps'     => $apps,
            'selected' => $selected,
            'record'   => $record
        ]);
    }

    /** GET /staff/schedule (Appointment Management) */
    public function schedule()
    {
        Auth::requireRole('STAFF');
        $pdo = $this->pdo();
        $sid = $this->uid();

        // choose customer display column safely
        $custName = $this->colExists($pdo, 'users', 'full_name')
                  ? 'u.full_name'
                  : ($this->colExists($pdo, 'users', 'name') ? 'u.name' : "CONCAT('User ', u.id)");

        // Today's schedule
        $start = date('Y-m-d 00:00:00'); $end = date('Y-m-d 23:59:59');
        $today = [];
        if ($this->colExists($pdo,'appointments','staff_id')) {
            $st = $pdo->prepare("
                SELECT a.id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no,
                       $custName AS customer_name
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                LEFT JOIN users u ON u.id=a.customer_id
                WHERE a.staff_id=? AND a.scheduled_at BETWEEN ? AND ?
                ORDER BY a.scheduled_at ASC
            ");
            $st->execute([$sid, $start, $end]);
            $today = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Upcoming
        $upcoming = [];
        if ($this->colExists($pdo,'appointments','staff_id')) {
            $st = $pdo->prepare("
                SELECT a.id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.staff_id=? AND a.scheduled_at > NOW()
                ORDER BY a.scheduled_at ASC
                LIMIT 25
            ");
            $st->execute([$sid]);
            $upcoming = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // Pending booking requests
        $pending = [];
        if ($this->colExists($pdo,'appointments','staff_id')) {
            $st = $pdo->prepare("
                SELECT a.id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.staff_id=? AND a.status='PENDING'
                ORDER BY a.scheduled_at ASC
                LIMIT 25
            ");
            $st->execute([$sid]);
            $pending = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('staff/schedule.php', [
            'today'    => $today,
            'upcoming' => $upcoming,
            'pending'  => $pending
        ]);
    }
}
