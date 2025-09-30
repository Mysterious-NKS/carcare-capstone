<?php $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES); ?>
<div class="max-w-5xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold">Notifications</h1>
    <?php if (!empty($items)): ?>
      <form method="post" action="<?= url('notifications/mark-read') ?>">
        <button class="px-4 py-2 rounded-full border hover:bg-gray-50">Mark all as read</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div class="text-gray-600">No notifications yet.</div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($items as $n): ?>
        <div class="bg-white border rounded-2xl shadow-card p-4 flex items-start justify-between">
          <div>
            <div class="font-semibold"><?= $e($n['title']) ?></div>
            <div class="text-sm text-gray-600 whitespace-pre-line"><?= $e($n['body']) ?></div>
            <div class="text-xs text-gray-500 mt-1"><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></div>
          </div>

          <div class="text-right ml-4">
            <div class="mb-2">
              <span class="px-2 py-1 rounded-full text-[10px] border <?= $n['is_read'] ? 'bg-gray-50 text-gray-600 border-gray-300' : 'bg-blue-50 text-blue-700 border-blue-300' ?>">
                <?= $n['is_read'] ? 'READ' : 'NEW' ?>
              </span>
            </div>
            <?php if (!$n['is_read']): ?>
              <form method="post" action="<?= url('notifications/mark-read') ?>">
                <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                <button class="text-sm underline hover:opacity-80">mark read</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
