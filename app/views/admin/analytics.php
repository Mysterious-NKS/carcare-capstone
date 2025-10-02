<?php
/**
 * @var array $filters
 * @var array $services
 * @var array $staff
 * @var array $statuses
 * @var array $kpis
 * @var array $series  // [ ['date'=>'YYYY-MM-DD','count'=>N,'revenue'=>F], ... ]
 */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

/** Header actions for this page (optional) */
$qs = http_build_query(array_filter($filters, fn($v)=>$v!=='' && $v!==0));
$reportsUrl = url('admin/reports'.($qs?('?'.$qs):''));
$headerActions = function() use ($reportsUrl){
?>
  <a class="btn" href="<?= $reportsUrl ?>"><?= function_exists('icon')?icon('table','h-4 w-4'):'' ?> Reports</a>
<?php
};

/** Main content slot */
$slot = function() use ($filters,$services,$staff,$statuses,$kpis,$series,$e){
?>
  <!-- Filter panel (inline, same fields as reports) -->
  <div class="card shadow-card p-4 mb-6">
    <form method="get" action="<?= url('admin/analytics') ?>" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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

      <div class="md:col-span-2 flex items-end gap-2">
        <button class="btn btn-primary"><?= function_exists('icon') ? icon('search','h-4 w-4') : '' ?><span>Apply</span></button>
        <a class="btn" href="<?= url('admin/analytics') ?>">Reset</a>
      </div>
    </form>
  </div>

  <!-- KPI tiles -->
  <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
    <div class="card shadow-card p-4 md:col-span-2">
      <div class="text-sm text-gray-500">Appointments</div>
      <div class="text-3xl font-extrabold mt-1"><?= (int)$kpis['appointments'] ?></div>
    </div>
    <div class="card shadow-card p-4 md:col-span-2">
      <div class="text-sm text-gray-500">Revenue</div>
      <div class="text-3xl font-extrabold mt-1">RM <?= number_format((float)$kpis['revenue'],2) ?></div>
    </div>
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Avg Price</div>
      <div class="text-3xl font-extrabold mt-1">RM <?= number_format((float)$kpis['avg_price'],2) ?></div>
    </div>
    <div class="card shadow-card p-4">
      <div class="text-sm text-gray-500">Completion Rate</div>
      <div class="text-3xl font-extrabold mt-1"><?= number_format((float)$kpis['completion_rate'],1) ?>%</div>
    </div>
  </div>

  <!-- DAILY TREND CHART -->
  <!-- DAILY TREND CHART -->
<div class="card shadow-card p-5 mb-6">
  <div class="font-semibold mb-3">Daily Trend (Chart)</div>

  <!-- Fixed-height wrapper so the canvas can't stretch forever -->
  <div style="height:360px; width:100%;">
    <canvas id="trendChart" style="width:100%; height:100%; display:block;"></canvas>
  </div>
</div>


  <!-- Daily trend table (count + revenue) -->
  <div class="card shadow-card p-5">
    <div class="font-semibold mb-3">Daily Trend (Table)</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2 pr-3">Date</th>
            <th class="py-2 pr-3">Appointments</th>
            <th class="py-2 pr-3 text-right">Revenue (RM)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($series as $row): ?>
            <tr class="border-t">
              <td class="py-2 pr-3"><?= $e($row['date']) ?></td>
              <td class="py-2 pr-3"><?= (int)$row['count'] ?></td>
              <td class="py-2 pr-3 text-right"><?= number_format((float)$row['revenue'],2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Chart.js (CDN) + init -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    (function () {
      const dataSeries = <?php echo json_encode($series, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

      const labels  = dataSeries.map(r => r.date);
      const counts  = dataSeries.map(r => Number(r.count || 0));
      const revenue = dataSeries.map(r => Number(r.revenue || 0));

      const canvas = document.getElementById('trendChart');
      if (!canvas) return;

      new Chart(canvas, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Appointments',
              data: counts,
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 2,
              yAxisID: 'yCount'
            },
            {
              label: 'Revenue (RM)',
              data: revenue,
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 2,
              yAxisID: 'yRev'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: true },
            tooltip: {
              callbacks: {
                label: (ctx) => {
                  const v = ctx.parsed.y ?? 0;
                  return ctx.dataset.yAxisID === 'yRev'
                    ? `Revenue: RM ${Number(v).toFixed(2)}`
                    : `Appointments: ${v}`;
                }
              }
            }
          },
          scales: {
            x: {
              ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
            },
            yCount: {
              type: 'linear',
              position: 'left',
              beginAtZero: true,
              title: { display: true, text: 'Appointments' }
            },
            yRev: {
              type: 'linear',
              position: 'right',
              beginAtZero: true,
              title: { display: true, text: 'Revenue (RM)' },
              grid: { drawOnChartArea: false }
            }
          }
        }
      });
    })();
  </script>
<?php
};

$sectionTitle = 'analytics';
include view('admin/layout.php');
