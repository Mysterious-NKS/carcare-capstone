<?php
/** VEHICLES LIST + ADD PANEL (admin) */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);

/** guard: ensure expected vars exist */
$vehicles  = isset($vehicles)  && is_array($vehicles)  ? $vehicles  : [];
$vehPager  = isset($vehPager)  && is_array($vehPager)  ? $vehPager  : ['q'=>'','page'=>1,'pages'=>1,'per'=>10,'total'=>0,'has_prev'=>false,'has_next'=>false];
$qVal      = (string)($vehPager['q'] ?? '');
?>
<!-- Vehicle Management -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
  <!-- List -->
  <div class="lg:col-span-3 card shadow-card p-5">
    <div class="flex items-center justify-between mb-4">
      <div class="font-semibold text-lg">Vehicle Management</div>

      <!-- Server-side search (q) -->
      <form method="get" action="<?= url('admin/administration#vehicles') ?>" class="flex items-center gap-2">
        <input type="hidden" name="page" value="1">
        <input type="text" name="q" value="<?= $e($qVal) ?>"
               class="border rounded-lg px-3 py-2 w-56" placeholder="Search plate, make, model, ID">
        <button class="btn btn-sm">Search</button>
      </form>
    </div>

    <?php if (!is_array($vehicles) || count($vehicles) === 0): ?>
      <div class="text-sm text-gray-500">No vehicles found.</div>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($vehicles as $v):
          $vid   = (int)($v['id']        ?? 0);
          $year  = (string)($v['year']   ?? '');
          $make  = (string)($v['make']   ?? '');
          $model = (string)($v['model']  ?? '');
          $plate = (string)($v['plate_no'] ?? '');
          $owner = (string)($v['user_id']  ?? ($v['customer_id'] ?? ''));
          $title = trim("$year $make $model");
        ?>
          <li class="border rounded-xl px-4 py-3">
            <div class="flex items-center justify-between">
              <div class="truncate">
                <div class="font-medium">
                  <?= $e($title !== '' ? $title : '—') ?>
                  <?php if ($plate !== ''): ?>
                    <span class="text-xs text-gray-500 ml-2">• <?= $e($plate) ?></span>
                  <?php endif; ?>
                </div>
                <div class="text-xs text-gray-500">#<?= $vid ?></div>
              </div>

              <div class="flex items-center gap-2">
                <button type="button" class="btn !px-3 !py-1 text-xs" data-toggle data-target="#veh-view-<?= $vid ?>">View</button>
                <button type="button" class="btn !px-3 !py-1 text-xs" data-toggle data-target="#veh-edit-<?= $vid ?>">Edit</button>
                <form method="post" action="<?= url('admin/vehicles/'.$vid.'/delete') ?>" onsubmit="return confirm('Delete vehicle #<?= $vid ?>?');">
                  <button class="px-3 py-1 rounded-full border text-xs text-rose-700 hover:bg-rose-50">Delete</button>
                </form>
              </div>
            </div>

            <!-- View panel -->
            <div id="veh-view-<?= $vid ?>" class="hidden mt-3 text-sm text-gray-700">
              <div class="grid grid-cols-2 gap-3">
                <div><span class="text-gray-500">Year:</span> <?= $e($year !== '' ? $year : '—') ?></div>
                <div><span class="text-gray-500">Make:</span> <?= $e($make !== '' ? $make : '—') ?></div>
                <div><span class="text-gray-500">Model:</span> <?= $e($model !== '' ? $model : '—') ?></div>
                <div><span class="text-gray-500">Plate:</span> <?= $e($plate !== '' ? $plate : '—') ?></div>
                <div><span class="text-gray-500">Owner ID:</span> <?= $e($owner !== '' ? $owner : '—') ?></div>
              </div>
            </div>

            <!-- Edit panel -->
            <div id="veh-edit-<?= $vid ?>" class="hidden mt-3">
              <form method="post" action="<?= url('admin/vehicles/'.$vid.'/update') ?>" class="grid grid-cols-1 md:grid-cols-5 gap-2">
                <input name="year"     class="border rounded-lg px-2 py-1" placeholder="Year"           value="<?= $e($year)  ?>">
                <input name="make"     class="border rounded-lg px-2 py-1" placeholder="Make"           value="<?= $e($make)  ?>">
                <input name="model"    class="border rounded-lg px-2 py-1" placeholder="Model"          value="<?= $e($model) ?>">
                <input name="plate_no" class="border rounded-lg px-2 py-1" placeholder="Plate"          value="<?= $e($plate) ?>">
                <input name="user_id"  class="border rounded-lg px-2 py-1" placeholder="Owner/User ID"  value="<?= $e($owner) ?>">
                <div class="md:col-span-5">
                  <button class="btn btn-sm mt-2">Save</button>
                </div>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Pagination -->
      <?php
        $p    = $vehPager;
        $page = (int)($p['page']  ?? 1);
        $pages= (int)($p['pages'] ?? 1);
        $tot  = (int)($p['total'] ?? 0);
        $link = function($pg) use ($qVal) {
          $qs = http_build_query(['q'=>$qVal, 'page'=>$pg]);
          return url('admin/administration?'.$qs).'#vehicles';
        };
      ?>
      <div class="flex items-center justify-between mt-4 text-sm">
        <div class="text-gray-500">
          Page <?= $page ?> of <?= $pages ?> • <?= $tot ?> total
        </div>
        <div class="flex items-center gap-2">
          <?php if (!empty($p['has_prev'])): ?>
            <a class="btn btn-sm" href="<?= $link($page-1) ?>">Prev</a>
          <?php else: ?>
            <span class="btn btn-sm text-gray-400 cursor-not-allowed">Prev</span>
          <?php endif; ?>
          <?php if (!empty($p['has_next'])): ?>
            <a class="btn btn-sm" href="<?= $link($page+1) ?>">Next</a>
          <?php else: ?>
            <span class="btn btn-sm text-gray-400 cursor-not-allowed">Next</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Add/Edit -->
  <div class="lg:col-span-2 card shadow-card p-5">
    <div class="font-semibold mb-4">Add Vehicle</div>
    <?php include view('admin/partials/vehicle_form.php'); ?>
    <p class="text-xs text-gray-500 mt-3">
      Tip: Use the search box to find existing plates before adding a new vehicle.
    </p>
  </div>
</div>
