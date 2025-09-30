<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-6">
    <h1 class="text-2xl font-bold mb-4">Book an Appointment</h1>

    <form method="post" action="<?= url('appointments/create') ?>" class="space-y-5">
      <div>
        <label class="text-sm text-gray-600">Vehicle</label>
        <select name="vehicle_id" class="mt-1 w-full border rounded-lg px-3 py-2" required>
          <option value="">— choose —</option>
          <?php foreach ($vehicles as $v): ?>
            <option value="<?= (int)$v['id'] ?>">
              <?= $e("{$v['year']} {$v['make']} {$v['model']} • {$v['plate_no']}") ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="text-sm text-gray-600">Service</label>
        <select name="service_id" class="mt-1 w-full border rounded-lg px-3 py-2" required>
          <option value="">— choose —</option>
          <?php foreach ($services as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= $e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (!empty($staff)): ?>
        <div>
          <label class="text-sm text-gray-600">Preferred Staff (optional)</label>
          <select name="staff_id" class="mt-1 w-full border rounded-lg px-3 py-2">
            <option value="">— any —</option>
            <?php foreach ($staff as $st): ?>
              <option value="<?= (int)$st['id'] ?>"><?= $e($st['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div>
        <label class="text-sm text-gray-600">When</label>
        <input type="datetime-local" name="scheduled_at" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>

      <div>
        <label class="text-sm text-gray-600">Notes (optional)</label>
        <textarea name="notes" rows="4" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Anything the shop should know..."></textarea>
      </div>

      <div class="pt-2">
        <a href="<?= url('appointments') ?>" class="px-4 py-2 rounded-full border">Cancel</a>
        <button class="px-6 py-2 rounded-full bg-black text-white">Book</button>
      </div>
    </form>
  </div>
</div>
