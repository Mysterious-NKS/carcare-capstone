<?php
$path = $_SERVER['REQUEST_URI'] ?? '';
$isOps   = str_contains($path, '/admin') && !str_contains($path, '/administration');
$isAdmin = str_contains($path, '/admin/administration');
?>
<div class="staff-tabs mb-4">
  <a class="staff-tab <?= $isOps ? 'tab-active' : 'tab-idle' ?>" href="<?= url('admin') ?>">
    <?= function_exists('icon') ? icon('workflow','h-4 w-4') : '' ?>
    <span>Operations</span>
  </a>
  <a class="staff-tab <?= $isAdmin ? 'tab-active' : 'tab-idle' ?>" href="<?= url('admin/administration') ?>">
    <?= function_exists('icon') ? icon('settings','h-4 w-4') : '' ?>
    <span>Administration</span>
  </a>
</div>
