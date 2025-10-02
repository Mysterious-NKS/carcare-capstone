<form method="post" action="<?= url('admin/users') ?>" class="space-y-3">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block">
      <span class="text-sm text-gray-600">Full Name</span>
      <input name="full_name" class="w-full border rounded-lg px-3 py-2" placeholder="Jane Doe">
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Email</span>
      <input name="email" type="email" class="w-full border rounded-lg px-3 py-2" placeholder="jane@email.com">
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Password</span>
      <input name="password" type="password" class="w-full border rounded-lg px-3 py-2" placeholder="••••••••">
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Role</span>
      <select name="role" class="w-full border rounded-lg px-3 py-2">
        <option value="CUSTOMER">Customer</option>
        <option value="STAFF">Staff</option>
        <option value="ADMIN">Admin</option>
      </select>
    </label>
    <label class="block md:col-span-2">
      <span class="text-sm text-gray-600">Notes</span>
      <textarea name="notes" rows="4" class="w-full border rounded-lg px-3 py-2" placeholder="Optional"></textarea>
    </label>
  </div>

  <div class="pt-2">
    <button class="btn">Add User</button>
  </div>
</form>
