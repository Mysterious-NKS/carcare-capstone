


<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold">your garage</h1>
    <a href="<?= url('customer/vehicles/add') ?>" class="px-4 py-2 rounded-full bg-black text-white">Add Vehicle</a>
  </div>

  <?php if (empty($vehicles)): ?>
    <div class="border rounded-xl p-8 text-gray-600">No vehicles yet. Add your first car to start tracking maintenance.</div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($vehicles as $v): ?>
        <div class="border rounded-2xl shadow-card p-5">
          <div class="text-lg font-bold mb-1"><?= htmlspecialchars("{$v['year']} {$v['make']} {$v['model']}") ?></div>
          <div class="text-sm text-gray-600 mb-3">Plate: <?= htmlspecialchars($v['plate_no']) ?></div>

          <?php
            $dir = __DIR__ . '/../../../public/uploads/vehicles/' . $v['id'];
            $web = '/carcare/public/uploads/vehicles/' . $v['id'];
            $thumb = null;
            if (is_dir($dir)) {
              $imgs = array_values(array_filter(scandir($dir), fn($f)=>!in_array($f,['.','..'])));
              if (!empty($imgs)) $thumb = $web . '/' . $imgs[0];
            }
          ?>
          <?php if ($thumb): ?>
            <img src="<?= $thumb ?>" alt="" class="w-full h-40 object-cover rounded-xl mb-4">
          <?php endif; ?>

          <div class="flex gap-2">
            <a href="<?= url('customer/vehicles/edit') . '?id=' . (int)$v['id'] ?>" class="px-3 py-2 rounded-lg border">Edit</a>
            <form method="post" action="<?= url('customer/vehicles/delete') . '?id=' . (int)$v['id'] ?>" onsubmit="return confirm('Delete this vehicle?');">
              <button class="px-3 py-2 rounded-lg border text-rose-600">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
