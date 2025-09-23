<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-7xl mx-auto px-4 py-10">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold">your appointments</h1>
    <a href="<?= url('appointments/create') ?>" class="px-5 py-2 rounded-full bg-black text-white">Book Appointment</a>
  </div>

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
          </div>

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
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
