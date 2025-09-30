<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-extrabold mb-6">My Profile</h1>

  <div class="bg-white border rounded-2xl shadow-card p-6">
    <form method="post" action="<?= url('profile') ?>" class="space-y-4">
      <div>
        <label class="text-sm text-gray-600">Full name</label>
        <input class="mt-1 w-full border rounded-lg px-3 py-2" name="name" value="<?= $e($user['full_name'] ?? '') ?>" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">Email</label>
        <input type="email" class="mt-1 w-full border rounded-lg px-3 py-2" name="email" value="<?= $e($user['email'] ?? '') ?>" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">Phone</label>
        <input class="mt-1 w-full border rounded-lg px-3 py-2" name="phone" value="<?= $e($user['phone'] ?? '') ?>">
      </div>

      <div class="pt-2">
        <button class="px-6 py-2 rounded-full bg-black text-white">Save Profile</button>
      </div>
    </form>

    <hr class="my-8">

    <form method="post" action="<?= url('profile/password') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm text-gray-600">Current password</label>
        <input type="password" name="current" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">New password</label>
        <input type="password" name="new" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">Repeat new password</label>
        <input type="password" name="repeat" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div class="md:col-span-3">
        <button class="px-6 py-2 rounded-full border">Change Password</button>
      </div>
    </form>
  </div>
</div>
