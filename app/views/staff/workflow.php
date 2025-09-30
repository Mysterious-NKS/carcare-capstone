<?php $e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES); ?>
<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold">currently...</h1>
      <p class="text-sm text-gray-500">who’s on your toes..?</p>
    </div>
    <a href="<?= url('staff') ?>" class="btn">Dashboard</a>
  </div>

  <?php include view('partials/staff_tabs.php'); ?>

  <form class="mb-6">
    <label class="text-sm text-gray-600 block mb-1">Select Appointment</label>
    <select name="appointment_id" class="w-full md:w-[520px] border rounded-lg px-3 py-2"
            onchange="this.form.submit()">
      <option value="">—</option>
      <?php foreach($apps as $a): ?>
        <option value="<?= (int)$a['id'] ?>" <?= (!empty($_GET['appointment_id']) && (int)$_GET['appointment_id']===(int)$a['id']?'selected':'') ?>>
          #<?= (int)$a['id'] ?> • <?= date('Y-m-d H:i', strtotime($a['scheduled_at'])) ?> • <?= $e($a['service_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if (!$selected): ?>
    <div class="text-gray-600">Pick an appointment to manage.</div>
  <?php else: ?>
    <!-- INFO STRIP -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Vehicle / Service -->
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3 flex items-center justify-between">
          <span>Vehicle Details</span>
          <span class="status-pill status-<?= $e($selected['status']) ?>"><?= $e($selected['status']) ?></span>
        </div>
        <div class="text-sm text-gray-700">
          <?= $e($selected['year'].' '.$selected['make'].' '.$selected['model']) ?> • Plate <?= $e($selected['plate_no']) ?>
        </div>
        <div class="text-sm text-gray-500 mt-1">Service: <?= $e($selected['service_name']) ?></div>

        <!-- Quick Actions -->
        <div class="mt-4 flex flex-wrap gap-2">
          <!-- Start = IN_PROGRESS -->
          <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>">
            <input type="hidden" name="status" value="IN_PROGRESS">
            <button class="btn btn-soft">Start</button>
          </form>

          <!-- Delay -->
          <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>">
            <input type="hidden" name="status" value="DELAYED">
            <button class="btn btn-soft">Delay</button>
          </form>

          <!-- Confirm -->
          <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>">
            <input type="hidden" name="status" value="CONFIRMED">
            <button class="btn btn-soft">Confirm</button>
          </form>

          <!-- Cancel -->
          <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>" onsubmit="return confirm('Cancel this appointment?');">
            <input type="hidden" name="status" value="CANCELLED">
            <button class="btn btn-danger">Cancel</button>
          </form>

          <!-- Mark Completed -->
          <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>">
            <input type="hidden" name="status" value="COMPLETED">
            <button class="btn btn-primary">Mark Completed</button>
          </form>
        </div>
      </div>

      <!-- Status (Segmented controls) -->
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Service Status</div>
        <form method="post" action="<?= url('appointments/'.$selected['id'].'/status') ?>" class="space-y-4">
          <div class="segmented">
            <?php
              $opts = ['PENDING','CONFIRMED','IN_PROGRESS','DELAYED','CANCELLED','COMPLETED'];
              foreach ($opts as $opt):
                $checked = ($opt === $selected['status']) ? 'checked' : '';
            ?>
              <label class="segmented-item">
                <input type="radio" name="status" value="<?= $opt ?>" <?= $checked ?>>
                <span><?= $opt ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="text-xs text-gray-500">Tip: you can also use the quick action buttons in the Vehicle card.</p>
          <button class="btn">Update Status</button>
        </form>
      </div>
    </div>

    <!-- MAIN GRID -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Inspection / Work -->
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Inspection Report</div>
        <form method="post" action="<?= url('service-records/save') ?>" enctype="multipart/form-data" class="space-y-3">
          <input type="hidden" name="appointment_id" value="<?= (int)$selected['id'] ?>">

          <label class="block">
            <span class="text-sm text-gray-600">Odometer (km)</span>
            <input name="odometer_km" class="w-full border rounded-lg px-3 py-2"
                   value="<?= (int)($record['odometer_km'] ?? 0) ?>">
          </label>

          <label class="block">
            <span class="text-sm text-gray-600">Work Performed</span>
            <textarea name="work_done" rows="4" class="w-full border rounded-lg px-3 py-2"
                      placeholder="Describe the work performed..."><?= $e($record['work_done'] ?? '') ?></textarea>
          </label>

          <label class="block">
            <span class="text-sm text-gray-600">Diagnostics Notes</span>
            <textarea name="diagnostics_notes" rows="4" class="w-full border rounded-lg px-3 py-2"
                      placeholder="Any diagnostics or observations..."><?= $e($record['diagnostics_notes'] ?? '') ?></textarea>
          </label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm text-gray-600">Cost (RM)</span>
              <input name="cost" class="w-full border rounded-lg px-3 py-2"
                     value="<?= $e($record['cost'] ?? '') ?>">
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Photos & Diagnostics</span>
              <input type="file" name="photos[]" multiple class="w-full border rounded-lg px-3 py-2 bg-gray-50">
            </label>
          </div>

          <?php if (!empty($record['photos_json'])):
            $imgs = json_decode((string)$record['photos_json'], true) ?: []; ?>
            <div class="mt-3 grid grid-cols-3 gap-2">
              <?php foreach ($imgs as $img): ?>
                <img src="<?= url('uploads/appointments/'.$selected['id'].'/'.$img) ?>"
                     class="w-full h-24 object-cover rounded-lg border" alt="photo">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <button class="btn btn-primary">Save Record</button>
        </form>
      </div>

      <!-- Comments to Customer -->
      <div class="card shadow-card p-5">
        <div class="font-semibold mb-3">Comments & Advice</div>
        <form method="post" action="<?= url('notifications/custom') ?>" class="space-y-3">
          <input type="hidden" name="user_id" value="<?= (int)$selected['customer_id'] ?>">
          <label class="block">
            <span class="text-sm text-gray-600">Subject</span>
            <input name="title" class="w-full border rounded-lg px-3 py-2"
                   value="Service update for appointment #<?= (int)$selected['id'] ?>">
          </label>
          <label class="block">
            <span class="text-sm text-gray-600">Message</span>
            <textarea name="body" rows="5" class="w-full border rounded-lg px-3 py-2" placeholder="Advice for customer..."></textarea>
          </label>
          <button class="btn">Send to Customer</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
