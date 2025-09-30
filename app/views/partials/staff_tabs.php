<?php
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');

/**
 * Return active class:
 *  - 'staff' tab is active only on /staff
 *  - other tabs active on /staff/{slug}
 */
$active = function(string $slug) use ($path){
  if ($slug === 'staff') {
    return ($path === 'staff') ? 'tab-active' : 'tab-idle';
  }
  return (str_contains($path, 'staff/'.$slug)) ? 'tab-active' : 'tab-idle';
};
?>
<div class="staff-tabs mb-6">
  <a href="<?= url('staff') ?>" class="staff-tab <?= $active('staff') ?>">
    <?= function_exists('icon') ? icon('tasks','h-4 w-4') : '' ?> <span>Dashboard</span>
  </a>
  <a href="<?= url('staff/interactions') ?>" class="staff-tab <?= $active('interactions') ?>">
    <?= function_exists('icon') ? icon('chat','h-4 w-4') : '' ?> <span>Customer Interaction</span>
  </a>
  <a href="<?= url('staff/workflow') ?>" class="staff-tab <?= $active('workflow') ?>">
    <?= function_exists('icon') ? icon('wrench','h-4 w-4') : '' ?> <span>Service Workflow Management</span>
  </a>
  <a href="<?= url('staff/schedule') ?>" class="staff-tab <?= $active('schedule') ?>">
    <?= function_exists('icon') ? icon('calendar','h-4 w-4') : '' ?> <span>Appointment Management</span>
  </a>
</div>
