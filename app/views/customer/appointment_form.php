<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-6">Book Appointment</h1>

    <?php if (isset($_GET['e']) && $_GET['e'] === 'invalid'): ?>
      <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
        Please choose a vehicle, service, and time.
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('appointments') ?>">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="text-sm text-gray-600">Vehicle</label>
          <select name="vehicle_id" class="mt-1 w-full border rounded-lg px-3 py-2">
            <option value="">— select vehicle —</option>
            <?php foreach ($vehicles as $v): ?>
              <option value="<?= (int)$v['id'] ?>">
                <?= $e($v['year'].' '.$v['make'].' '.$v['model'].' • '.$v['plate_no']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-600">Service</label>
          <select name="service_id" class="mt-1 w-full border rounded-lg px-3 py-2">
            <option value="">— select service —</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= (int)$s['id'] ?>"><?= $e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-600">Date</label>
          <input type="date" name="date" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>

        <div>
          <label class="text-sm text-gray-600">Time</label>
          <input type="time" name="time" class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <a href="<?= url('appointments') ?>" class="px-5 py-2 rounded-full border">Cancel</a>
        <button class="px-6 py-3 rounded-full bg-black text-white">Create Appointment</button>
      </div>
    </form>
  </div>
</div>
