<?php
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);
?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-extrabold mb-6">your profile</h1>

  <div class="bg-white border rounded-2xl shadow-card p-6 space-y-6">
    <form method="post" action="<?= url('profile') ?>" class="space-y-4">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Full name</label>
        <input name="full_name" value="<?= $e($u['full_name']) ?>" class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Email</label>
        <input value="<?= $e($u['email']) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-50" disabled>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Phone</label>
        <input name="phone" value="<?= $e($u['phone']) ?>" class="w-full border rounded-lg px-3 py-2">
      </div>

      <button class="px-4 py-2 rounded-full bg-black text-white">Save profile</button>
    </form>

    <hr class="my-4">

    <form method="post" action="<?= url('profile/password') ?>" class="space-y-4">
      <div class="font-semibold">Change password</div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input name="current" type="password" placeholder="Current password" class="border rounded-lg px-3 py-2">
        <input name="new"     type="password" placeholder="New password"     class="border rounded-lg px-3 py-2">
        <input name="confirm" type="password" placeholder="Confirm new"      class="border rounded-lg px-3 py-2">
      </div>
      <button class="px-4 py-2 rounded-full border">Update password</button>
    </form>
  </div>
</div>
