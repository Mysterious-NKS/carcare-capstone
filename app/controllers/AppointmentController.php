<?php

class AppointmentController extends Controller
{
    /* ---------- helpers ---------- */

    private function uid(): int {
        if (!isset($_SESSION['user'])) $this->redirect('login');
        return (int)$_SESSION['user']['id'];
    }

    private function tableExists(PDO $pdo, string $table): bool {
        $q = $pdo->prepare("
            SELECT 1
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
            LIMIT 1
        ");
        $q->execute([$table]);
        return (bool)$q->fetchColumn();
    }

    private function colExists(PDO $pdo, string $table, string $col): bool {
        $q = $pdo->prepare("
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
            LIMIT 1
        ");
        $q->execute([$table, $col]);
        return (bool)$q->fetchColumn();
    }

    // Render whichever view file exists (new or old paths)
    private function renderAny(array $candidates, array $data = []): void {
        $base = dirname(__DIR__).'/views/';
        foreach ($candidates as $rel) {
            if (is_file($base.$rel)) {
                $this->render($rel, $data);
                return;
            }
        }
        // fallback to first (will error if not present, but keeps behavior consistent)
        $this->render($candidates[0], $data);
    }

    /* ---------- actions ---------- */

    // GET /appointments
    public function index()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        // Build SELECT dynamically so we don't reference missing tables/columns
        $select = "
            SELECT a.id, a.status, a.scheduled_at,
                   svc.name AS service_name,
                   v.year, v.make, v.model, v.plate_no
        ";
        $joins = "
            FROM appointments a
            JOIN services  svc ON svc.id = a.service_id
            JOIN vehicles  v   ON v.id   = a.vehicle_id
        ";

        // Optionally join staff or users table for staff name
        if ($this->tableExists($pdo, 'staff') && $this->colExists($pdo, 'appointments', 'staff_id')) {
            $select .= ", s.name AS staff_name";
            $joins  .= " LEFT JOIN staff s ON s.id = a.staff_id ";
        } elseif (
            $this->tableExists($pdo, 'users')
            && $this->colExists($pdo, 'users', 'full_name')
            && $this->colExists($pdo, 'appointments', 'staff_id')
        ) {
            $select .= ", u.full_name AS staff_name";
            $joins  .= " LEFT JOIN users u ON u.id = a.staff_id ";
        } else {
            $select .= ", '' AS staff_name";
        }

        $sql = $select.$joins." WHERE a.customer_id = ? ORDER BY a.scheduled_at DESC";
        $st  = $pdo->prepare($sql);
        $st->execute([$uid]);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        // New path first, then legacy
        $this->renderAny(
            ['appointments/index.php', 'customer/appointments.php'],
            ['items' => $items]
        );
    }

    // GET /appointments/create
    public function create()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $v = $pdo->prepare("
            SELECT id, year, make, model, plate_no
            FROM vehicles
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $v->execute([$uid]);
        $vehicles = $v->fetchAll(PDO::FETCH_ASSOC);

        $services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        // Optional staff list (if a suitable table exists)
        $staff = [];
        if ($this->tableExists($pdo, 'staff') && $this->colExists($pdo, 'staff', 'name')) {
            $staff = $pdo->query("SELECT id, name FROM staff ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        } elseif (
            $this->tableExists($pdo, 'users')
            && $this->colExists($pdo, 'users', 'full_name')
            && $this->colExists($pdo, 'users', 'role')
        ) {
            $staffSt = $pdo->prepare("SELECT id, full_name AS name FROM users WHERE role = 'STAFF' ORDER BY full_name");
            $staffSt->execute();
            $staff = $staffSt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAny(
            ['appointments/create.php', 'customer/appointment_form.php'],
            ['vehicles' => $vehicles, 'services' => $services, 'staff' => $staff]
        );
    }

    // POST /appointments/create
    public function store()
    {
        Auth::requireRole('CUSTOMER');
        $uid  = Auth::id();
        $pdo  = DB::pdo();

        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $service_id = (int)($_POST['service_id'] ?? 0);

        // accept scheduled_at or (date + time)
        $when = trim($_POST['scheduled_at'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        if ($when === '' && $date !== '' && $time !== '') {
            $when = "$date $time:00";
        }

        $staff_id = isset($_POST['staff_id']) && $_POST['staff_id'] !== '' ? (int)$_POST['staff_id'] : null;
        $notes    = trim($_POST['notes'] ?? '');

        if ($vehicle_id <= 0 || $service_id <= 0 || $when === '') {
            $_SESSION['flash'] = ['err' => 'Please fill all required fields.'];
            return $this->redirect('appointments/create');
        }

        // ensure the vehicle belongs to the user
        $chk = $pdo->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
        $chk->execute([$vehicle_id, $uid]);
        if (!$chk->fetch()) {
            $_SESSION['flash'] = ['err' => 'Invalid vehicle selected.'];
            return $this->redirect('appointments/create');
        }

        // portable INSERT (only include cols that exist)
        $cols  = ['customer_id','vehicle_id','service_id','scheduled_at','status'];
        $vals  = [$uid, $vehicle_id, $service_id, $when, 'PENDING'];
        $marks = '?,?,?,?,?';

        if ($this->colExists($pdo, 'appointments', 'staff_id') && $staff_id) {
            $cols[]  = 'staff_id';
            $vals[]  = $staff_id;
            $marks  .= ',?';
        }
        if ($this->colExists($pdo, 'appointments', 'notes') && $notes !== '') {
            $cols[]  = 'notes';
            $vals[]  = $notes;
            $marks  .= ',?';
        }

        $sql = "INSERT INTO appointments (".implode(',', $cols).") VALUES ($marks)";
        $st  = $pdo->prepare($sql);
        $st->execute($vals);

        $_SESSION['flash'] = ['ok' => 'Appointment created.'];
        return $this->redirect('appointments');
    }

    // GET /appointments/show?id=123
    public function show()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();
        $id  = (int)($_GET['id'] ?? 0);

        $select = "
            SELECT a.id, a.status, a.scheduled_at,
                   svc.name AS service_name,
                   v.make, v.model, v.year, v.plate_no
        ";
        $joins = "
            FROM appointments a
            JOIN services  svc ON  svc.id = a.service_id
            JOIN vehicles  v   ON  v.id   = a.vehicle_id
        ";

        if ($this->tableExists($pdo, 'staff') && $this->colExists($pdo, 'appointments', 'staff_id')) {
            $select .= ", s.name AS staff_name";
            $joins  .= " LEFT JOIN staff s ON s.id = a.staff_id ";
        } elseif (
            $this->tableExists($pdo, 'users')
            && $this->colExists($pdo, 'users', 'full_name')
            && $this->colExists($pdo, 'appointments', 'staff_id')
        ) {
            $select .= ", u.full_name AS staff_name";
            $joins  .= " LEFT JOIN users u ON u.id = a.staff_id ";
        } else {
            $select .= ", '' AS staff_name";
        }

        $sql = $select.$joins." WHERE a.id = ? AND a.customer_id = ? LIMIT 1";
        $st  = $pdo->prepare($sql);
        $st->execute([$id, $uid]);
        $a = $st->fetch(PDO::FETCH_ASSOC);

        if (!$a) return $this->redirect('appointments');

        $this->renderAny(
            ['appointments/show.php', 'customer/appointment_show.php'],
            ['a' => $a]
        );
    }

    // POST /appointments/cancel
    public function cancel()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare("UPDATE appointments SET status='CANCELLED' WHERE id=? AND customer_id=?");
            $st->execute([$id, $uid]);
        }
        $this->redirect('appointments');
    }
}
