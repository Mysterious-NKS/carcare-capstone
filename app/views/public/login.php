<div class="max-w-7xl mx-auto px-4 py-16">
  <!-- give the card an id so we can 'crack' it -->
  <div id="login-card" class="relative max-w-md mx-auto bg-white border rounded-2xl shadow-card p-8 overflow-hidden">
    <!-- container for floating shards -->
    <div id="shards" class="pointer-events-none absolute inset-0 hidden"></div>

    <h1 class="text-3xl font-extrabold mb-2">welcome back!</h1>
    <p class="text-gray-600 mb-6">Sign in to access your bespoke automotive dashboard.</p>

    <?php if(isset($_GET['e'])): ?>
      <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
        <?php
          $map = [
            'invalid' => 'Please enter a valid email and password.',
            'creds'   => 'Email or password is incorrect.',
            'server'  => 'Something went wrong on our side. Try again shortly.'
          ];
          echo $map[$_GET['e']] ?? 'Unable to sign in.';
        ?>
      </div>
    <?php endif; ?>

    <!-- novalidate so easter eggs can intercept; data-base used by JS for safe redirects -->
    <form id="login-form" method="post" action="<?= url('login') ?>" class="space-y-4" novalidate autocomplete="off" data-base="<?= rtrim(url(''),'/') ?>">
      <div>
        <label class="text-sm text-gray-600" for="login-email">Email</label>
        <!-- type=text on purpose (no @-validation); keep autofill hints off -->
        <input
          id="login-email"
          type="text"
          name="email"
          class="mt-1 w-full border rounded-lg px-3 py-2 transition-colors"
          autocomplete="off"
          autocorrect="off"
          autocapitalize="off"
          spellcheck="false"
          inputmode="text"
        >
      </div>
      <div>
        <label class="text-sm text-gray-600" for="login-password">Password</label>
        <!-- not required so the RI path can fire with blank password -->
        <input
          id="login-password"
          type="password"
          name="password"
          class="mt-1 w-full border rounded-lg px-3 py-2"
          autocomplete="off"
        >
      </div>

      <div class="flex items-center justify-between">
        <label class="text-sm text-gray-600">
          <input type="checkbox" class="mr-2">Remember me
        </label>
        <span class="text-sm text-gray-400 select-none">&nbsp;</span>
      </div>

      <button id="signin-btn" type="submit" class="w-full px-4 py-3 rounded-full bg-black text-white">sign in →</button>
    </form>
  </div>
</div>

<!-- tarot only; no banish overlay on login -->
<?php
  $tarot = view('partials/tarot-modal.php');
  if (is_file($tarot)) include $tarot;
?>

<link rel="stylesheet" href="<?= url('assets/css/glitch.css') ?>">
<script src="<?= url('assets/js/easter-eggs.js') ?>" defer></script>
