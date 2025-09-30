<?php
// appointments list
// expects $items
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
?>
<div class="max-w-7xl mx-auto px-4 py-10">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold">your appointments</h1>
    <a href="<?= url('appointments/create') ?>" class="px-5 py-2 rounded-full bg-black text-white">Book Appointment</a>
  </div>

  <?php if (!empty($_SESSION['flash'])): ?>
    <?php $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="mb-6 rounded-2xl border px-4 py-3 text-sm
                <?= isset($f['ok']) ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                    : 'border-rose-200 bg-rose-50 text-rose-800' ?>">
      <?= $e($f['ok'] ?? $f['err'] ?? '') ?>
    </div>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="rounded-2xl border bg-white p-6 text-gray-600">
      No appointments yet. Book your first one to get started.
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($items as $a): ?>
        <div class="bg-white border rounded-2xl shadow-card p-5 flex items-start justify-between">
          <div>
            <div class="font-semibold"><?= $e($a['service_name']) ?></div>
            <div class="text-sm text-gray-600">
              <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
            </div>
            <div class="text-sm text-gray-600">
              <?= date('Y-m-d • H:i', strtotime($a['scheduled_at'])) ?>
              <?php if (!empty($a['staff_name'])): ?>
                • Staff: <?= $e($a['staff_name']) ?>
              <?php endif; ?>
            </div>
            <div class="mt-3">
              <a href="<?= url('appointments/view?id='.(int)$a['id']) ?>" class="text-sm underline">View</a>
            </div>
          </div>

          <div class="flex flex-col items-end gap-3">
            <span class="px-3 py-1 rounded-full text-xs border
                <?php
                  $cls = [
                    'PENDING'=>'border-amber-300 text-amber-700 bg-amber-50',
                    'APPROVED'=>'border-blue-300 text-blue-700 bg-blue-50',
                    'IN_PROGRESS'=>'border-indigo-300 text-indigo-700 bg-indigo-50',
                    'WAITING_PARTS'=>'border-orange-300 text-orange-700 bg-orange-50',
                    'COMPLETED'=>'border-emerald-300 text-emerald-700 bg-emerald-50',
                    'CANCELLED'=>'border-rose-300 text-rose-700 bg-rose-50',
                    'REJECTED'=>'border-gray-300 text-gray-700 bg-gray-50',
                  ];
                  echo $cls[$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';
                ?>">
              <?= $e($a['status']) ?>
            </span>

            <?php if (in_array($a['status'], ['PENDING','APPROVED'], true)): ?>
              <form method="post"
                    action="<?= url('appointments/cancel?id='.(int)$a['id']) ?>"
                    onsubmit="return confirm('Cancel this appointment?');">
                <button class="px-3 py-1 rounded-full border text-rose-700 hover:bg-rose-50 text-xs">
                  Cancel
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
