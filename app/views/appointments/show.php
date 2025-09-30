<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
/** @var array $a */
$badge = [
  'PENDING'=>'border-amber-300 text-amber-700 bg-amber-50',
  'APPROVED'=>'border-blue-300 text-blue-700 bg-blue-50',
  'CONFIRMED'=>'border-sky-300 text-sky-700 bg-sky-50',
  'IN_PROGRESS'=>'border-indigo-300 text-indigo-700 bg-indigo-50',
  'WAITING_PARTS'=>'border-orange-300 text-orange-700 bg-orange-50',
  'DELAYED'=>'border-yellow-300 text-yellow-700 bg-yellow-50',
  'COMPLETED'=>'border-emerald-300 text-emerald-700 bg-emerald-50',
  'CANCELLED'=>'border-rose-300 text-rose-700 bg-rose-50',
][$a['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';

// Optional fields if you later store them in service_records, prevent notices now
$workDone = trim($a['work_done'] ?? '');
$diagNotes = trim($a['diagnostics_notes'] ?? '');
?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <a href="<?= url('appointments') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to appointments</a>

  <div class="bg-white border rounded-2xl shadow-card p-6 md:p-8">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-extrabold mb-1"><?= $e($a['service_name']) ?></h1>
        <div class="text-sm text-gray-600">
          <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> • Plate <?= $e($a['plate_no']) ?>
        </div>
      </div>
      <span class="px-3 py-1 rounded-full text-xs border <?= $badge ?>">
        <?= $e($a['status']) ?>
      </span>
    </div>

    <!-- Four info cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Service Date</div>
        <div class="mt-1 font-semibold"><?= date('M d, Y • H:i', strtotime($a['scheduled_at'])) ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Technician</div>
        <div class="mt-1 font-semibold"><?= $e($a['staff_name'] ?: 'TBA') ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Vehicle</div>
        <div class="mt-1 font-semibold"><?= $e($a['model']) ?> (<?= $e($a['make']) ?>) • <?= $e($a['plate_no']) ?></div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="text-xs text-gray-500">Estimated Cost</div>
        <div class="mt-1 font-semibold">RM <?= number_format((float)($a['service_price'] ?? 0), 2) ?></div>
        <?php if (!empty($a['est_hours'] ?? null)): ?>
          <div class="text-xs text-gray-500 mt-1"><?= $e($a['est_hours']) ?> hours</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Work & Diagnostics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
      <div class="border rounded-xl p-4">
        <div class="font-semibold mb-2">Work Performed</div>
        <div class="text-sm text-gray-800 whitespace-pre-line">
          <?= $workDone !== '' ? $e($workDone) : '—' ?>
        </div>
      </div>
      <div class="border rounded-xl p-4">
        <div class="font-semibold mb-2">Diagnostics Notes</div>
        <div class="text-sm text-gray-800 whitespace-pre-line">
          <?= $diagNotes !== '' ? $e($diagNotes) : '—' ?>
        </div>
      </div>
    </div>

    <?php if (!in_array($a['status'], ['CANCELLED','COMPLETED'], true)): ?>
      <div class="mt-8 flex gap-3">
        <a href="<?= url('appointments/'.$a['id'].'/reschedule') ?>"
           class="px-5 py-2 rounded-full border hover:bg-gray-50">Reschedule</a>

        <form method="post" action="<?= url('appointments/cancel') ?>"
              onsubmit="return confirm('Cancel this appointment?');">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="px-5 py-2 rounded-full border text-rose-700 hover:bg-rose-50">Cancel Appointment</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
