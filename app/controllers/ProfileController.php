<?php

class ProfileController extends Controller
{
    private function pdo(): PDO { return DB::pdo(); }

    private function needLogin(): array {
        if (!isset($_SESSION['user'])) $this->redirect('login');
        return $_SESSION['user'];
    }

    public function show()
    {
        $me = $this->needLogin();
        $pdo = $this->pdo();

        $st = $pdo->prepare("SELECT id, full_name, email, phone FROM users WHERE id=? LIMIT 1");
        $st->execute([(int)$me['id']]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if (!$u) return $this->redirect('login');

        // one view for all roles
        $this->render('profile/show.php', ['u' => $u, 'role' => $me['role']]);
    }

    public function update()
    {
        $me = $this->needLogin();
        $pdo = $this->pdo();

        $name  = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '') {
            $_SESSION['flash'] = ['err' => 'Name is required.'];
            return $this->redirect('profile');
        }

        $st = $pdo->prepare("UPDATE users SET full_name=?, phone=? WHERE id=?");
        $st->execute([$name, $phone, (int)$me['id']]);

        // keep header greeting fresh
        $_SESSION['user']['name'] = $name;

        $_SESSION['flash'] = ['ok' => 'Profile updated.'];
        $this->redirect('profile');
    }

    public function changePassword()
    {
        $me = $this->needLogin();
        $pdo = $this->pdo();

        $current = $_POST['current'] ?? '';
        $new     = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (strlen($new) < 6 || $new !== $confirm) {
            $_SESSION['flash'] = ['err' => 'New passwords must match and be at least 6 characters.'];
            return $this->redirect('profile');
        }

        $st = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
        $st->execute([(int)$me['id']]);
        $hash = $st->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            $_SESSION['flash'] = ['err' => 'Current password is incorrect.'];
            return $this->redirect('profile');
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $up = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $up->execute([$newHash, (int)$me['id']]);

        $_SESSION['flash'] = ['ok' => 'Password changed.'];
        $this->redirect('profile');
    }
}
