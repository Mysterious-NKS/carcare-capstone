<?php

class CustomerController extends Controller
{
    private function uid(): int {
        if (!isset($_SESSION['user'])) $this->redirect('login');
        return (int)$_SESSION['user']['id'];
    }

    // tiny helper to detect columns safely
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

        // 1) metrics
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments a
            WHERE a.customer_id = ?
              AND a.status IN ('PENDING','APPROVED','IN_PROGRESS','WAITING_PARTS')
              AND a.scheduled_at >= NOW()
        ");
        $stmt->execute([$uid]);
        $upcoming = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v WHERE v.user_id = ?");
        $stmt->execute([$uid]);
        $vehicleCount = (int)$stmt->fetchColumn();

        // 'Maintenance Due' = any reminder that is due by date or mileage for this user's vehicles
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM reminders r
            JOIN vehicles v ON v.id = r.vehicle_id
            WHERE v.user_id = ?
              AND r.status = 'DUE'
              AND (
                    (r.due_date IS NOT NULL   AND r.due_date   <= CURDATE())
                 OR (r.due_mileage IS NOT NULL AND r.due_mileage <= v.mileage)
              )
        ");
        $stmt->execute([$uid]);
        $due = (int)$stmt->fetchColumn();

        // 2) recent activity (latest 10 appointments)
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

        $this->render('customer/dashboard.php', [
            'metrics' => [
                'upcoming' => $upcoming,
                'vehicles' => $vehicleCount,
                'due'      => $due,
            ],
            'recent' => $recent,
        ]);
    }
}
