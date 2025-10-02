<?php
/** Expected variables:
 *  - string $adminName
 *  - string $sectionTitle
 *  - callable $slot
 *  - callable|null $headerActions   // NEW: optional actions in the header right
 */
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
?>
<div class="max-w-7xl mx-auto px-4 py-10">

  <!-- Welcome banner -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-4xl md:text-4xl font-extrabold leading-tight">
        welcome, <?= $e($adminName ?? 'Admin') ?>.
      </h1>
      <p class="text-sm text-gray-500 mt-1">The bigger picture, always at your fingertips.</p>
    </div>

    <div class="flex items-center gap-3">
     <?php if (isset($headerActions) && is_callable($headerActions)) { $headerActions(); } ?>

      <a href="<?= url('admin/analytics') ?>" class="btn">View Analytics</a>
    </div>
  </div>

  <!-- Tabs -->
  <?php include view('admin/partials/tabs.php'); ?>

  <!-- Section headline -->
  <?php if (!empty($sectionTitle)): ?>
    <div class="mt-4 mb-6">
      <h2 class="text-3xl font-extrabold"><?= $e($sectionTitle) ?></h2>
      <?php if (strtolower($sectionTitle) === 'smooth op.'): ?>
        <p class="text-sm text-gray-500">Your workspace for smooth, efficient service.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Page content -->
  <div>
    <?php is_callable($slot) && $slot(); ?>
  </div>
</div>
