<?php /* register_admin.php */ ?>
<link rel="stylesheet" href="<?= url('assets/css/glitch.css') ?>">

<div class="max-w-7xl mx-auto px-4 py-16">
  <!-- Include the banish overlay ALWAYS; it self-decides whether to show -->
  <?php include view('partials/glitch-overlays.php'); ?>

  <!-- make this wrapper relative so the ambient field can anchor correctly -->
  <div class="relative">
    <!-- ambient field lives alongside the card; it will never receive pointer events -->
    <div id="eldritch-field" class="eldritch-field" aria-hidden="true"></div>

    <div id="admin-card" class="max-w-md mx-auto bg-white border rounded-2xl shadow-card p-8 relative overflow-hidden">
      <h1 class="glitch-title text-3xl font-extrabold mb-2" data-text="hello, admin">hello, admin</h1>
      <p class="text-gray-600 mb-6">Input accepted. Proceed with creation protocol.</p>

      <?php if(isset($_GET['e']) && $_GET['e'] !== 'pin'): ?>
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
          <?php
            $map = [
              'invalid' => 'Name, valid email, and 6+ character password are required.',
              'exists'  => 'Email already exists.',
              'server'  => 'Server refused to comply. Try again shortly.'
            ];
            echo $map[$_GET['e']] ?? 'Unable to register.';
          ?>
        </div>
      <?php endif; ?>

      <!-- Turn ON autocomplete to allow browser suggestions/history -->
      <form method="post" action="<?= url('register/admin') ?>" class="space-y-4 relative z-10" autocomplete="on">
        <div>
          <label class="text-sm text-gray-600">Name</label>
          <input type="text" name="name" class="mt-1 w-full border rounded-lg px-3 py-2"
                 autocomplete="name" autocapitalize="words" spellcheck="false" required>
        </div>
        <div>
          <label class="text-sm text-gray-600">Email</label>
          <input type="email" name="email" class="mt-1 w-full border rounded-lg px-3 py-2"
                 autocomplete="email" autocapitalize="off" spellcheck="false" required>
        </div>
        <div>
          <label class="text-sm text-gray-600">Phone</label>
          <input type="text" name="phone" class="mt-1 w-full border rounded-lg px-3 py-2"
                 autocomplete="tel" inputmode="tel">
        </div>
        <div>
          <label class="text-sm text-gray-600">Password</label>
          <input type="password" name="password" class="mt-1 w-full border rounded-lg px-3 py-2"
                 autocomplete="new-password" required>
        </div>
        <div>
          <label class="text-sm text-gray-600">Security Pin</label>
          <input
            type="text"
            name="pin"
            class="mt-1 w-full border rounded-lg px-3 py-2"
            inputmode="numeric"
            pattern="[0-9]*"
            autocomplete="one-time-code"
            autocapitalize="off"
            spellcheck="false"
            required
          >
          <p class="text-xs text-gray-500 mt-1"></p>
        </div>
        <button class="w-full px-4 py-3 rounded-full bg-black text-white">initiate →</button>
      </form>
    </div>
  </div>
</div>

<!-- ambient swarm / bouncing mantra logic -->
<script src="<?= url('assets/js/eldritch-ambient.js') ?>" defer></script>
