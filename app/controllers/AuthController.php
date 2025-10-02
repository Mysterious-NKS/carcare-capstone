<?php
class AuthController extends Controller {

  // show forms — nothing spicy here
  public function showLogin(){ $this->render('public/login.php'); }
  public function showRegister(){ $this->render('public/register.php'); }

  // ---------- NEW: Staff/Admin register forms ----------
  public function showRegisterStaff(){ $this->render('auth/register_staff.php'); }
  public function showRegisterAdmin(){
    // pass through error flags if any (?e=pin, etc.)
    $this->render('auth/register_admin.php', [
      'error' => $_GET['e'] ?? null,
    ]);
  }

  // ---------- NEW: POST /register/staff ----------
  public function registerStaff(){
    $PIN = '1783174';

    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pin   = trim($_POST['pin'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
      return $this->redirect('register/staff?e=invalid');
    }
    if ($pin !== $PIN) {
      return $this->redirect('register/staff?e=pin');
    }

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) return $this->redirect('register/staff?e=exists');

      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?, 'STAFF','ACTIVE')");
      $ins->execute([$name,$email,$phone,$hash]);

      $id = (int)$pdo->lastInsertId();
      $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'STAFF'];

      return $this->redirect('staff');
    } catch (Throwable $e) {
      return $this->redirect('register/staff?e=server');
    }
  }

  // ---------- NEW: POST /register/admin ----------
  public function registerAdmin(){
    $PIN = '8811798';

    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pin   = trim($_POST['pin'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
      return $this->redirect('register/admin?e=invalid');
    }

    // ⛔ Admin-only wrong PIN -> set one-time glitch flag and bounce to login
    if ($pin !== $PIN) {
      $_SESSION['glitch'] = 'banish';                // one-time flash consumed by the login view
      return $this->redirect('login');               // NO query string; overlay shows once then stops
    }

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) return $this->redirect('register/admin?e=exists');

      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?, 'ADMIN','ACTIVE')");
      $ins->execute([$name,$email,$phone,$hash]);

      $id = (int)$pdo->lastInsertId();
      $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'ADMIN'];

      return $this->redirect('admin'); // (we’ll wire this later)
    } catch (Throwable $e) {
      return $this->redirect('register/admin?e=server');
    }
  }

  // POST /register — create customer
  public function register(){
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
      return $this->redirect('register?e=invalid');
    }

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) return $this->redirect('register?e=exists');

      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?, 'CUSTOMER','ACTIVE')");
      $ins->execute([$name,$email,$phone,$hash]);

      $id = (int)$pdo->lastInsertId();
      $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'CUSTOMER'];

      return $this->redirect('dashboard');
    } catch (Throwable $e) {
      return $this->redirect('register?e=server');
    }
  }

  // POST /login
  public function login(){
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
      return $this->redirect('login?e=invalid');
    }

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $u = $stmt->fetch();

      if (!$u || $u['status'] !== 'ACTIVE' || !password_verify($pass, $u['password_hash'])) {
        return $this->redirect('login?e=creds');
      }

      $_SESSION['user'] = [
        'id'    => (int)$u['id'],
        'name'  => $u['full_name'],
        'email' => $u['email'],
        'role'  => $u['role'],
      ];

      $role = $u['role'];
      if ($role === 'STAFF')  return $this->redirect('staff');
      if ($role === 'ADMIN')  return $this->redirect('admin');
      return $this->redirect('dashboard');

    } catch (Throwable $e) {
      return $this->redirect('login?e=server');
    }
  }

  // GET /logout
  public function logout(){ Auth::logout(); $this->redirect(''); }
}
