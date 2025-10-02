<?php $e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES); ?>
<!-- User Management -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">
  <!-- List -->
  <div class="lg:col-span-3 card shadow-card p-5">
    <div class="flex items-center justify-between mb-4">
      <div class="font-semibold text-lg">User Management</div>
      <input type="text" class="border rounded-lg px-3 py-2 w-56" placeholder="Search name/email…"
             oninput="filterList(this,'#user-list')">
    </div>

    <?php if (empty($users)): ?>
      <div class="text-sm text-gray-500">No users found.</div>
    <?php else: ?>
      <ul id="user-list" class="space-y-3">
        <?php foreach ($users as $u): ?>
          <?php
            $status = strtoupper($u['status'] ?? 'ACTIVE');
            // badge style by status
            $badgeCls = 'bg-gray-100 text-gray-700 border';
            if ($status === 'ACTIVE')   $badgeCls = 'bg-green-100 text-green-700 border border-green-200';
            if ($status === 'LOCKED')   $badgeCls = 'bg-gray-100 text-gray-700 border';
            if ($status === 'BANNED')   $badgeCls = 'bg-rose-100 text-rose-700 border border-rose-200';
            if ($status === 'INACTIVE') $badgeCls = 'bg-gray-100 text-gray-700 border';

            $btnLabel = ($status === 'ACTIVE') ? 'Lock' : 'Unlock';
          ?>
          <li class="border rounded-xl px-4 py-3 flex items-center justify-between">
            <div class="truncate">
              <div class="flex items-center gap-2">
                <div class="font-medium"><?= $e($u['name'] ?? '—') ?></div>
                <span class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wide <?= $badgeCls ?>">
                  <?= $e($status) ?>
                </span>
              </div>
              <div class="text-xs text-gray-500">
                <?= $e($u['email'] ?? '') ?>
                <?php if (!empty($u['role'])): ?> • <?= $e($u['role']) ?><?php endif; ?>
                <span class="text-gray-400"> • #<?= (int)$u['id'] ?></span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <form method="post" action="<?= url('admin/users/'.(int)$u['id'].'/toggle') ?>">
                <button class="px-3 py-1 rounded-full border text-xs"><?= $btnLabel ?></button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- Add -->
  <div class="lg:col-span-2 card shadow-card p-5">
    <div class="font-semibold mb-4">Add User</div>
    <?php include view('admin/administration/partials/user_form.php'); ?>
  </div>
</div>

<script>
  // simple client-side filter for the list (kept from your previous version)
  function filterList(input, listSelector){
    const q = (input.value || "").toLowerCase();
    document.querySelectorAll(listSelector + " > li").forEach(li=>{
      li.style.display = li.textContent.toLowerCase().includes(q) ? "" : "none";
    });
  }
</script>
