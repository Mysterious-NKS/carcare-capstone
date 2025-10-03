<?php $e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES); $me = Auth::user(); ?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-4xl font-extrabold">welcome, <?= $e($me['name'] ?? 'staff') ?>.</h1>
      <p class="text-sm text-gray-500 mt-1">Less paperwork, more productivity. Your job made easier.</p>
    </div>
    <a href="<?= url('staff/workflow') ?>" class="btn btn-primary">Add Appointment</a>
  </div>
<?php require_once dirname(__DIR__, 2) . '/helpers/icons.php'; ?>

  <?php include view('partials/staff_tabs.php'); ?>

  <!-- Metric tiles (now dynamic + clickable) -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <a href="<?= $links['todayTasks'] ?>" class="card p-5 shadow-card hover:bg-gray-50 transition block">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Today’s Tasks</span><span class="i-green"><?= icon('check','h-5 w-5') ?></span>
      </div>
      <div class="text-3xl font-extrabold mt-2"><?= (int)($metrics['todayTasks'] ?? count($today)) ?></div>
    </a>

    <a href="<?= $links['feedback'] ?>" class="card p-5 shadow-card hover:bg-gray-50 transition block">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Pending Feedback</span><span class="i-blue"><?= icon('chat','h-5 w-5') ?></span>
      </div>
      <div class="text-3xl font-extrabold mt-2"><?= (int)($metrics['feedback'] ?? count($feedback)) ?></div>
    </a>

    <a href="<?= $links['upcoming'] ?>" class="card p-5 shadow-card hover:bg-gray-50 transition block">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Appointments</span><span class="i-yellow"><?= icon('calendar','h-5 w-5') ?></span>
      </div>
      <div class="text-3xl font-extrabold mt-2"><?= (int)($metrics['upcoming'] ?? 0) ?></div>
    </a>

    <a href="<?= $links['urgent'] ?>" class="card p-5 shadow-card hover:bg-gray-50 transition block">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Urgent Items</span><span class="i-red"><?= icon('alert','h-5 w-5') ?></span>
      </div>
      <div class="text-3xl font-extrabold mt-2"><?= (int)($metrics['urgent'] ?? 0) ?></div>
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Today's Tasks</div>
        <?php if(!$today): ?>
          <div class="text-gray-600">No tasks for today.</div>
        <?php else: foreach($today as $t): ?>
          <div class="border rounded-xl p-4 mb-3">
            <div class="font-semibold"><?= $e($t['service_name']) ?></div>
            <div class="text-sm text-gray-600">
              <?= date('H:i', strtotime($t['scheduled_at'])) ?> •
              <?= $e($t['year'].' '.$t['make'].' '.$t['model']) ?> • Plate <?= $e($t['plate_no']) ?>
              <span class="status-pill status-<?= $e($t['status']) ?> ml-2"><?= $e($t['status']) ?></span>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Pending Feedback</div>
        <?php if(!$feedback): ?>
          <div class="text-gray-600">No recent feedback.</div>
        <?php else: foreach($feedback as $f): ?>
          <div class="border rounded-xl p-4 mb-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-semibold"><?= $e($f['service_name']) ?></div>
                <div class="text-sm text-gray-600">
                  <?= $e($f['year'].' '.$f['make'].' '.$f['model']) ?> • Plate <?= $e($f['plate_no']) ?>
                </div>
              </div>
              <div class="text-yellow-600 text-lg">
                <?= str_repeat('★', (int)$f['stars']) ?><?= str_repeat('☆', 5-(int)$f['stars']) ?>
              </div>
            </div>
            <?php if(!empty($f['comment'])): ?>
              <div class="text-sm text-gray-700 mt-2"><?= $e($f['comment']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="space-y-4">
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Recent Notifications</div>
        <?php if(!$notes): ?>
          <div class="text-gray-600">No notifications.</div>
        <?php else: foreach($notes as $n): ?>
          <div class="border rounded-xl p-4 mb-3">
            <div class="font-semibold"><?= $e($n['title']) ?></div>
            <div class="text-sm text-gray-600"><?= nl2br($e($n['body'])) ?></div>
            <div class="text-xs text-gray-500 mt-1"><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Upcoming Appointments</div>
        <a href="<?= url('staff/schedule') ?>" class="underline text-sm">Open schedule</a>
      </div>
    </div>
  </div>
</div>
