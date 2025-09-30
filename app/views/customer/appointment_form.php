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

    <form method="post" action="<?= url('appointments') ?>" id="apptFormB">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="text-sm text-gray-600">Vehicle</label>
          <select name="vehicle_id" class="mt-1 w-full border rounded-lg px-3 py-2" required>
            <option value="">— select vehicle —</option>
            <?php foreach ($vehicles as $v): ?>
              <option value="<?= (int)$v['id'] ?>">
                <?= $e($v['year'].' '.$v['make'].' '.$v['model'].' • '.$v['plate_no']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label class="text-sm text-gray-600">Services</label>
            <span class="text-xs text-gray-500">Hold Ctrl/Cmd to choose multiple</span>
          </div>
          <select name="service_ids[]" class="mt-1 w-full border rounded-lg px-3 py-2" size="6" multiple required id="svcSelectB">
            <?php foreach ($services as $s): ?>
              <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)($s['price'] ?? 0) ?>">
                <?= $e($s['name']) ?> — RM <?= number_format((float)($s['price'] ?? 0), 2) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-2 text-sm text-gray-700 hidden" id="totalWrapB">
            Estimated total: <span class="font-semibold" id="totalB"></span>
          </div>
        </div>

        <?php if (!empty($staff)): ?>
          <div class="md:col-span-2">
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
          <label class="text-sm text-gray-600">Date</label>
          <input type="date" name="date" class="mt-1 w-full border rounded-lg px-3 py-2" required>
        </div>

        <div>
          <label class="text-sm text-gray-600">Time</label>
          <input type="time" name="time" class="mt-1 w-full border rounded-lg px-3 py-2" required>
        </div>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <a href="<?= url('appointments') ?>" class="px-5 py-2 rounded-full border">Cancel</a>
        <button class="px-6 py-3 rounded-full bg-black text-white">Create Appointment</button>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
  const fmt = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' });
  const select = document.getElementById('svcSelectB');
  const total  = document.getElementById('totalB');
  const wrap   = document.getElementById('totalWrapB');

  function refresh() {
    if (!select) return;
    let sum = 0;
    Array.from(select.selectedOptions).forEach(opt => {
      const p = parseFloat(opt.getAttribute('data-price') || '0');
      if (!Number.isNaN(p)) sum += p;
    });
    if (sum > 0) {
      wrap.classList.remove('hidden');
      total.textContent = fmt.format(sum);
    } else {
      wrap.classList.add('hidden');
      total.textContent = '';
    }
  }

  select && select.addEventListener('change', refresh);
  refresh();
})();
</script>
