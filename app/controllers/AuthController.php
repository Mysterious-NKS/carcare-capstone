<?php
class AuthController extends Controller {

  // show forms — nothing spicy here
  public function showLogin(){ $this->render('public/login.php'); }
  public function showRegister(){ $this->render('public/register.php'); }

  // POST /register — create user, hash password, log them in. Simple, effective.
  public function register(){
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
      // low-effort guardrail; we keep the UX calm for now
      return $this->redirect('register?e=invalid');
    }

    try {
      $pdo = DB::pdo();
      // if email exists, MySQL will shout (UNIQUE). We pre-check to avoid loud errors.
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) return $this->redirect('register?e=exists');

      $hash = password_hash($pass, PASSWORD_DEFAULT); // sane defaults, future-proof
      $ins = $pdo->prepare("INSERT INTO users(full_name,email,phone,password_hash,role) VALUES(?,?,?,?, 'CUSTOMER')");
      $ins->execute([$name,$email,$phone,$hash]);

      // auto-login because friction is for enemies
      $id = (int)$pdo->lastInsertId();
      $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'CUSTOMER'];

      // later: redirect by role; for now: customer dashboard
      return $this->redirect('dashboard');
    } catch (Throwable $e) {
      // yes, this is minimally dramatic — logs later in Phase 6
      return $this->redirect('register?e=server');
    }
  }

  // POST /login — verify hash, set session, move on with our lives.
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
        return $this->redirect('login?e=creds'); // incorrect email/password… or banned; either way, no.
      }

      $_SESSION['user'] = [
        'id'    => (int)$u['id'],
        'name'  => $u['full_name'],
        'email' => $u['email'],
        'role'  => $u['role'],
      ];
     
      // send them where they belong; easy to explain in a demo
$role = $u['role'];
if ($role === 'STAFF')  return $this->redirect('staff');
if ($role === 'ADMIN')  return $this->redirect('admin');
return $this->redirect('dashboard'); // default: customers

    } catch (Throwable $e) {
      return $this->redirect('login?e=server');
    }
  }

  // GET /logout — hard reset, as is tradition.
  public function logout(){ Auth::logout(); $this->redirect(''); }
}
