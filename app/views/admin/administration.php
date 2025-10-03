<?php
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

/** Page-level props for layout */
$sectionTitle = "the driver’s seat."; // shown below tabs

$slot = function() use ($vehicles, $vehPager, $users, $userQ, $e) {
?>
  <!-- Local anchor tabs -->
  <div class="mb-4 flex items-center gap-2 text-sm">
    <a href="#vehicles" class="btn btn-sm">Vehicles</a>
    <a href="#users" class="btn btn-sm">Users</a>
  </div>

  <!-- ========================= -->
  <!-- Vehicle Management        -->
  <!-- ========================= -->
  <section id="vehicles" class="scroll-mt-24">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- List -->
      <div class="lg:col-span-3 card shadow-card p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="font-semibold text-lg">Vehicle Management</div>

          <form method="get" action="<?= url('admin/administration#vehicles') ?>" class="flex items-center gap-2">
            <input type="hidden" name="page" value="1">
            <input type="text" name="q" value="<?= $e($vehPager['q'] ?? '') ?>"
                   class="border rounded-lg px-3 py-2 w-56" placeholder="Search plate, make, model, ID">
            <button class="btn btn-sm">Search</button>
          </form>
        </div>

        <?php if (empty($vehicles)): ?>
          <div class="empty-state">No vehicles found.</div>
        <?php else: ?>
          <ul class="space-y-3">
            <?php foreach ($vehicles as $v): ?>
              <li class="border rounded-xl p-4">
                <!-- Row header -->
                <div class="flex items-center justify-between gap-3">
                  <div class="min-w-0">
                    <div class="font-medium truncate">
                      <?= $e(trim(($v['year']??'').' '.($v['make']??'').' '.($v['model']??''))) ?>
                      <?php if(!empty($v['plate_no'])): ?>
                        <span class="text-xs text-gray-500 ml-2">• <?= $e($v['plate_no']) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-500">#<?= (int)$v['id'] ?></div>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <button type="button" class="btn btn-sm" data-toggle="veh-view-<?= (int)$v['id'] ?>">View</button>
                    <button type="button" class="btn btn-sm" data-toggle="veh-edit-<?= (int)$v['id'] ?>">Edit</button>
                    <form method="post" action="<?= url('admin/vehicles/'.(int)$v['id'].'/delete') ?>"
                          onsubmit="return confirm('Delete vehicle #<?= (int)$v['id'] ?>?');">
                      <button class="px-3 py-1 rounded-full border text-xs text-rose-700 hover:bg-rose-50">Delete</button>
                    </form>
                  </div>
                </div>

                <!-- View panel -->
                <div id="veh-view-<?= (int)$v['id'] ?>" class="mt-3 hidden">
                  <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">Year:</span> <?= $e($v['year'] ?? '—') ?></div>
                    <div><span class="text-gray-500">Make:</span> <?= $e($v['make'] ?? '—') ?></div>
                    <div><span class="text-gray-500">Model:</span> <?= $e($v['model'] ?? '—') ?></div>
                    <div><span class="text-gray-500">Plate:</span> <?= $e($v['plate_no'] ?? '—') ?></div>
                    <?php if (!empty($v['user_id'])): ?>
                      <div><span class="text-gray-500">Owner (ID):</span> #<?= (int)$v['user_id'] ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Edit panel -->
                <div id="veh-edit-<?= (int)$v['id'] ?>" class="mt-3 hidden">
                  <form method="post" action="<?= url('admin/vehicles/'.(int)$v['id'].'/update') ?>" class="grid sm:grid-cols-2 gap-3 text-sm">
                    <label class="block">
                      <span class="text-gray-600 text-xs">Year</span>
                      <input name="year" value="<?= $e($v['year']) ?>" class="w-full border rounded-lg px-3 py-2">
                    </label>
                    <label class="block">
                      <span class="text-gray-600 text-xs">Make</span>
                      <input name="make" value="<?= $e($v['make']) ?>" class="w-full border rounded-lg px-3 py-2">
                    </label>
                    <label class="block">
                      <span class="text-gray-600 text-xs">Model</span>
                      <input name="model" value="<?= $e($v['model']) ?>" class="w-full border rounded-lg px-3 py-2">
                    </label>
                    <label class="block">
                      <span class="text-gray-600 text-xs">Plate</span>
                      <input name="plate_no" value="<?= $e($v['plate_no']) ?>" class="w-full border rounded-lg px-3 py-2">
                    </label>
                    <label class="block sm:col-span-2">
                      <span class="text-gray-600 text-xs">Owner (User ID)</span>
                      <input name="user_id" value="<?= (int)($v['user_id'] ?? 0) ?>" class="w-full border rounded-lg px-3 py-2" inputmode="numeric">
                    </label>
                    <div class="sm:col-span-2">
                      <button class="btn btn-sm">Save changes</button>
                      <button type="button" class="btn btn-sm" data-toggle="veh-edit-<?= (int)$v['id'] ?>">Cancel</button>
                    </div>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>

          <!-- Pagination -->
          <?php
            $p = $vehPager;
            $q = $p['q'] ?? '';
            $link = function($page) use($q) {
              $qs = http_build_query(['q'=>$q, 'page'=>$page]);
              return url('admin/administration?'.$qs).'#vehicles';
            };
          ?>
          <div class="flex items-center justify-between mt-4 text-sm">
            <div class="text-gray-500">
              Page <?= (int)$p['page'] ?> of <?= (int)$p['pages'] ?> • <?= (int)$p['total'] ?> total
            </div>
            <div class="flex items-center gap-2">
              <?php if ($p['has_prev']): ?>
                <a class="btn btn-sm" href="<?= $link($p['page']-1) ?>">Prev</a>
              <?php else: ?>
                <span class="btn btn-sm text-gray-400 cursor-not-allowed">Prev</span>
              <?php endif; ?>
              <?php if ($p['has_next']): ?>
                <a class="btn btn-sm" href="<?= $link($p['page']+1) ?>">Next</a>
              <?php else: ?>
                <span class="btn btn-sm text-gray-400 cursor-not-allowed">Next</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Add -->
      <div class="lg:col-span-2 card shadow-card p-5">
        <div class="font-semibold mb-4">Add Vehicle</div>
        <form method="post" action="<?= url('admin/vehicles') ?>" class="space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm text-gray-600">Year</span>
              <input name="year" class="w-full border rounded-lg px-3 py-2" placeholder="2020" inputmode="numeric">
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Make</span>
              <input name="make" class="w-full border rounded-lg px-3 py-2" placeholder="Honda" required>
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Model</span>
              <input name="model" class="w-full border rounded-lg px-3 py-2" placeholder="Civic" required>
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Plate No.</span>
              <input name="plate_no" class="w-full border rounded-lg px-3 py-2" placeholder="ABC1234" required>
            </label>
            <label class="block md:col-span-2">
              <span class="text-sm text-gray-600">Assign to User (ID)</span>
              <input name="user_id" class="w-full border rounded-lg px-3 py-2" placeholder="e.g., 7">
            </label>
          </div>
          <div class="pt-2"><button class="btn">Add Vehicle</button></div>
        </form>
        <p class="text-xs text-gray-500 mt-3">Tip: find existing plates via the search first.</p>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- User Management           -->
  <!-- ========================= -->
  <section id="users" class="mt-8 scroll-mt-24">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- List -->
      <div class="lg:col-span-3 card shadow-card p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="font-semibold text-lg">User Management</div>

          <!-- DB-backed search (includes all statuses) -->
          <form method="get" action="<?= url('admin/administration#users') ?>" class="flex items-center gap-2">
            <input type="text"
                   name="user_q"
                   value="<?= $e($userQ ?? '') ?>"
                   class="border rounded-lg px-3 py-2 w-56"
                   placeholder="Search name / email / ID">
            <button class="btn btn-sm">Search</button>
          </form>
        </div>

        <?php if (empty($users)): ?>
          <div class="empty-state">No users found.</div>
        <?php else: ?>
          <ul id="user-list" class="space-y-3">
            <?php foreach ($users as $u): ?>
              <?php
                // Normalize status from DB; fall back to is_locked if no explicit status column
                $raw = strtoupper(trim((string)($u['status'] ?? '')));
                if ($raw === '') { $raw = ((int)($u['is_locked'] ?? 0) === 1) ? 'LOCKED' : 'ACTIVE'; }

                // Consider any non-ACTIVE as "locked-like" for the button label
                $lockedLike = in_array($raw, ['LOCKED','BANNED','INACTIVE','DISABLED','SUSPENDED'], true);

                // Badge classes
                $badgeCls = 'bg-gray-100 text-gray-700 border';
                if ($raw === 'ACTIVE')   $badgeCls = 'bg-green-100 text-green-700 border border-green-200';
                if ($raw === 'LOCKED')   $badgeCls = 'bg-gray-100 text-gray-700 border';
                if ($raw === 'BANNED')   $badgeCls = 'bg-rose-100 text-rose-700 border border-rose-200';
                if ($raw === 'INACTIVE' || $raw === 'DISABLED' || $raw === 'SUSPENDED') {
                  $badgeCls = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                }
              ?>
              <li class="border rounded-xl px-4 py-3 flex items-center justify-between">
                <div class="truncate">
                  <div class="font-medium flex items-center gap-2">
                    <?= $e($u['name'] ?? '—') ?>
                    <span class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wide <?= $badgeCls ?>">
                      <?= $e($raw) ?>
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
                    <button class="px-3 py-1 rounded-full border text-xs">
                      <?= $lockedLike ? 'Unlock' : 'Lock' ?>
                    </button>
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
        <form method="post" action="<?= url('admin/users') ?>" class="space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-sm text-gray-600">Full Name</span>
              <input name="full_name" class="w-full border rounded-lg px-3 py-2" placeholder="Jane Doe">
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Email</span>
              <input name="email" type="email" class="w-full border rounded-lg px-3 py-2" placeholder="jane@email.com">
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Password</span>
              <input name="password" type="password" class="w-full border rounded-lg px-3 py-2" placeholder="••••••••">
            </label>
            <label class="block">
              <span class="text-sm text-gray-600">Role</span>
              <select name="role" class="w-full border rounded-lg px-3 py-2">
                <option value="CUSTOMER">Customer</option>
                <option value="STAFF">Staff</option>
                <option value="ADMIN">Admin</option>
              </select>
            </label>
          </div>
          <div class="pt-2"><button class="btn">Add User</button></div>
        </form>
      </div>
    </div>
  </section>

  <script>
    // mini toggler for the "View" & "Edit" panels
    document.addEventListener('click', function(e){
      const btn = e.target.closest('[data-toggle]');
      if(!btn) return;
      const id = btn.getAttribute('data-toggle');
      const el = document.getElementById(id);
      if(!el) return;
      el.classList.toggle('hidden');
    });
  </script>
<?php
}; // end $slot
?>

<?php
include view('admin/layout.php');
