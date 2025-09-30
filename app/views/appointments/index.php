<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
/** @var array $items */
/** @var string $q */
/** @var string $sort */
/** @var array $statuses */
/** @var array $allowedStatuses */
$badgeCls = [
  'PENDING'=>'border-amber-300 text-amber-700 bg-amber-50',
  'APPROVED'=>'border-blue-300 text-blue-700 bg-blue-50',
  'CONFIRMED'=>'border-sky-300 text-sky-700 bg-sky-50',
  'IN_PROGRESS'=>'border-indigo-300 text-indigo-700 bg-indigo-50',
  'WAITING_PARTS'=>'border-orange-300 text-orange-700 bg-orange-50',
  'DELAYED'=>'border-yellow-300 text-yellow-700 bg-yellow-50',
  'COMPLETED'=>'border-emerald-300 text-emerald-700 bg-emerald-50',
  'CANCELLED'=>'border-rose-300 text-rose-700 bg-rose-50',
];
?>
<div class="max-w-7xl mx-auto px-4 py-10">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold">your appointments</h1>
    <a href="<?= url('appointments/create') ?>" class="px-5 py-2 rounded-full bg-black text-white">Book Appointment</a>
  </div>

  <!-- Search + Filters -->
  <form method="get" class="flex flex-wrap items-center gap-3 mb-5 relative" id="filterForm">
    <input type="text" name="q" value="<?= $e($q ?? '') ?>" placeholder="Search service, vehicle, or plate..."
           class="flex-1 min-w-[260px] border rounded-full px-4 py-2" />

    <button type="button" id="filterBtn"
            class="px-4 py-2 rounded-full border bg-white hover:bg-gray-50">Filters</button>

    <!-- Sort (inline, per figma it sits to the right) -->
    <label class="ml-auto hidden sm:flex items-center gap-2 text-sm text-gray-600">
      <span>Sort</span>
      <select name="sort" class="border rounded-full px-3 py-1">
        <option value="new"     <?= ($sort ?? 'new')==='new'?'selected':'' ?>>Newest</option>
        <option value="old"     <?= ($sort ?? '')==='old'?'selected':'' ?>>Oldest</option>
        <option value="status"  <?= ($sort ?? '')==='status'?'selected':'' ?>>Status</option>
        <option value="service" <?= ($sort ?? '')==='service'?'selected':'' ?>>Service</option>
      </select>
    </label>

    <button class="px-4 py-2 rounded-full border bg-white hover:bg-gray-50">Apply</button>

    <!-- Popover -->
    <div id="filterPanel"
         class="absolute z-10 mt-2 top-12 left-0 w-full sm:w-[620px] bg-white border rounded-2xl shadow-card p-4 hidden">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <?php foreach ($allowedStatuses as $st): ?>
          <label class="inline-flex items-center gap-2 border rounded-full px-3 py-1 text-sm">
            <input type="checkbox" name="status[]" value="<?= $e($st) ?>"
                   <?= in_array($st, $statuses ?? [], true) ? 'checked' : '' ?>>
            <span><?= $e($st) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="filterClose" class="px-4 py-2 rounded-full border">Close</button>
        <button class="px-4 py-2 rounded-full bg-black text-white">Apply Filters</button>
      </div>
    </div>
  </form>

  <?php if (empty($items)): ?>
    <div class="rounded-2xl border bg-white p-6 text-gray-600">
      No appointments found.
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($items as $a): ?>
        <div class="bg-white border rounded-2xl shadow-card p-5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="font-semibold"><?= $e($a['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
              </div>
              <div class="text-sm text-gray-600">
                <?= date('Y-m-d • H:i', strtotime($a['scheduled_at'])) ?>
                <?php if (!empty($a['staff_name'])): ?> • Staff: <?= $e($a['staff_name']) ?><?php endif; ?>
              </div>
              <a href="<?= url('appointments/show?id='.(int)$a['id']) ?>" class="inline-block mt-2 text-sm underline">View</a>
            </div>

            <div class="flex items-center gap-3">
              <span class="px-3 py-1 rounded-full text-xs border <?= $badgeCls[$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50' ?>">
                <?= $e($a['status']) ?>
              </span>

              <?php if ($a['status'] !== 'CANCELLED' && $a['status'] !== 'COMPLETED'): ?>
                <form method="post" action="<?= url('appointments/cancel') ?>">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <button class="px-3 py-1 rounded-full text-xs border">Cancel</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
(function(){
  const btn = document.getElementById('filterBtn');
  const panel = document.getElementById('filterPanel');
  const close = document.getElementById('filterClose');

  function toggle(){ panel.classList.toggle('hidden'); }
  btn && btn.addEventListener('click', toggle);
  close && close.addEventListener('click', toggle);
  document.addEventListener('click', (e)=>{
    if (!panel || panel.classList.contains('hidden')) return;
    if (e.target === btn || panel.contains(e.target)) return;
    panel.classList.add('hidden');
  });
})();
</script>
