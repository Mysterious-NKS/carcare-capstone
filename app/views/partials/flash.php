<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
if ($flash):
  $type = isset($flash['ok']) ? 'ok' : 'err';
  $msg  = $flash['ok'] ?? $flash['err'] ?? '';
?>
  <div class="flash <?= $type === 'ok' ? 'flash-ok' : 'flash-err' ?>">
    <div class="max-w-7xl mx-auto px-4 py-3">
      <span><?= htmlspecialchars($msg, ENT_QUOTES) ?></span>
    </div>
  </div>
<?php endif; ?>
