<?php
/** Expected:
 *  $row             — appointment row
 *  $rowStaffOptions — array of ['id'=>..,'name'=>..]
 */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

$veh = trim(($row['year'] ?? '').' '.($row['make'] ?? '').' '.($row['model'] ?? ''));
$veh = preg_replace('/\s+/', ' ', $veh);
$plate  = $row['plate_no'] ?? '';
$when   = !empty($row['scheduled_at']) ? date('Y-m-d H:i', strtotime($row['scheduled_at'])) : '';
$status = $row['status'] ?? '';
$staff  = $row['staff_name'] ?? '';

$badge = 'bg-gray-100 text-gray-700';
if ($status==='COMPLETED')       $badge = 'bg-green-100 text-green-700';
elseif ($status==='IN_PROGRESS') $badge = 'bg-blue-100 text-blue-700';
elseif ($status==='DELAYED')     $badge = 'bg-orange-100 text-orange-700';
elseif ($status==='PENDING')     $badge = 'bg-yellow-100 text-yellow-800';
?>
<!-- Two-column layout: left text column (~60%), right actions (~40%).
     Left column never overlaps the selects/buttons. -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:items-center">
  <!-- LEFT (text only) -->
  <div class="md:col-span-7 min-w-0">
    <!-- Service name -->
    <div class="font-medium truncate">
      <?= $e($row['service_name'] ?? 'Service') ?>
    </div>

    <!-- Vehicle (full, own line) -->
    <div class="text-sm text-gray-600 truncate">
      <?= $e($veh ?: 'Vehicle') ?>
      <?php if ($plate): ?><span class="text-gray-400"> • <?= $e($plate) ?></span><?php endif; ?>
    </div>

    <!-- Date/time -->
    <div class="text-xs text-gray-500"><?= $e($when) ?></div>

    <!-- Status + Staff (each on its own line for room) -->
    <div class="mt-2 flex flex-wrap items-center gap-2">
      <span class="inline-block rounded-full px-2 py-0.5 text-xs <?= $badge ?>"><?= $e($status) ?></span>
    </div>

    <?php if ($staff): ?>
      <div class="text-sm text-gray-700 truncate mt-1">
        <span class="text-gray-500">Staff:</span> <?= $e($staff) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT (actions only) -->
  <div class="md:col-span-5">
    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 justify-end">
      <?php if (!empty($rowStaffOptions)): ?>
        <form class="flex items-center gap-2" method="post" action="<?= url('admin/appointments/'.$e($row['id']).'/assign-staff') ?>">
          <select name="staff_id" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">Assign staff…</option>
            <?php foreach ($rowStaffOptions as $st): ?>
              <option value="<?= (int)$st['id'] ?>"><?= $e($st['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm">Assign</button>
        </form>
      <?php endif; ?>

      <form class="flex items-center gap-2" method="post" action="<?= url('admin/appointments/'.$e($row['id']).'/status') ?>">
        <select name="status" class="border rounded px-2 py-1 text-sm w-44">
          <?php
            $opts = ['PENDING','APPROVED','CONFIRMED','IN_PROGRESS','WAITING_PARTS','DELAYED','COMPLETED','CANCELLED','REJECTED'];
            foreach ($opts as $opt):
          ?>
            <option value="<?= $opt ?>" <?= $opt===$status?'selected':'' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm">Update</button>
      </form>
    </div>
  </div>
</div>
