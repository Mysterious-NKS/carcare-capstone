<form method="post" action="<?= url('admin/vehicles') ?>" class="space-y-3">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block">
      <span class="text-sm text-gray-600">Year</span>
      <input name="year" class="w-full border rounded-lg px-3 py-2" placeholder="2020" inputmode="numeric">
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Make</span>
      <input name="make" class="w-full border rounded-lg px-3 py-2" placeholder="Honda" required>
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Model</span>
      <input name="model" class="w-full border rounded-lg px-3 py-2" placeholder="Civic" required>
    </label>
    <label class="block">
      <span class="text-sm text-gray-600">Plate No.</span>
      <input name="plate_no" class="w-full border rounded-lg px-3 py-2" placeholder="ABC1234" required>
    </label>
    <label class="block md:col-span-2">
      <span class="text-sm text-gray-600">Assign to User (ID)</span>
      <input name="user_id" class="w-full border rounded-lg px-3 py-2" placeholder="e.g., 7">
    </label>
  </div>

  <div class="pt-2">
    <button class="btn">Add Vehicle</button>
  </div>
</form>
