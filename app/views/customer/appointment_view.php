<div class="max-w-4xl mx-auto px-4 py-10">
  <a href="<?= url('customer/appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back</a>

  <div class="bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-2xl font-extrabold mb-4"><?= htmlspecialchars($a['service_name']) ?></h1>
    <div class="grid md:grid-cols-2 gap-6">
      <div>
        <div class="text-sm text-gray-600">Vehicle</div>
        <div class="font-medium"><?= htmlspecialchars($a['year'].' '.$a['make'].' '.$a['model']) ?> • <?= htmlspecialchars($a['plate_no']) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Scheduled</div>
        <div class="font-medium"><?= htmlspecialchars($a['scheduled_at']) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Staff</div>
        <div class="font-medium"><?= htmlspecialchars($a['staff_name'] ?: 'Any') ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-600">Estimated</div>
        <div class="font-medium">$<?= number_format($a['price'],2) ?> • <?= $a['est_hours'] ?> hours</div>
      </div>
    </div>
    <?php if (!empty($a['remarks'])): ?>
      <div class="mt-6">
        <div class="text-sm text-gray-600">Remarks</div>
        <div class="font-medium"><?= nl2br(htmlspecialchars($a['remarks'])) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>
