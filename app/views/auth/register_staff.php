<?php /* register_staff.php */ ?>
<div class="max-w-7xl mx-auto px-4 py-16">
  <div class="max-w-md mx-auto bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-2">welcome, new staff!</h1>
    <p class="text-gray-600 mb-6">Enter your details and your staff security pin.</p>

    <?php if(isset($_GET['e'])): ?>
      <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
        <?php
          $map = [
            'invalid' => 'Fill in name, a valid email, and a 6+ character password.',
            'exists'  => 'That email is already registered.',
            'pin'     => 'Please try again.',
            'server'  => 'We could not register you just now. Try again in a moment.'
          ];
          echo $map[$_GET['e']] ?? 'Unable to register.';
        ?>
      </div>
    <?php endif; ?>

    <!-- Turn ON autocomplete to allow browser suggestions/history -->
    <form method="post" action="<?= url('register/staff') ?>" class="space-y-4" autocomplete="on">
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
        <!-- Make pin friendly to autofill/password managers & numeric keyboards -->
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
      <button class="w-full px-4 py-3 rounded-full bg-black text-white">sign up →</button>
    </form>

    <div class="mt-6 text-sm text-gray-600">Already have an account? <a href="<?= url('login') ?>" class="underline">Login</a></div>
  </div>
</div>
