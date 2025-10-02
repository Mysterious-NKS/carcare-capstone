<?php
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

/** Page-level props for layout */
$sectionTitle = 'smooth op.';   // shown below tabs
$slot = function() use ($metrics, $systemLog, $recentFeedback, $todayAppointments, $staffOptions, $apptScope, $e) {

  $scope = $apptScope ?? (isset($_GET['scope']) ? strtolower((string)$_GET['scope']) : 'all');
  if (!in_array($scope, ['all','today','week'], true)) $scope = 'all';

  $scopeLabel = [
    'all'   => 'All',
    'today' => 'Today',
    'week'  => 'Last 7 Days'
  ][$scope];

  $scopeLink = function($s) {
    $qs = $_GET; $qs['scope'] = $s;
    return url('admin'. (empty($qs) ? '' : ('?'.http_build_query($qs))));
  };
?>
  <!-- Metric tiles (interactive) -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <a class="card shadow-card p-4 block hover:shadow-md transition"
       href="<?= url('admin/reports?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d')) ?>">
      <div class="text-sm text-gray-500">Today’s Appointments</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$metrics['today'] ?></div>
      <div class="text-xs text-gray-500 mt-1">View today in Reports →</div>
    </a>

    <a class="card shadow-card p-4 block hover:shadow-md transition"
       href="<?= url('admin/administration#users') ?>">
      <div class="text-sm text-gray-500">Active Users</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$metrics['active'] ?></div>
      <div class="text-xs text-gray-500 mt-1">Open Users list →</div>
    </a>

    <a class="card shadow-card p-4 block hover:shadow-md transition"
       href="<?= url('admin/administration#vehicles') ?>">
      <div class="text-sm text-gray-500">Vehicles Managed</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$metrics['vehicles'] ?></div>
      <div class="text-xs text-gray-500 mt-1">Open Vehicles list →</div>
    </a>

    <!-- Urgent: tile opens today's report (no status); quick filters are separated below -->
    <a class="card shadow-card p-4 block hover:shadow-md transition"
       href="<?= url('admin/reports?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d')) ?>">
      <div class="text-sm text-gray-500">Urgent Issues</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$metrics['urgent'] ?></div>
      <div class="text-xs text-gray-500 mt-1">See today’s delayed/pending in Reports</div>
    </a>
  </div>

  <!-- Clean quick filters row -->
  <div class="mt-3 text-sm text-gray-600">
    <span class="mr-2">Quick filters:</span>
    <a class="underline hover:no-underline mr-3"
       href="<?= url('admin/reports?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d').'&status=DELAYED') ?>">Delayed</a>
    <a class="underline hover:no-underline"
       href="<?= url('admin/reports?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d').'&status=PENDING') ?>">Pending</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Appointment Manager -->
    <div class="card shadow-card p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold">Appointment Manager</div>

        <!-- Segmented scope control -->
        <div class="segmented">
          <label class="segmented-item">
            <input type="radio" name="scope" <?= $scope==='all'?'checked':'' ?> onchange="location.href='<?= $scopeLink('all') ?>'">
            <span>All</span>
          </label>
          <label class="segmented-item">
            <input type="radio" name="scope" <?= $scope==='today'?'checked':'' ?> onchange="location.href='<?= $scopeLink('today') ?>'">
            <span>Today</span>
          </label>
          <label class="segmented-item">
            <input type="radio" name="scope" <?= $scope==='week'?'checked':'' ?> onchange="location.href='<?= $scopeLink('week') ?>'">
            <span>Last 7 days</span>
          </label>
        </div>
      </div>

      <?php if (empty($todayAppointments)): ?>
        <div class="empty-state">No appointments found.</div>
      <?php else: ?>
        <!-- Scroll container so the panel never grows too tall -->
        <div class="scroll-area">
          <ul class="space-y-3">
            <?php foreach ($todayAppointments as $a): ?>
              <li class="border rounded-xl px-4 py-4 md:min-h-[88px]">
                <?php
                  $row = $a; $rowStaffOptions = $staffOptions;
                  include view('admin/partials/appointment_row.php');
                ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="text-xs text-gray-500 mt-2"><?= count($todayAppointments) ?> shown — scope: <?= $e($scopeLabel) ?></div>
      <?php endif; ?>
    </div>

    <!-- System Log (compact; full page comes in Step 6) -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">System Log</div>
      <?php if (empty($systemLog)): ?>
        <div class="text-gray-500 text-sm">No recent entries.</div>
      <?php else: ?>
        <ul class="text-sm space-y-2">
          <?php foreach ($systemLog as $n): ?>
            <li class="flex items-center justify-between border rounded-lg px-3 py-2">
              <div class="truncate">
                <span class="font-medium"><?= $e($n['type']) ?></span> — <?= $e($n['title']) ?>
              </div>
              <div class="text-xs text-gray-500 ml-3"><?= $e(date('Y-m-d H:i', strtotime($n['created_at']))) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Customer Interactions -->
    <div class="card shadow-card p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold">Customer Interactions</div>
        <a class="btn btn-sm" href="<?= url('admin/interactions') ?>">View all</a>
      </div>

      <?php if (empty($recentFeedback)): ?>
        <div class="text-gray-500 text-sm">No feedback yet.</div>
      <?php else: ?>
        <ul class="text-sm space-y-2">
          <?php foreach ($recentFeedback as $r): ?>
            <li class="border rounded-lg px-3 py-2">
              <div aria-label="<?= (int)$r['stars'] ?> out of 5 stars">
                <?= str_repeat('★',(int)$r['stars']) ?><?= str_repeat('☆',5-(int)$r['stars']) ?>
              </div>
              <?php if(!empty($r['comment'])): ?>
                <div class="text-gray-700"><?= $e($r['comment']) ?></div>
              <?php endif; ?>
              <?php if(!empty($r['reply'])): ?>
                <div class="mt-2 text-gray-600">
                  <span class="text-xs uppercase tracking-wide font-semibold">Staff reply:</span>
                  <div><?= nl2br($e($r['reply'])) ?></div>
                </div>
              <?php endif; ?>
              <div class="text-xs text-gray-500 mt-1"><?= $e(date('Y-m-d H:i', strtotime($r['created_at']))) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Analytics & Reporting -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">Analytics & Reporting</div>
      <div class="flex flex-wrap gap-3">
        <a class="btn" href="<?= url('admin/reports?preset=monthly') ?>">Monthly Report</a>
        <a class="btn" href="<?= url('admin/reports?preset=yearly') ?>">Yearly Report</a>
        <a class="btn" href="<?= url('admin/reports') ?>">Custom Report</a>
      </div>
      <p class="text-xs text-gray-500 mt-3">Use presets or open the full report with filters.</p>
    </div>

<?php }; ?>

<?php
include view('admin/layout.php');
