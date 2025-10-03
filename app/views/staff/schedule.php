<?php require_once dirname(__DIR__, 2) . '/helpers/icons.php'; ?>

<?php $e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES); ?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold">coming through!</h1>
      <p class="text-sm text-gray-500">who’s booked and waiting?</p>
    </div>
    <a href="<?= url('staff/workflow') ?>" class="btn btn-primary">Go to Workflow</a>
  </div>

  <?php include view('partials/staff_tabs.php'); ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Today's Schedule -->
    <div class="lg:col-span-2 card shadow-card p-5">
      <div class="font-semibold mb-3 flex items-center gap-2">
        <?= icon('calendar','h-5 w-5') ?> Today’s Schedule • (<?= date('Y-m-d') ?>)
      </div>

      <?php if(!$today): ?>
        <div class="text-gray-600">No appointments today.</div>
      <?php else: foreach($today as $t): ?>
        <div class="border rounded-xl p-4 mb-3">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold"><?= $e($t['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= date('H:i', strtotime($t['scheduled_at'])) ?> •
                <?= $e($t['year'].' '.$t['make'].' '.$t['model']) ?> • Plate <?= $e($t['plate_no']) ?>
                <span class="status-pill status-<?= $e($t['status']) ?> ml-2"><?= $e($t['status']) ?></span>
              </div>
            </div>

            <!-- Clear, differentiated actions -->
            <div class="flex items-center gap-2">
              <!-- “Confirm arrival / check-in” -->
              <form method="post" action="<?= url('appointments/'.$t['id'].'/status') ?>">
                <input type="hidden" name="status" value="CONFIRMED">
                <button class="btn btn-soft text-xs" title="Confirm arrival">Confirm</button>
              </form>

              <!-- “Start work” (mark in progress) -->
              <form method="post" action="<?= url('appointments/'.$t['id'].'/status') ?>">
                <input type="hidden" name="status" value="IN_PROGRESS">
                <button class="btn text-xs" title="Mark as in progress">Start</button>
              </form>

              <!-- “Mark completed” -->
              <form method="post" action="<?= url('appointments/'.$t['id'].'/status') ?>">
                <input type="hidden" name="status" value="COMPLETED">
                <button class="btn text-xs" title="Mark as completed">Complete</button>
              </form>

              <!-- “Cancel” -->
              <form method="post" action="<?= url('appointments/'.$t['id'].'/status') ?>">
                <input type="hidden" name="status" value="CANCELLED">
                <button class="btn btn-danger text-xs" title="Cancel appointment">Cancel</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Quick Reschedule (no more typing IDs) -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">Quick Reschedule</div>

      <?php
        // Build a small, safe picker using the data we already have on the page.
        // Merge lists and de-duplicate by id.
        $forRes = [];
        foreach ([$today, $upcoming, $pending] as $group) {
          foreach ($group as $row) { $forRes[$row['id']] = $row; }
        }
        $forRes = array_values($forRes);
        usort($forRes, fn($a,$b)=>strcmp($a['scheduled_at'],$b['scheduled_at']));
      ?>

      <?php if (empty($forRes)): ?>
        <div class="text-gray-600">No appointments available to reschedule.</div>
      <?php else: ?>
        <form method="post" action="" id="resForm" class="space-y-3">
          <label class="block">
            <span class="text-sm text-gray-600">Select appointment</span>
            <select id="res_appt" class="w-full border rounded-lg px-3 py-2">
              <?php foreach ($forRes as $r): ?>
                <option value="<?= (int)$r['id'] ?>">
                  #<?= (int)$r['id'] ?> • <?= date('Y-m-d H:i', strtotime($r['scheduled_at'])) ?>
                  • <?= $e($r['service_name']) ?> • <?= $e($r['plate_no']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm text-gray-600">New Date</span>
              <input type="date" id="res_date" class="w-full border rounded-lg px-3 py-2">
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">New Time</span>
              <input type="time" id="res_time" class="w-full border rounded-lg px-3 py-2">
            </label>
          </div>

          <input type="hidden" name="scheduled_at" id="res_when">
          <button class="btn" onclick="event.preventDefault(); quickReschedulePicker();">Save</button>
        </form>

        <script>
          function quickReschedulePicker(){
            const sel  = document.getElementById('res_appt');
            const date = document.getElementById('res_date').value.trim();
            const time = document.getElementById('res_time').value.trim();

            if(!sel || !sel.value){ alert('Choose an appointment.'); return; }
            if(!date || !time){ alert('Choose the new date & time.'); return; }

            const when = date + ' ' + time + ':00';
            document.getElementById('res_when').value = when;

            const form = document.getElementById('resForm');
            form.action = '<?= url('appointments') ?>/' + sel.value + '/staff-reschedule';
            form.submit();
          }
        </script>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upcoming & Pending -->
  <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- UPCOMING -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">Upcoming Appointments</div>
      <?php if(!$upcoming): ?>
        <div class="text-gray-600">Nothing upcoming.</div>
      <?php else: foreach($upcoming as $u): ?>
        <div class="border rounded-xl p-4 mb-3">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold"><?= $e($u['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= date('Y-m-d H:i', strtotime($u['scheduled_at'])) ?> •
                <?= $e($u['year'].' '.$u['make'].' '.$u['model']) ?> • Plate <?= $e($u['plate_no']) ?>
                <span class="status-pill status-<?= $e($u['status']) ?> ml-2"><?= $e($u['status']) ?></span>
              </div>
            </div>

            <!-- “Confirm” here = confirm upcoming slot -->
            <form method="post" action="<?= url('appointments/'.$u['id'].'/status') ?>">
              <input type="hidden" name="status" value="CONFIRMED">
              <button class="btn btn-soft text-xs" title="Confirm booking">Confirm</button>
            </form>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- PENDING REQUESTS -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">Pending Booking Request</div>
      <?php if(!$pending): ?>
        <div class="text-gray-600">No pending requests.</div>
      <?php else: foreach($pending as $p): ?>
        <div class="border rounded-xl p-4 mb-3">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold"><?= $e($p['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= date('Y-m-d H:i', strtotime($p['scheduled_at'])) ?> •
                <?= $e($p['year'].' '.$p['make'].' '.$p['model']) ?> • Plate <?= $e($p['plate_no']) ?>
                <span class="status-pill status-<?= $e($p['status']) ?> ml-2"><?= $e($p['status']) ?></span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <!-- Approve (becomes CONFIRMED) -->
              <form method="post" action="<?= url('appointments/'.$p['id'].'/status') ?>">
                <input type="hidden" name="status" value="CONFIRMED">
                <button class="btn btn-soft text-xs" title="Approve request">Approve</button>
              </form>

              <!-- Reject (becomes CANCELLED) -->
              <form method="post" action="<?= url('appointments/'.$p['id'].'/status') ?>">
                <input type="hidden" name="status" value="CANCELLED">
                <button class="btn btn-danger text-xs" title="Reject request">Reject</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
