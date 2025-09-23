<?php

class AppointmentController extends Controller
{
    private function uid(): int {
        if (!isset($_SESSION['user'])) $this->redirect('login');
        return (int)$_SESSION['user']['id'];
    }

    // GET /appointments — list user's appointments
    public function index()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $sql = "
          SELECT a.id, a.status, a.scheduled_at,
                 s.name AS service_name,
                 v.year, v.make, v.model, v.plate_no,
                 u.full_name AS staff_name
          FROM appointments a
          JOIN services s ON s.id = a.service_id
          JOIN vehicles v ON v.id = a.vehicle_id
          LEFT JOIN users u ON u.id = a.staff_id
          WHERE a.customer_id = ?
          ORDER BY a.scheduled_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('customer/appointments.php', ['items' => $items]);
    }

    // GET /appointments/create — form
    public function create()
    {
        Auth::requireRole('CUSTOMER');
        $uid = Auth::id();
        $pdo = DB::pdo();

        $v = $pdo->prepare("SELECT id, year, make, model, plate_no FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
        $v->execute([$uid]);
        $vehicles = $v->fetchAll(PDO::FETCH_ASSOC);

        $services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('customer/appointment_form.php', [
            'vehicles' => $vehicles,
            'services' => $services,
        ]);
    }

    // POST /appointments — create
    public function store()
    {
        Auth::requireRole('CUSTOMER');
        $uid      = Auth::id();
        $vehicle  = (int)($_POST['vehicle_id'] ?? 0);
        $service  = (int)($_POST['service_id'] ?? 0);
        $date     = trim($_POST['date'] ?? '');
        $time     = trim($_POST['time'] ?? '');
        $when     = trim($_POST['scheduled_at'] ?? '');

        if ($when === '' && $date !== '' && $time !== '') {
            $when = "$date $time:00";
        }

        if ($vehicle <= 0 || $service <= 0 || $when === '') {
            return $this->redirect('appointments/create?e=invalid');
        }

        $pdo = DB::pdo();

        // ensure the vehicle belongs to this user
        $chk = $pdo->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
        $chk->execute([$vehicle, $uid]);
        if (!$chk->fetch()) {
            return $this->redirect('appointments/create?e=invalid');
        }

        $ins = $pdo->prepare("
          INSERT INTO appointments (customer_id, vehicle_id, service_id, scheduled_at, status)
          VALUES (?,?,?,?, 'PENDING')
        ");
        $ins->execute([$uid, $vehicle, $service, $when]);

        $_SESSION['flash'] = ['ok' => 'Appointment created.'];
        return $this->redirect('appointments');
    }
}
