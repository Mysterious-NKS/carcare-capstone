<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-6">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold mb-2"><?= $e($a['service_name']) ?></h1>
        <div class="text-sm text-gray-600">
          <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
        </div>
        <div class="text-sm text-gray-600">
          When: <?= date('Y-m-d • H:i', strtotime($a['scheduled_at'])) ?>
          <?php if (!empty($a['staff_name'])): ?>
            • Staff: <?= $e($a['staff_name']) ?>
          <?php endif; ?>
        </div>
      </div>

      <span class="px-3 py-1 rounded-full text-xs border
        <?php
          $cls = [
            'PENDING'       =>'border-amber-300 text-amber-700 bg-amber-50',
            'APPROVED'      =>'border-blue-300 text-blue-700 bg-blue-50',
            'IN_PROGRESS'   =>'border-indigo-300 text-indigo-700 bg-indigo-50',
            'WAITING_PARTS' =>'border-orange-300 text-orange-700 bg-orange-50',
            'COMPLETED'     =>'border-emerald-300 text-emerald-700 bg-emerald-50',
            'CANCELLED'     =>'border-rose-300 text-rose-700 bg-rose-50',
            'REJECTED'      =>'border-gray-300 text-gray-700 bg-gray-50',
          ];
          echo $cls[$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';
        ?>">
        <?= $e($a['status']) ?>
      </span>
    </div>

    <?php if ($a['status'] !== 'CANCELLED' && $a['status'] !== 'COMPLETED'): ?>
      <form method="post" action="<?= url('appointments/cancel') ?>" class="mt-6">
        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
        <button class="px-5 py-2 rounded-full border">Cancel Appointment</button>
      </form>
    <?php endif; ?>
  </div>
</div>
