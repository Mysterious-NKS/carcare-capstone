<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-6">
    <h1 class="text-2xl font-bold mb-4">Book an Appointment</h1>

    <form method="post" action="<?= url('appointments/create') ?>" class="space-y-5" id="apptFormA">
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
        <div class="flex items-center justify-between">
          <label class="text-sm text-gray-600">Services</label>
          <span class="text-xs text-gray-500">Tip: hold Ctrl/Cmd to select multiple</span>
        </div>
        <select name="service_ids[]" class="mt-1 w-full border rounded-lg px-3 py-2" size="6" multiple required id="svcSelectA">
          <?php foreach ($services as $s): ?>
            <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['price'] ?>">
              <?= $e($s['name']) ?> — RM <?= number_format((float)$s['price'], 2) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="mt-2 text-sm text-gray-700 hidden" id="totalWrapA">
          Estimated total: <span class="font-semibold" id="totalA"></span>
        </div>
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

<script>
(function() {
  const fmt = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' });
  const select = document.getElementById('svcSelectA');
  const total  = document.getElementById('totalA');
  const wrap   = document.getElementById('totalWrapA');

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
