<?php
/** @var array $rows */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);
$badge = function($st){
  $map = [
    'PENDING'       =>'border-amber-300 text-amber-700 bg-amber-50',
    'APPROVED'      =>'border-blue-300 text-blue-700 bg-blue-50',
    'IN_PROGRESS'   =>'border-indigo-300 text-indigo-700 bg-indigo-50',
    'WAITING_PARTS' =>'border-orange-300 text-orange-700 bg-orange-50',
    'COMPLETED'     =>'border-emerald-300 text-emerald-700 bg-emerald-50',
    'CANCELLED'     =>'border-rose-300 text-rose-700 bg-rose-50',
    'REJECTED'      =>'border-gray-300 text-gray-700 bg-gray-50',
    'DELAYED'       =>'border-yellow-300 text-yellow-700 bg-yellow-50',
  ];
  return $map[$st] ?? 'border-gray-300 text-gray-700 bg-gray-50';
};
?>
<?php if (empty($rows)): ?>
  <div class="text-gray-600">No records match your filters.</div>
<?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-gray-500">
          <th class="py-2 pr-3">Date</th>
          <th class="py-2 pr-3">Service</th>
          <th class="py-2 pr-3">Vehicle</th>
          <th class="py-2 pr-3">Plate</th>
          <th class="py-2 pr-3">Staff</th>
          <th class="py-2 pr-3">Status</th>
          <th class="py-2 pr-3 text-right">Price (RM)</th>
          <th class="py-2 pr-3 text-right">#</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="py-2 pr-3"><?= $e(date('Y-m-d H:i', strtotime($r['scheduled_at']))) ?></td>
            <td class="py-2 pr-3"><?= $e($r['service_name']) ?></td>
            <td class="py-2 pr-3"><?= $e($r['year'].' '.$r['make'].' '.$r['model']) ?></td>
            <td class="py-2 pr-3"><?= $e($r['plate_no']) ?></td>
            <td class="py-2 pr-3"><?= $e($r['staff_name'] ?? '') ?></td>
            <td class="py-2 pr-3">
              <span class="px-2 py-0.5 rounded-full border <?= $badge($r['status']) ?>">
                <?= $e($r['status']) ?>
              </span>
            </td>
            <td class="py-2 pr-3 text-right"><?= number_format((float)($r['service_price'] ?? 0), 2) ?></td>
            <td class="py-2 pr-3 text-right">
              <a class="underline" href="<?= url('appointments/'.(int)$r['id']) ?>">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
