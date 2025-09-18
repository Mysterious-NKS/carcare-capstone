<div class="max-w-7xl mx-auto px-4 py-16">
  <div class="max-w-md mx-auto bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-2">welcome!</h1>
    <p class="text-gray-600 mb-8">Your car’s health, just a click away.</p>
    <form method="post" action="<?= url('register') ?>" class="space-y-4">
      <div><label class="text-sm text-gray-600">Name</label><input type="text" name="name" class="mt-1 w-full border rounded-lg px-3 py-2" required></div>
      <div><label class="text-sm text-gray-600">Email</label><input type="email" name="email" class="mt-1 w-full border rounded-lg px-3 py-2" required></div>
      <div><label class="text-sm text-gray-600">Phone</label><input type="text" name="phone" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
      <div><label class="text-sm text-gray-600">Password</label><input type="password" name="password" class="mt-1 w-full border rounded-lg px-3 py-2" required></div>
      <button class="w-full px-4 py-3 rounded-full bg-black text-white">sign up →</button>
    </form>
    <div class="mt-6 text-sm text-gray-600">Already have an account? <a href="<?= url('login') ?>" class="underline">Login</a></div>
  </div>
</div>
