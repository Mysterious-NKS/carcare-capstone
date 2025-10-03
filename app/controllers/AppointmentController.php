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

    /* ---------- actions (CUSTOMER) ---------- */

    // ...file header stays the same...

    // GET /appointments
    public function index()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        // --------- incoming filters (GET) ----------
        $q       = trim($_GET['q'] ?? '');
        $sort    = trim($_GET['sort'] ?? 'new');              // new|old|status|service
        $statusG = $_GET['status'] ?? [];                     // array from checkboxes
        if (!is_array($statusG)) {                            // if a single value came through
            $statusG = $statusG !== '' ? [$statusG] : [];
        }
        // Whitelist of allowed statuses we actually display
        $allowedStatuses = [
            'PENDING','APPROVED','CONFIRMED','IN_PROGRESS','WAITING_PARTS',
            'DELAYED','COMPLETED','CANCELLED'
        ];
        $statuses = array_values(array_intersect(
            array_map('strtoupper', $statusG),
            $allowedStatuses
        ));

        // --------- base select ----------
        $select = "
            SELECT a.id, a.status, a.scheduled_at,
                   svc.name AS service_name, svc.price AS service_price,
                   v.year, v.make, v.model, v.plate_no
        ";
        $joins = "
            FROM appointments a
            JOIN services  svc ON svc.id = a.service_id
            JOIN vehicles  v   ON v.id   = a.vehicle_id
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

        // --------- where building ----------
        $where  = ["a.customer_id = ?"];
        $params = [$uid];

        if ($q !== '') {
            // search in service name, vehicle make/model/plate
            $where[] = "(svc.name LIKE ? OR v.make LIKE ? OR v.model LIKE ? OR v.plate_no LIKE ?)";
            $like = "%$q%";
            array_push($params, $like, $like, $like, $like);
        }

        if (!empty($statuses)) {
            // build "IN (?,?,?)" safely
            $ph = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = "a.status IN ($ph)";
            foreach ($statuses as $s) $params[] = $s;
        }

        $order = "a.scheduled_at DESC";
        switch (strtolower($sort)) {
            case 'old':    $order = "a.scheduled_at ASC"; break;
            case 'status': $order = "FIELD(a.status,'PENDING','APPROVED','CONFIRMED','IN_PROGRESS','WAITING_PARTS','DELAYED','COMPLETED','CANCELLED'), a.scheduled_at DESC"; break;
            case 'service':$order = "svc.name ASC, a.scheduled_at DESC"; break;
            default:       $order = "a.scheduled_at DESC";
        }

        $sql = $select.$joins." WHERE ".implode(' AND ', $where)." ORDER BY $order";
        $st  = $pdo->prepare($sql);
        $st->execute($params);
        $items = $st->fetchAll(PDO::FETCH_ASSOC);

        $this->renderAny(
            ['appointments/index.php', 'customer/appointments.php'],
            [
                'items'    => $items,
                'q'        => $q,
                'sort'     => $sort,
                'statuses' => $statuses,
                'allowedStatuses' => $allowedStatuses
            ]
        );
    }

// ...rest of controller unchanged...


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

        $services = $pdo->query("SELECT id, name, price FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

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

    // POST /appointments or /appointments/create
    public function store()
    {
        Auth::requireRole('CUSTOMER');
        $uid  = Auth::id();
        $pdo  = DB::pdo();

        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);

        $serviceIds = [];
        if (!empty($_POST['service_ids']) && is_array($_POST['service_ids'])) {
            foreach ($_POST['service_ids'] as $sid) {
                $sid = (int)$sid;
                if ($sid > 0) $serviceIds[] = $sid;
            }
        } else {
            $sid = (int)($_POST['service_id'] ?? 0);
            if ($sid > 0) $serviceIds[] = $sid;
        }

        $serviceIds = array_values(array_unique($serviceIds));
        if (count($serviceIds) > 10) {
            $_SESSION['flash'] = ['err' => 'Please select at most 10 services.'];
            return $this->redirect('appointments/create');
        }

        $when = trim($_POST['scheduled_at'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        if ($when === '' && $date !== '' && $time !== '') {
            $when = "$date $time:00";
        }

        $staff_id = isset($_POST['staff_id']) && $_POST['staff_id'] !== '' ? (int)$_POST['staff_id'] : null;
        $notes    = trim($_POST['notes'] ?? '');

        if ($vehicle_id <= 0 || empty($serviceIds) || $when === '') {
            $_SESSION['flash'] = ['err' => 'Please fill all required fields.'];
            return $this->redirect('appointments/create');
        }

        $chk = $pdo->prepare("SELECT id, plate_no FROM vehicles WHERE id = ? AND user_id = ?");
        $chk->execute([$vehicle_id, Auth::id()]);
        $vehRow = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$vehRow) {
            $_SESSION['flash'] = ['err' => 'Invalid vehicle selected.'];
            return $this->redirect('appointments/create');
        }
        $plate = (string)($vehRow['plate_no'] ?? '');

        $baseCols  = ['customer_id','vehicle_id','service_id','scheduled_at','status'];
        $baseMarks = '?,?,?,?,?';

        $created = 0;
        $pdo->beginTransaction();
        try {
            foreach ($serviceIds as $service_id) {
                $cols  = $baseCols;
                $vals  = [Auth::id(), $vehicle_id, $service_id, $when, 'PENDING'];
                $marks = $baseMarks;

                if ($this->colExists($pdo, 'appointments', 'staff_id') && $staff_id) {
                    $cols[]  = 'staff_id';
                    $vals[]  = $staff_id;
                    $marks  .= ',?';
                }
                if ($this->colExists($pdo, 'appointments', 'remarks') && $notes !== '') {
                    $cols[]  = 'remarks';
                    $vals[]  = $notes;
                    $marks  .= ',?';
                }

                $sql = "INSERT INTO appointments (".implode(',', $cols).") VALUES ($marks)";
                $pdo->prepare($sql)->execute($vals);
                $created++;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            $_SESSION['flash'] = ['err' => 'Could not create appointment(s).'];
            return $this->redirect('appointments/create');
        }

        if ($created > 0) {
            $title = 'Booking submitted';
            $body  = "We received $created appointment".($created>1?'s':'')." for vehicle $plate.\nScheduled for: $when.";
            $this->notifyUser(Auth::id(), $title, $body);
        }

        $_SESSION['flash'] = ['ok' => $created.' appointment(s) created.'];
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
            SELECT a.id, a.status, a.scheduled_at, a.remarks,
                   svc.name AS service_name, svc.price AS service_price, svc.est_hours,
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

        // Pull service record (if exists) for the detailed panel
        // Pull service record (if exists) for the detailed panel
$record = null;
if ($this->tableExists($pdo, 'service_records')) {
    $rs = $pdo->prepare("SELECT * FROM service_records WHERE appointment_id=? LIMIT 1");
    $rs->execute([$id]);
    $record = $rs->fetch(PDO::FETCH_ASSOC) ?: null;

    // Support either column name; your DB uses `photos`
    $photosRaw = $record['photos'] ?? ($record['photos_json'] ?? null);
    if ($photosRaw) {
        $decoded = json_decode($photosRaw, true);
        $record['photos'] = is_array($decoded) ? $decoded : [];
    } else {
        $record['photos'] = [];
    }
}


        $this->renderAny(
            ['appointments/show.php', 'customer/appointment_show.php'],
            ['a' => $a, 'record' => $record]
        );
    }

    public function view() { return $this->show(); }

    public function showById($id) {
        $_GET['id'] = (int)$id;
        return $this->show();
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

            if ($st->rowCount() > 0) {
                $this->notifyUser($uid, 'Booking cancelled', "Your appointment #$id has been cancelled.");
            }
        }
        $this->redirect('appointments');
    }

    public function rescheduleForm($id)
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $sql = "SELECT a.id, a.customer_id, a.scheduled_at, a.status,
                       s.name AS service_name,
                       v.year, v.make, v.model, v.plate_no
                FROM appointments a
                JOIN services s ON s.id=a.service_id
                JOIN vehicles v ON v.id=a.vehicle_id
                WHERE a.id=? AND a.customer_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$id, $uid]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) return $this->redirect('appointments');

        $this->render('appointments/reschedule.php', ['a' => $item]);
    }

    public function rescheduleSave($id)
    {
        Auth::requireRole('CUSTOMER');
        $uid  = Auth::id();
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $when = trim($_POST['scheduled_at'] ?? '');

        if ($when === '' && $date !== '' && $time !== '') {
            $when = "$date $time:00";
        }
        if ($when === '') {
            $_SESSION['flash'] = ['err' => 'Please choose a new date & time.'];
            return $this->redirect("appointments/$id/reschedule");
        }

        $pdo = DB::pdo();
        $chk = $pdo->prepare("SELECT id FROM appointments WHERE id=? AND customer_id=?");
        $chk->execute([(int)$id, $uid]);
        if (!$chk->fetch()) return $this->redirect('appointments');

        $up = $pdo->prepare("UPDATE appointments SET scheduled_at=?, status='PENDING' WHERE id=?");
        $up->execute([$when, (int)$id]);

        $this->notifyUser($uid, 'Appointment rescheduled', "Appointment #$id moved to: $when.");

        $_SESSION['flash'] = ['ok' => 'Appointment rescheduled.'];
        return $this->redirect("appointments/$id");
    }

    private function notifyUser(int $userId, string $title, string $body): void {
        $pdo = DB::pdo();
        $chk = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='notifications' LIMIT 1");
        $chk->execute();
        if (!$chk->fetchColumn()) return;

        $st = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, is_read, created_at)
                             VALUES (?,?,?,?,0,NOW())");
        $st->execute([$userId, 'IN_APP', $title, $body]);
    }

    /* =========================
       STAFF-ONLY actions (unchanged)
       ========================= */

    public function updateStatus($id)
    {
        Auth::requireRole('STAFF');
        $pdo = DB::pdo();
        $aid = (int)$id;

        $st = $this->colExists($pdo, 'appointments', 'staff_id')
            ? $pdo->prepare("SELECT customer_id, staff_id FROM appointments WHERE id=?")
            : $pdo->prepare("SELECT customer_id, NULL AS staff_id FROM appointments WHERE id=?");
        $st->execute([$aid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return $this->redirect('staff/schedule');

        if ($this->colExists($pdo, 'appointments', 'staff_id') && (int)$row['staff_id'] !== (int)Auth::id()) {
            $_SESSION['flash'] = ['err' => 'You are not assigned to this appointment.'];
            return $this->redirect('staff/schedule');
        }

        $status  = strtoupper(trim($_POST['status'] ?? ''));
        $allowed = ['PENDING','CONFIRMED','IN_PROGRESS','COMPLETED','DELAYED','CANCELLED','APPROVED'];
        if (!in_array($status, $allowed, true)) {
            $_SESSION['flash'] = ['err' => 'Invalid status.'];
            return $this->redirect('staff/schedule');
        }

        $pdo->prepare("UPDATE appointments SET status=? WHERE id=?")->execute([$status, $aid]);

        $cust = (int)($row['customer_id'] ?? 0);
        if ($cust) $this->notifyUser($cust, 'Appointment update', "Your appointment #$aid is now: $status.");

        $_SESSION['flash'] = ['ok' => 'Status updated.'];
        return $this->redirect('staff/workflow?appointment_id='.$aid);
    }

    public function staffRescheduleSave($id)
    {
        Auth::requireRole('STAFF');
        $pdo = DB::pdo();
        $aid = (int)$id;

        $st = $pdo->prepare("SELECT customer_id, staff_id FROM appointments WHERE id=?");
        $st->execute([$aid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || ($this->colExists($pdo,'appointments','staff_id') && (int)$row['staff_id'] !== (int)Auth::id())) {
            $_SESSION['flash'] = ['err' => 'You are not assigned to this appointment.'];
            return $this->redirect('staff/schedule');
        }

        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $when = trim($_POST['scheduled_at'] ?? '');
        if ($when === '' && $date !== '' && $time !== '') $when = "$date $time:00";
        if ($when === '') {
            $_SESSION['flash'] = ['err' => 'Please provide the new date & time.'];
            return $this->redirect('staff/schedule');
        }

        $pdo->prepare("UPDATE appointments SET scheduled_at=?, status='CONFIRMED' WHERE id=?")
            ->execute([$when, $aid]);

        $cust = (int)($row['customer_id'] ?? 0);
        if ($cust) $this->notifyUser($cust, 'Appointment rescheduled', "Your appointment #$aid is moved to: $when.");

        $_SESSION['flash'] = ['ok' => 'Appointment rescheduled.'];
        return $this->redirect('staff/workflow?appointment_id='.$aid);
    }
}
