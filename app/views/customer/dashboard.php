<?php
// expected: $metrics (['upcoming'=>..,'vehicles'=>..,'due'=>..]), $recent (array)
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$m = $metrics ?? ['upcoming' => 0, 'vehicles' => 0, 'due' => 0];
?>
<div class="max-w-7xl mx-auto px-4 py-10">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-4xl font-extrabold">
      welcome, <?= $e($_SESSION['user']['name'] ?? 'there') ?>.
    </h1>
    <div class="flex gap-3">
      <a href="<?= url('customer/vehicles') ?>" class="px-4 py-2 rounded-full border">My Vehicles</a>
      <a href="<?= url('appointments/create') ?>" class="px-4 py-2 rounded-full bg-black text-white">Book Appointment</a>
    </div>
  </div>

  <!-- tabs -->
  <div class="flex gap-3 mb-8">
    <a href="<?= url('dashboard') ?>" class="px-4 py-2 rounded-full border bg-white">Overview</a>
    <a href="<?= url('customer/vehicles') ?>" class="px-4 py-2 rounded-full border">My Vehicles</a>
    <a href="<?= url('appointments') ?>" class="px-4 py-2 rounded-full border">Appointments</a>
    <a href="<?= url('notifications') ?>" class="px-4 py-2 rounded-full border">Notifications</a>
    <a href="<?= url('feedback') ?>" class="px-4 py-2 rounded-full border">Feedback</a>
  </div>

  <!-- KPI cards (single source of truth: $metrics) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white border rounded-2xl shadow-card p-6">
      <div class="text-gray-600">Upcoming Appointments</div>
      <div class="text-5xl font-black mt-2"><?= (int)$m['upcoming'] ?></div>
    </div>
    <div class="bg-white border rounded-2xl shadow-card p-6">
      <div class="text-gray-600">Registered Vehicles</div>
      <div class="text-5xl font-black mt-2"><?= (int)$m['vehicles'] ?></div>
    </div>
    <div class="bg-white border rounded-2xl shadow-card p-6">
      <div class="text-gray-600">Maintenance Due</div>
      <div class="text-5xl font-black mt-2"><?= (int)$m['due'] ?></div>
    </div>
  </div>

  <!-- Recent Activity -->
  <h2 class="text-2xl font-bold mt-10 mb-4">Recent Activity</h2>

  <?php if (empty($recent)): ?>
    <div class="text-gray-600">No activity yet. Book your first appointment to see it here.</div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($recent as $r): ?>
        <div class="bg-white border rounded-2xl shadow-card p-5 flex items-start justify-between">
          <div>
            <div class="font-semibold"><?= $e($r['service_name']) ?></div>
            <div class="text-sm text-gray-600">
              <?= $e($r['year'].' '.$r['make'].' '.$r['model']) ?>
              • Plate <?= $e($r['plate_no']) ?>
            </div>
            <div class="text-sm text-gray-600">
              <?= date('Y-m-d • H:i', strtotime($r['scheduled_at'])) ?>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-xs border
              <?php
                $cls = [
                  'PENDING'      => 'border-amber-300 text-amber-700 bg-amber-50',
                  'APPROVED'     => 'border-blue-300 text-blue-700 bg-blue-50',
                  'IN_PROGRESS'  => 'border-indigo-300 text-indigo-700 bg-indigo-50',
                  'WAITING_PARTS'=> 'border-orange-300 text-orange-700 bg-orange-50',
                  'COMPLETED'    => 'border-emerald-300 text-emerald-700 bg-emerald-50',
                  'CANCELLED'    => 'border-rose-300 text-rose-700 bg-rose-50',
                  'REJECTED'     => 'border-gray-300 text-gray-700 bg-gray-50',
                ];
                echo $cls[$r['status']] ?? 'border-gray-300 text-gray-700 bg-gray-50';
              ?>">
            <?= $e($r['status']) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
