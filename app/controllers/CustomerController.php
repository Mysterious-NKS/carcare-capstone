<?php

class CustomerController extends Controller
{
    private function uid(): int {
        if (!isset($_SESSION['user'])) $this->redirect('login');
        return (int)$_SESSION['user']['id'];
    }

    // helpers
    private function tableExists(PDO $pdo, string $table): bool {
        $q = $pdo->prepare("
            SELECT 1 FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1
        ");
        $q->execute([$table]);
        return (bool)$q->fetchColumn();
    }
    private function colExists(PDO $pdo, string $table, string $col): bool {
        $q = $pdo->prepare("
          SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME   = ?
            AND COLUMN_NAME  = ?
          LIMIT 1
        ");
        $q->execute([$table, $col]);
        return (bool)$q->fetchColumn();
    }

    public function dashboard()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        // -----------------------------
        // Metrics (safe & lightweight)
        // -----------------------------
        // Upcoming = future bookings in active-ish states we actually use
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments a
            WHERE a.customer_id = ?
              AND a.status IN ('PENDING','CONFIRMED','IN_PROGRESS','DELAYED')
              AND a.scheduled_at >= NOW()
        ");
        $stmt->execute([$uid]);
        $upcoming = (int)$stmt->fetchColumn();

        // Vehicles count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v WHERE v.user_id = ?");
        $stmt->execute([$uid]);
        $vehicleCount = (int)$stmt->fetchColumn();

        // Maintenance due (optional table)
        $due = 0;
        if ($this->tableExists($pdo, 'reminders')) {
            // if mileage column doesn’t exist, skip that part
            $hasMileage = $this->colExists($pdo, 'vehicles', 'mileage');
            $sql = "
                SELECT COUNT(*)
                FROM reminders r
                JOIN vehicles v ON v.id = r.vehicle_id
                WHERE v.user_id = ?
                  AND r.status = 'DUE'
                  AND (
                        (r.due_date IS NOT NULL   AND r.due_date   <= CURDATE())
                     " . ($hasMileage ? " OR (r.due_mileage IS NOT NULL AND r.due_mileage <= v.mileage)" : "") . "
                  )
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$uid]);
            $due = (int)$stmt->fetchColumn();
        }

        // -----------------------------
        // Recent appointments (latest 10)
        // -----------------------------
        $stmt = $pdo->prepare("
            SELECT  a.id,
                    a.status,
                    a.scheduled_at,
                    s.name      AS service_name,
                    v.make, v.model, v.year, v.plate_no
            FROM appointments a
            JOIN services     s ON s.id = a.service_id
            JOIN vehicles     v ON v.id = a.vehicle_id
            WHERE a.customer_id = ?
            ORDER BY a.scheduled_at DESC
            LIMIT 10
        ");
        $stmt->execute([$uid]);
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------------
        // Upcoming mini list (next 5)
        // -----------------------------
        $upcomingMini = [];
        $stmt = $pdo->prepare("
            SELECT  a.id, a.scheduled_at, a.status,
                    s.name AS service_name,
                    v.year, v.make, v.model, v.plate_no
            FROM appointments a
            JOIN services s ON s.id=a.service_id
            JOIN vehicles v ON v.id=a.vehicle_id
            WHERE a.customer_id=? AND a.scheduled_at >= NOW()
            ORDER BY a.scheduled_at ASC
            LIMIT 5
        ");
        $stmt->execute([$uid]);
        $upcomingMini = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------------
        // Notifications (top 5, if table exists)
        // -----------------------------
        $notes = [];
        if ($this->tableExists($pdo, 'notifications')) {
            $st = $pdo->prepare("
                SELECT id,title,body,is_read,created_at
                FROM notifications
                WHERE user_id=?
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $st->execute([$uid]);
            $notes = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('customer/dashboard.php', [
            'metrics' => [
                'upcoming' => $upcoming,
                'vehicles' => $vehicleCount,
                'due'      => $due,
            ],
            'recent'       => $recent,
            'upcomingMini' => $upcomingMini,
            'notes'        => $notes,
        ]);
    }
}
