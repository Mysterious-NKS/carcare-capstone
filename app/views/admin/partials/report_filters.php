<?php
/** @var array $filters */
/** @var array $services */
/** @var array $staff */
/** @var array $statuses */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);
?>
<form method="get" action="<?= url('admin/reports') ?>" class="grid grid-cols-1 md:grid-cols-6 gap-4">
  <div>
    <label class="text-sm text-gray-600">From</label>
    <input type="date" name="date_from" value="<?= $e($filters['date_from'] ?? '') ?>"
           class="mt-1 w-full border rounded-lg px-3 py-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">To</label>
    <input type="date" name="date_to" value="<?= $e($filters['date_to'] ?? '') ?>"
           class="mt-1 w-full border rounded-lg px-3 py-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">Status</label>
    <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
      <option value="">— Any —</option>
      <?php foreach ($statuses as $st): ?>
        <option value="<?= $e($st) ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>>
          <?= $e($st) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-sm text-gray-600">Service</label>
    <select name="service_id" class="mt-1 w-full border rounded-lg px-3 py-2">
      <option value="0">— Any —</option>
      <?php foreach ($services as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= ((int)($filters['service_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
          <?= $e($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-sm text-gray-600">Staff</label>
    <select name="staff_id" class="mt-1 w-full border rounded-lg px-3 py-2">
      <option value="0">— Any —</option>
      <?php foreach ($staff as $st): ?>
        <option value="<?= (int)$st['id'] ?>" <?= ((int)($filters['staff_id'] ?? 0) === (int)$st['id']) ? 'selected' : '' ?>>
          <?= $e($st['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-sm text-gray-600">Plate</label>
    <input type="text" name="plate" value="<?= $e($filters['plate'] ?? '') ?>"
           placeholder="e.g. WXX 1234" class="mt-1 w-full border rounded-lg px-3 py-2">
  </div>

  <div class="md:col-span-4">
    <label class="text-sm text-gray-600">Sort</label>
    <select name="sort" class="mt-1 w-full border rounded-lg px-3 py-2">
      <option value="date_desc"  <?= (($filters['sort'] ?? '')==='date_desc')?'selected':'' ?>>Date (newest)</option>
      <option value="date_asc"   <?= (($filters['sort'] ?? '')==='date_asc')?'selected':'' ?>>Date (oldest)</option>
      <option value="price_desc" <?= (($filters['sort'] ?? '')==='price_desc')?'selected':'' ?>>Price (high→low)</option>
      <option value="price_asc"  <?= (($filters['sort'] ?? '')==='price_asc')?'selected':'' ?>>Price (low→high)</option>
      <option value="status"     <?= (($filters['sort'] ?? '')==='status')?'selected':'' ?>>Status</option>
    </select>
  </div>

  <div class="md:col-span-2 flex items-end gap-2">
    <button class="btn btn-primary"><?= function_exists('icon') ? icon('search','h-4 w-4') : '' ?><span>Apply</span></button>
    <a class="btn" href="<?= url('admin/reports') ?>">Reset</a>
  </div>
</form>
