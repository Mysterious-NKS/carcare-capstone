<?php $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-8">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-extrabold mb-1"><?= $e($a['service_name']) ?></h1>
        <div class="text-gray-600">
          <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
        </div>
      </div>

      <?php
        $badge = [
          'PENDING'       => 'border-amber-300 text-amber-700 bg-amber-50',
          'APPROVED'      => 'border-blue-300 text-blue-700 bg-blue-50',
          'IN_PROGRESS'   => 'border-indigo-300 text-indigo-700 bg-indigo-50',
          'WAITING_PARTS' => 'border-orange-300 text-orange-700 bg-orange-50',
          'COMPLETED'     => 'border-emerald-300 text-emerald-700 bg-emerald-50',
          'CANCELLED'     => 'border-rose-300 text-rose-700 bg-rose-50',
          'REJECTED'      => 'border-gray-300 text-gray-700 bg-gray-50',
        ][$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';
      ?>
      <span class="px-3 py-1 rounded-full text-xs border <?= $badge; ?>">
        <?= $e($a['status']) ?>
      </span>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <div class="text-sm text-gray-500">When</div>
        <div class="font-semibold"><?= date('Y-m-d • H:i', strtotime($a['scheduled_at'])) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500">Staff</div>
        <div class="font-semibold"><?= $e($a['staff_name'] ?: 'TBA') ?></div>
      </div>
    </div>

    <?php if (in_array($a['status'], ['PENDING','APPROVED'], true)): ?>
      <form method="post"
            action="<?= url('appointments/cancel?id='.(int)$a['id']) ?>"
            class="mt-8"
            onsubmit="return confirm('Cancel this appointment?');">
        <button class="px-4 py-2 rounded-full border text-rose-700 hover:bg-rose-50">
          Cancel Appointment
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>
