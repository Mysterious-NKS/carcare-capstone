<?php $e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES); ?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold">your clients</h1>
      <p class="text-sm text-gray-500">your workspace for smooth, efficient service.</p>
    </div>
    <a href="<?= url('staff/schedule') ?>" class="btn">Go to Schedule</a>
  </div>

  <?php include view('partials/staff_tabs.php'); ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Feedback list + reply -->
    <div class="lg:col-span-2 card shadow-card p-5">
      <div class="font-semibold mb-3 flex items-center gap-2">
        <?= function_exists('icon') ? icon('chat','h-5 w-5') : '' ?> <span>Customer Feedback</span>
      </div>

      <?php if(empty($rows)): ?>
        <div class="text-gray-600">No feedback yet.</div>
      <?php else: foreach($rows as $r): ?>
        <div class="border rounded-xl p-4 mb-3">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold"><?= $e($r['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= $e($r['year'].' '.$r['make'].' '.$r['model']) ?> • Plate <?= $e($r['plate_no']) ?>
                <span class="ml-2 text-xs text-gray-500"><?= date('Y-m-d H:i', strtotime($r['scheduled_at'])) ?></span>
              </div>
            </div>
            <div class="text-yellow-600 text-lg">
              <?= str_repeat('★', (int)$r['stars']) ?><?= str_repeat('☆', 5-(int)$r['stars']) ?>
            </div>
          </div>

          <?php if(!empty($r['comment'])): ?>
            <div class="text-sm text-gray-700 mt-2"><?= $e($r['comment']) ?></div>
          <?php endif; ?>

          <?php if(!empty($r['staff_reply'])): ?>
            <div class="mt-3 px-4 py-3 rounded-xl border bg-gray-50">
              <div class="text-xs font-semibold text-gray-600 mb-1">Your last reply</div>
              <div class="text-sm text-gray-800 whitespace-pre-line"><?= $e($r['staff_reply']) ?></div>
            </div>
          <?php endif; ?>

          <!-- Reply box -->
          <form method="post" action="<?= url('notifications/custom') ?>" class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-2">
            <input type="hidden" name="type" value="FEEDBACK_REPLY">
            <input type="hidden" name="user_id" value="<?= (int)$r['customer_id'] ?>">
            <input type="hidden" name="title" value="Reply for appointment #<?= (int)$r['appointment_id'] ?>">
            <textarea name="body" rows="2" class="md:col-span-5 w-full border rounded-lg px-3 py-2" placeholder="Write a short reply..."></textarea>
            <button class="btn btn-primary md:col-span-1">Send Reply</button>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Custom reminder (existing) -->
    <div class="card shadow-card p-5">
      <div class="font-semibold mb-3">Send Custom Reminder</div>
      <form method="post" action="<?= url('notifications/custom') ?>" class="space-y-3">
        <label class="block">
          <span class="text-sm text-gray-600">Customer</span>
          <select name="user_id" class="w-full border rounded-lg px-3 py-2">
            <?php foreach($customers as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= $e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="block">
          <span class="text-sm text-gray-600">Subject</span>
          <input name="title" class="w-full border rounded-lg px-3 py-2" placeholder="e.g., Service Reminder">
        </label>
        <label class="block">
          <span class="text-sm text-gray-600">Message</span>
          <textarea name="body" rows="5" class="w-full border rounded-lg px-3 py-2" placeholder="Your message..."></textarea>
        </label>
        <input type="hidden" name="type" value="IN_APP">
        <button class="btn btn-primary"><?= function_exists('icon') ? icon('send','h-4 w-4') : '' ?> <span>Send Reminder</span></button>
      </form>
    </div>
  </div>

  <!-- Maintenance Reminders (optional) -->
  <?php if (!empty($dueReminders)): ?>
    <div class="mt-6 card shadow-card p-5">
      <div class="font-semibold mb-3 flex items-center justify-between">
        <span>Send Maintenance Reminders</span>
        <span class="text-xs text-gray-500"><?= count($dueReminders) ?> due</span>
      </div>

      <div class="space-y-3">
        <?php foreach ($dueReminders as $d): ?>
          <div class="border rounded-xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm">
              <div class="font-medium"><?= $e($d['user_name']) ?></div>
              <div class="text-gray-600">
                <?= $e($d['year'].' '.$d['make'].' '.$d['model']) ?> • Plate <?= $e($d['plate_no']) ?>
                <?php if(!empty($d['due_date'])): ?> • Due <?= $e($d['due_date']) ?><?php endif; ?>
                <?php if(!empty($d['due_mileage'])): ?> • At <?= (int)$d['due_mileage'] ?> km<?php endif; ?>
              </div>
            </div>
            <form method="post" action="<?= url('notifications/custom') ?>" class="flex items-center gap-2">
              <input type="hidden" name="user_id" value="<?= (int)$d['user_id'] ?>">
              <input type="hidden" name="type" value="MAINTENANCE_REMINDER">
              <input type="hidden" name="title" value="Maintenance due for <?= $e($d['plate_no']) ?>">
              <input type="hidden" name="body" value="Hi <?= $e($d['user_name']) ?>, your vehicle <?= $e($d['year'].' '.$d['make'].' '.$d['model']) ?> (<?= $e($d['plate_no']) ?>) is due for service. Please book a slot.">
              <button class="btn btn-primary"><?= function_exists('icon') ? icon('send','h-4 w-4') : '' ?> <span>Send Reminder</span></button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
