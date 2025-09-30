<?php
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
$r = $rec; // service record row
$photos = $photos ?? [];
$stars  = isset($stars) ? (int)$stars : null;
$comment= $comment ?? null;
?>
<div class="max-w-4xl mx-auto px-4 py-10">
  <a href="<?= url('history') ?>" class="inline-flex items-center text-sm mb-6 hover:underline">← back to history</a>

  <div class="bg-white border rounded-2xl shadow-card p-6">
    <h1 class="text-2xl font-extrabold mb-1"><?= $e($r['service_name']) ?></h1>
    <div class="text-gray-600 mb-4">
      <?= $e($r['year'].' '.$r['make'].' '.$r['model']) ?> • Plate <?= $e($r['plate_no']) ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <div class="text-sm text-gray-500">Appointment</div>
        <div class="font-semibold"><?= date('Y-m-d • H:i', strtotime($r['scheduled_at'])) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500">Completed</div>
        <div class="font-semibold"><?= $r['completed_at'] ? date('Y-m-d • H:i', strtotime($r['completed_at'])) : '—' ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500">Odometer (km)</div>
        <div class="font-semibold"><?= (int)($r['odometer_km'] ?? 0) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500">Cost</div>
        <div class="font-semibold">RM <?= number_format((float)($r['cost'] ?? 0), 2) ?></div>
      </div>
    </div>

    <?php if (!empty($r['work_done'])): ?>
      <div class="mt-6">
        <div class="text-sm text-gray-500 mb-1">Work Performed</div>
        <div class="whitespace-pre-line"><?= nl2br($e($r['work_done'])) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($r['diagnostics_notes'])): ?>
      <div class="mt-6">
        <div class="text-sm text-gray-500 mb-1">Diagnostics</div>
        <div class="whitespace-pre-line"><?= nl2br($e($r['diagnostics_notes'])) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($photos)): ?>
      <div class="mt-8">
        <div class="text-lg font-semibold mb-3">Service Photos</div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <?php foreach ($photos as $p): ?>
            <a href="<?= url($p) ?>" target="_blank" class="block overflow-hidden rounded-xl border">
              <img src="<?= url($p) ?>" alt="service photo" class="w-full h-40 object-cover">
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="mt-10 pt-6 border-t">
      <div class="text-lg font-semibold mb-3">Submit Your Review</div>

      <?php if ($stars !== null): ?>
        <div class="bg-gray-50 border rounded-xl p-4">
          <div class="font-medium mb-1">Your rating</div>
          <div class="text-yellow-500 text-lg">
            <?= str_repeat('★', (int)$stars) ?><?= str_repeat('☆', 5 - (int)$stars) ?>
          </div>
          <?php if ($comment): ?>
            <div class="text-sm text-gray-700 mt-2"><?= $e($comment) ?></div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <form method="post" action="<?= url('feedback') ?>" class="space-y-3">
          <input type="hidden" name="appointment_id" value="<?= (int)$r['appointment_id'] ?>">
          <!-- send back to this detail page after submit -->
          <input type="hidden" name="return_to" value="<?= $e(url('history/detail?id='.(int)$r['id'])) ?>">

          <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600">Your rating</label>
            <select name="stars" class="border rounded-lg px-3 py-2">
              <?php for ($i=5; $i>=1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> star<?= $i>1?'s':'' ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <div>
            <textarea name="comment" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Share your experience (optional)..."></textarea>
          </div>

          <button class="px-5 py-2 rounded-full bg-black text-white">Submit Review</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
