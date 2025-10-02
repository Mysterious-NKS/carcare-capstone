<?php
$e = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES);

/** Page-level props for layout */
$sectionTitle = 'customer interactions'; // shown under the tabs

// This page only needs $items (from AdminController::interactions)
$slot = function() use ($items, $e) {
?>
  <div class="card shadow-card p-5">
    <div class="flex items-center justify-between mb-4">
      <div class="font-semibold text-lg">Recent Feedback & Replies</div>
      <div class="text-sm text-gray-500">
        <?= count($items) ?> record<?= count($items)===1?'':'s' ?>
      </div>
    </div>

    <?php if (empty($items)): ?>
      <div class="empty-state">No feedback yet.</div>
    <?php else: ?>
      <ul class="space-y-3 text-sm">
        <?php foreach ($items as $it): ?>
          <li class="border rounded-xl p-4">
            <!-- Feedback header -->
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="font-medium" aria-label="<?= (int)$it['stars'] ?> out of 5 stars">
                  <?= str_repeat('★',(int)$it['stars']) ?><?= str_repeat('☆', 5-(int)$it['stars']) ?>
                </div>
                <?php if (!empty($it['comment'])): ?>
                  <div class="text-gray-800 mt-1"><?= nl2br($e($it['comment'])) ?></div>
                <?php endif; ?>
                <div class="text-xs text-gray-500 mt-1">
                  <?= $e($it['created_at']) ?>
                  <?php if (!empty($it['appointment_id'])): ?>
                    • appt #<?= (int)$it['appointment_id'] ?>
                  <?php endif; ?>
                  • feedback #<?= (int)$it['id'] ?>
                </div>
              </div>

              <!-- Delete feedback -->
              <form method="post"
                    action="<?= url('admin/interactions/feedback/'.(int)$it['id'].'/delete') ?>"
                    onsubmit="return confirm('Delete this feedback (and its replies)?');">
                <button class="px-3 py-1 rounded-full border text-xs text-rose-700 hover:bg-rose-50">
                  Delete feedback
                </button>
              </form>
            </div>

            <!-- Staff reply (if any) -->
            <?php if (!empty($it['reply_id']) && !empty($it['reply_text'])): ?>
              <div class="mt-3 border-t pt-3">
                <div class="uppercase text-[11px] tracking-wide text-gray-500 mb-1">Staff reply</div>
                <div class="text-gray-800"><?= nl2br($e($it['reply_text'])) ?></div>
                <div class="text-xs text-gray-500 mt-1">
                  <?= $e($it['reply_created_at'] ?? '') ?> • reply #<?= (int)$it['reply_id'] ?>
                </div>

                <form class="mt-2" method="post"
                      action="<?= url('admin/interactions/reply/'.(int)$it['reply_id'].'/delete') ?>"
                      onsubmit="return confirm('Delete this reply?');">
                  <button class="px-3 py-1 rounded-full border text-xs">Delete reply</button>
                </form>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
<?php
}; // end $slot
?>

<?php
include view('admin/layout.php');
