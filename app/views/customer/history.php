<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-extrabold mb-6">Service History</h1>

  <?php if (empty($items)): ?>
    <div class="text-gray-600">No service records yet.</div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($items as $r): ?>
        <a href="<?= url('history/detail?id='.(int)$r['id']) ?>"
           class="block bg-white border rounded-2xl shadow-card p-5 hover:bg-gray-50">
          <div class="flex items-start justify-between">
            <div>
              <div class="font-semibold"><?= $e($r['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= $e($r['year'].' '.$r['make'].' '.$r['model']) ?> • Plate <?= $e($r['plate_no']) ?>
              </div>
              <div class="text-sm text-gray-600">
                Appt: <?= date('Y-m-d • H:i', strtotime($r['scheduled_at'])) ?>
                <?php if ($r['completed_at']): ?>
                  • Completed: <?= date('Y-m-d • H:i', strtotime($r['completed_at'])) ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-right">
              <?php if ($r['cost'] !== null): ?>
                <div class="font-semibold">RM <?= number_format((float)$r['cost'], 2) ?></div>
              <?php endif; ?>
              <span class="text-xs text-gray-600"><?= $e($r['status']) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
