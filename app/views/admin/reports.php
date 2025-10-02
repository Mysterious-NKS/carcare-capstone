<?php
// Build query string from current non-empty filters
$qs = http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 0));
$csvUrl    = url('admin/reports/export/csv' . ($qs ? ('?'.$qs) : ''));
$pdfUrl    = url('admin/reports/export/pdf' . ($qs ? ('?'.$qs) : ''));
$analytics = url('admin/analytics' . ($qs ? ('?'.$qs) : ''));

// Header actions (right side)
$headerActions = function() use ($csvUrl,$pdfUrl){ ?>
  <a class="btn" href="<?= $csvUrl ?>"><?= function_exists('icon')?icon('download','h-4 w-4'):'' ?> Export CSV</a>
  <a class="btn" href="<?= $pdfUrl ?>"><?= function_exists('icon')?icon('printer','h-4 w-4'):'' ?> Download PDF</a>
<?php };

$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

// Page-level slot
$slot = function() use ($filters,$services,$staff,$rows,$statuses,$totalAppointments,$totalRevenue,$byStatus,$e) {
?>
  <!-- Filter toggle -->
  <div class="mb-4">
    <button id="filterToggle" class="btn">
      <?= function_exists('icon') ? icon('filter','h-4 w-4') : '' ?>
      <span>Filter</span>
    </button>
  </div>

  <!-- Filter panel (collapsible) -->
  <div id="filterPanel" class="card shadow-card p-4 mb-6 hidden">
    <?php include view('admin/partials/report_filters.php'); ?>
  </div>

  <!-- Summary tiles -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Appointments</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$totalAppointments ?></div>
    </div>
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Revenue (est.)</div>
      <div class="text-3xl font-extrabold mt-1">RM <?= number_format((float)$totalRevenue, 2) ?></div>
    </div>
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Completed</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)($byStatus['COMPLETED'] ?? 0) ?></div>
    </div>
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Pending</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)($byStatus['PENDING'] ?? 0) ?></div>
    </div>
  </div>

  <!-- Table -->
  <div class="card shadow-card p-5">
    <?php include view('admin/partials/report_table.php'); ?>
  </div>

  <script>
    (function(){
      const btn = document.getElementById('filterToggle');
      const panel = document.getElementById('filterPanel');
      btn && btn.addEventListener('click', () => panel.classList.toggle('hidden'));
    })();
  </script>
<?php
};

$sectionTitle = $sectionTitle ?? 'reports';
include view('admin/layout.php');
