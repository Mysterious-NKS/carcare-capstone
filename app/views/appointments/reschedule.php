<?php
/** @var array $a  Appointment row with keys: id, service_name, scheduled_at, year, make, model, plate_no */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);

$ts   = strtotime($a['scheduled_at']);
$date = date('Y-m-d', $ts);
$time = date('H:i',   $ts);
?>
<div class="max-w-3xl mx-auto px-4 py-10">
  <a href="<?= url('appointments/'.$a['id']) ?>" class="inline-flex items-center text-sm mb-6 hover:underline">
    ← back to appointment
  </a>

  <div class="bg-white border rounded-2xl shadow-card p-8">
    <h1 class="text-3xl font-extrabold mb-2">Reschedule Appointment</h1>
    <div class="text-gray-600 mb-6">
      <?= $e($a['service_name']) ?> •
      <?= $e($a['year'].' '.$a['make'].' '.$a['model']) ?> •
      Plate <?= $e($a['plate_no']) ?>
    </div>

    <form method="post" action="<?= url('appointments/'.$a['id'].'/reschedule') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label class="text-sm text-gray-600">New Date</label>
        <input type="date" name="date" value="<?= $date ?>" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm text-gray-600">New Time</label>
        <input type="time" name="time" value="<?= $time ?>" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </div>

      <div class="md:col-span-2 mt-2 flex items-center gap-3">
        <a href="<?= url('appointments/'.$a['id']) ?>" class="px-5 py-2 rounded-full border">Cancel</a>
        <button class="px-6 py-3 rounded-full bg-black text-white">Save</button>
      </div>
    </form>
  </div>
</div>
