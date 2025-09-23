<?php
class VehicleController extends Controller
{
    private function userId() {
        if (!isset($_SESSION['user'])) { $this->redirect('login'); }
        return (int)$_SESSION['user']['id'];
    }

    public function index() {
        $uid = $this->userId();
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$uid]);
        $vehicles = $stmt->fetchAll();
        $this->render('customer/vehicles.php', ['vehicles' => $vehicles]);
    }

    public function create() {
        $this->userId();
        $this->render('customer/vehicle_form.php', ['mode' => 'add', 'vehicle' => null]);
    }

    public function store() {
    Auth::requireRole('CUSTOMER');

    $userId = Auth::id();

    $make   = trim($_POST['make'] ?? '');
    $model  = trim($_POST['model'] ?? '');
    $year   = (int)($_POST['year'] ?? 0);

    // plate is UNIQUE in DB; blank '' will violate it on a second insert.
    $plate  = trim($_POST['license_plate'] ?? '');
    if ($plate === '') {
        $plate = 'UNREG-' . strtoupper(substr(md5($userId.microtime(true)), 0, 6));
    }

    $color  = trim($_POST['color'] ?? '');
    $vin    = trim($_POST['vin'] ?? '');
    $mileage= (int)($_POST['mileage'] ?? 0);
    $last   = $_POST['last_service_date'] ?: null;
    $ins    = trim($_POST['insurance_provider'] ?? '');
    $policy = trim($_POST['policy_number'] ?? '');
    $notes  = trim($_POST['notes'] ?? '');

    if ($make === '' || $model === '' || $year <= 0) {
        return $this->redirect('customer/vehicles/add?e=invalid');
    }

    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
          INSERT INTO vehicles (
            user_id, plate_no, make, model, year, color, vin, mileage,
            last_service_date, insurance_provider, policy_number, notes
          ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $userId, $plate, $make, $model, $year, $color, $vin, $mileage,
            $last ?: null, $ins, $policy, $notes
        ]);

        $vehicleId = (int)$pdo->lastInsertId();

        // --- upload photos (optional) ---
        if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
            $dir = dirname(__DIR__, 2) . "/public/uploads/vehicles/$vehicleId";
            if (!is_dir($dir)) { @mkdir($dir, 0777, true); }

            $ok = ['image/jpeg','image/png','image/webp','image/gif'];
            $names = $_FILES['photos']['name'];
            $tmps  = $_FILES['photos']['tmp_name'];
            $types = $_FILES['photos']['type'];
            $errs  = $_FILES['photos']['error'];
            $sizes = $_FILES['photos']['size'];

            for ($i = 0; $i < count($names); $i++) {
                if ($errs[$i] !== UPLOAD_ERR_OK) continue;
                if (!in_array($types[$i], $ok, true)) continue;
                if ($sizes[$i] > 5 * 1024 * 1024) continue;

                $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION) ?: 'jpg');
                $file = 'v'.$vehicleId.'_'.time().'_'.substr(md5($names[$i].$i), 0, 6).'.'.$ext;

                if (@move_uploaded_file($tmps[$i], "$dir/$file")) {
                    $webPath = "/uploads/vehicles/$vehicleId/$file";
                    VehiclePhoto::add($vehicleId, $webPath);
                }
            }
        }

        $pdo->commit();
        $_SESSION['flash'] = ['ok' => 'Vehicle added successfully.'];
        return $this->redirect('customer/vehicles');

    } catch (Throwable $e) {
        $pdo->rollBack();
        // Optional: log error
        return $this->redirect('customer/vehicles/add?e=server');
    }
}




    public function edit() {
    $uid = $this->userId();
    $id  = (int)($_GET['id'] ?? 0);

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=? AND user_id=?");
    $stmt->execute([$id,$uid]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) return $this->redirect('customer/vehicles');

    $photos = class_exists('VehiclePhoto') ? VehiclePhoto::byVehicle($id) : [];

    $this->render('customer/vehicle_form.php', [
        'mode'    => 'edit',
        'vehicle' => $vehicle,
        'photos'  => $photos,
    ]);
}

public function update() {
    $uid = $this->userId();
    $id  = (int)($_GET['id'] ?? 0);

    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE id=? AND user_id=?");
    $stmt->execute([$id,$uid]);
    if(!$stmt->fetch()) return $this->redirect('customer/vehicles');

    $make   = trim($_POST['make'] ?? '');
    $model  = trim($_POST['model'] ?? '');
    $year   = (int)($_POST['year'] ?? 0);
    $color  = trim($_POST['color'] ?? '');
    $plate  = trim($_POST['license_plate'] ?? ''); // <-- was plate_no
    $vin    = trim($_POST['vin'] ?? '');
    $mileage= (int)($_POST['mileage'] ?? 0);
    $last   = $_POST['last_service_date'] ?: null;
    $ins    = trim($_POST['insurance_provider'] ?? '');
    $pol    = trim($_POST['policy_number'] ?? '');
    $notes  = trim($_POST['notes'] ?? '');

    $up = $pdo->prepare("UPDATE vehicles SET
      make=?, model=?, year=?, color=?, plate_no=?, vin=?, mileage=?, last_service_date=?, insurance_provider=?, policy_number=?, notes=?
      WHERE id=? AND user_id=?");
    $up->execute([$make,$model,$year,$color,$plate,$vin,$mileage,$last,$ins,$pol,$notes,$id,$uid]);

    $this->handleUploads($id);
    $this->redirect('customer/vehicles');
}


    public function destroy() {
        $uid = $this->userId();
        $id  = (int)($_GET['id'] ?? 0);
        $pdo = DB::pdo();
        $pdo->prepare("DELETE FROM vehicles WHERE id=? AND user_id=?")->execute([$id,$uid]);

        $dir = __DIR__ . '/../../public/uploads/vehicles/' . $id;
        if (is_dir($dir)) {
            foreach (glob($dir.'/*') as $f) @unlink($f);
            @rmdir($dir);
        }
        $this->redirect('customer/vehicles');
    }

    private function handleUploads(int $vehicleId): void {
        if (!isset($_FILES['photos'])) return;
        $base = __DIR__ . '/../../public/uploads/vehicles/' . $vehicleId;
        if (!is_dir($base)) @mkdir($base, 0777, true);

        $files = $_FILES['photos'];
        for ($i=0; $i < count((array)$files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $tmp  = $files['tmp_name'][$i];
            $name = preg_replace('/[^a-zA-Z0-9_.-]/','_', $files['name'][$i]);
            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
            $dest = $base . '/' . uniqid('img_', true) . '.' . $ext;
            @move_uploaded_file($tmp, $dest);
        }
    }
}
