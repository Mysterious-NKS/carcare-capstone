<div class="max-w-7xl mx-auto px-4 py-16">
  <div class="max-w-md mx-auto bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-2">welcome back!</h1>
    <p class="text-gray-600 mb-6">Sign in to access your bespoke automotive dashboard.</p>

    <?php if(isset($_GET['e'])): ?>
      <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
        <?php
          // decode the error in plain English; gentle honesty beats vague drama
          $map = [
            'invalid' => 'Please enter a valid email and password.',
            'creds'   => 'Email or password is incorrect.',
            'server'  => 'Something went wrong on our side. Try again shortly.'
          ];
          echo $map[$_GET['e']] ?? 'Unable to sign in.';
        ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('login') ?>" class="space-y-4">
      <div>
        <label class="text-sm text-gray-600">Email</label>
        <input type="email" name="email" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">Password</label>
        <input type="password" name="password" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div class="flex items-center justify-between">
        <label class="text-sm text-gray-600"><input type="checkbox" class="mr-2">Remember me</label>
        <a class="text-sm underline">Forgot password?</a>
      </div>
      <button class="w-full px-4 py-3 rounded-full bg-black text-white">sign in →</button>
    </form>
    <div class="mt-6 text-sm text-gray-600">No account? <a href="<?= url('register') ?>" class="underline">Register</a></div>
  </div>
</div>
