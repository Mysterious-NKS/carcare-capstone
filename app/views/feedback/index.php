<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-extrabold mb-6">Feedback</h1>

  <?php if (empty($rows)): ?>
    <div class="text-gray-600">No completed services yet.</div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($rows as $row): ?>
        <div class="bg-white border rounded-2xl shadow-card p-5">
          <div class="flex items-start justify-between">
            <div>
              <div class="font-semibold"><?= $e($row['service_name']) ?></div>
              <div class="text-sm text-gray-600">
                <?= $e($row['year'].' '.$row['make'].' '.$row['model']) ?> • Plate <?= $e($row['plate_no']) ?>
              </div>
              <div class="text-sm text-gray-600">
                <?= date('Y-m-d • H:i', strtotime($row['scheduled_at'])) ?>
              </div>
            </div>

            <?php if ($row['stars']): ?>
              <div class="text-right">
                <div class="font-semibold"><?= str_repeat('★', (int)$row['stars']) ?><?= str_repeat('☆', 5-(int)$row['stars']) ?></div>
                <?php if ($row['comment']): ?>
                  <div class="text-sm text-gray-600 max-w-sm mt-1"><?= $e($row['comment']) ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <?php if (!empty($row['staff_reply'])): ?>
            <div class="mt-3 px-4 py-3 rounded-xl border bg-gray-50">
              <div class="text-xs font-semibold text-gray-600 mb-1">Staff reply</div>
              <div class="text-sm text-gray-800 whitespace-pre-line"><?= $e($row['staff_reply']) ?></div>
            </div>
          <?php endif; ?>

          <div class="mt-4">
            <form method="post" action="<?= url('feedback') ?>" class="flex flex-col md:flex-row md:items-center gap-3">
              <input type="hidden" name="appointment_id" value="<?= (int)$row['id'] ?>">
              <label class="text-sm text-gray-600">Your rating</label>
              <select name="stars" class="border rounded-lg px-3 py-2">
                <?php for ($i=5; $i>=1; $i--): ?>
                  <option value="<?= $i ?>" <?= ((int)($row['stars'] ?? 0) === $i) ? 'selected' : '' ?>><?= $i ?> star<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
              <input name="comment" class="flex-1 border rounded-lg px-3 py-2" placeholder="Optional comment..." value="<?= $e($row['comment'] ?? '') ?>">
              <button class="px-4 py-2 rounded-full border">Save</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
