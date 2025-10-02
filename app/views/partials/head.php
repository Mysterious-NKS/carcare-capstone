<?php $me = Auth::user(); ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{boxShadow:{card:'0 8px 24px rgba(0,0,0,.06)'}}}}</script>
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">

<style>
  /* Pitstop Logo Font */
  @font-face {
    font-family: 'Pitstop';
    src: url('<?= url('assets/fonts/Formula1-Wide.ttf') ?>') format('truetype');
    font-display: swap;
  }
  .logo-wordmark{
    font-family:'Pitstop', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    letter-spacing:.5px;
    font-weight:900;
  }
  /* chart helper to prevent stretching */
  .chart-wrap{height:320px;}
</style>
</head>
<script src="<?= url('assets/js/app.js') ?>" defer></script>

<body class="bg-white text-gray-900">
<header class="border-b">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-6">
    <?php
      $role = $me['role'] ?? null;
      $homeLink = ($role === 'STAFF') ? 'staff' : (($role === 'ADMIN') ? 'admin' : '');
      $dashLink = ($role === 'STAFF') ? 'staff' : (($role === 'ADMIN') ? 'admin' : 'dashboard');
    ?>
    <a href="<?= url($homeLink) ?>" class="logo-wordmark text-xl" aria-label="Home">pitstop</a>

    <nav class="hidden md:flex gap-6 text-sm">
      <a href="<?= url($homeLink) ?>" class="hover:underline">home</a>
      <a href="<?= url('about') ?>" class="hover:underline">about</a>
      <a href="<?= url('contact') ?>" class="hover:underline">contact</a>
    </nav>

    <div class="ml-auto flex items-center gap-3">
      <?php if ($me): ?>
        <?php
          $unread = 0;
          try {
            $pdo = DB::pdo();
            $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables 
                                 WHERE table_schema = DATABASE() AND table_name = 'notifications'");
            $st->execute();
            if ((int)$st->fetchColumn() === 1) {
              $c = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
              $c->execute([(int)$me['id']]);
              $unread = (int)$c->fetchColumn();
            }
          } catch (Throwable $e) { /* keep header robust */ }
        ?>

        <a href="<?= url('notifications') ?>"
           class="notif-bell relative inline-flex items-center justify-center p-2 rounded-full border hover:bg-gray-50"
           title="Notifications"
           aria-label="Notifications<?= $unread ? ' — '.$unread.' unread' : '' ?>">
          <?= function_exists('icon') ? icon('bell','h-6 w-6') : '' ?>
          <?php if ($unread > 0): ?>
            <span class="notif-badge" aria-hidden="true"><?= $unread ?></span>
          <?php endif; ?>
        </a>

        <a href="<?= url('profile') ?>" class="text-sm text-gray-600 hover:underline">
          Hi, <?= htmlspecialchars($me['name']) ?>
        </a>

        <a class="px-4 py-2 rounded-full bg-black text-white" href="<?= url($dashLink) ?>">dashboard</a>
        <a class="px-3 py-2 rounded-full border" href="<?= url('logout') ?>">log out</a>
      <?php else: ?>
        <a class="px-3 py-2 rounded-full border" href="<?= url('login') ?>">log in</a>
        <a class="px-4 py-2 rounded-full bg-black text-white" href="<?= url('register') ?>">register</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main>
<?php
$flashView = view('partials/flash.php');
if (is_file($flashView)) { include $flashView; }
?>
