<?php
/** @var array $filters */
/** @var array $kpis */
/** @var array $series */
/** @var string $title */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);

// Prep data for charts
$dates   = array_map(fn($r)=>$r['date'], $series);
$counts  = array_map(fn($r)=>(int)$r['count'], $series);
$revenue = array_map(fn($r)=>(float)$r['revenue'], $series);

$maxC = max(1, (int)max($counts ?: [1]));
$maxR = max(1.0, (float)max($revenue ?: [1]));

function linePath(array $values, float $w, float $h, float $max): string {
  if (empty($values)) return '';
  $n = count($values);
  if ($n === 1) { $x=0; $y=$h*(1-($values[0]/$max)); return "M 0 $y L $w $y"; }
  $dx = $w / max(1, $n-1);
  $d = [];
  foreach ($values as $i=>$v) {
    $x = $i*$dx;
    $y = $h * (1 - ($v / ($max ?: 1)));
    $d[] = ($i===0 ? "M " : "L ").round($x,1)." ".round($y,1);
  }
  return implode(' ', $d);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= $e($title ?? 'Analytics') ?></title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; margin:24px; }
    h1 { margin:0 0 4px 0; font-size:22px; }
    .muted { color:#666; font-size:12px; }
    .kpis { display:flex; gap:12px; margin:12px 0 16px; }
    .kpi { border:1px solid #ddd; border-radius:8px; padding:10px 12px; flex:1; }
    .kpi .lbl { color:#666; font-size:12px; }
    .kpi .val { font-size:20px; font-weight:700; margin-top:4px; }
    .charts { display:flex; gap:16px; }
    .card { border:1px solid #ddd; border-radius:8px; padding:10px; flex:1; }
    .title { font-size:13px; font-weight:600; margin-bottom:8px; }
    table { width:100%; border-collapse: collapse; margin-top:14px; }
    th, td { border:1px solid #ddd; padding:6px; font-size:12px; }
    th { background:#f6f7f9; text-align:left; }
  </style>
</head>
<body>
  <h1><?= $e($title ?? 'Analytics') ?></h1>
  <div class="muted">
    Generated: <?= date('Y-m-d H:i') ?> •
    <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
      Range: <?= $e($filters['date_from'] ?: '—') ?> → <?= $e($filters['date_to'] ?: '—') ?>
    <?php else: ?>
      (no date filter)
    <?php endif; ?>
  </div>

  <!-- KPI tiles -->
  <div class="kpis">
    <div class="kpi"><div class="lbl">Appointments</div><div class="val"><?= (int)$kpis['appointments'] ?></div></div>
    <div class="kpi"><div class="lbl">Revenue</div><div class="val">RM <?= number_format((float)$kpis['revenue'],2) ?></div></div>
    <div class="kpi"><div class="lbl">Avg Price</div><div class="val">RM <?= number_format((float)$kpis['avg_price'],2) ?></div></div>
    <div class="kpi"><div class="lbl">Completion Rate</div><div class="val"><?= number_format((float)$kpis['completion_rate'],1) ?>%</div></div>
  </div>

  <!-- Charts (inline SVG) -->
  <div class="charts">
    <div class="card">
      <div class="title">Appointments Trend</div>
      <svg width="520" height="160" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="520" height="160" fill="#fff" stroke="#e5e7eb"/>
        <?php $path = linePath($counts, 500, 120, $maxC); ?>
        <g transform="translate(10,20)">
          <polyline fill="none" stroke="#000" stroke-width="1" points="0,120 500,120"/>
          <path d="<?= $path ?>" stroke="#2563eb" fill="none" stroke-width="2"/>
        </g>
      </svg>
    </div>
    <div class="card">
      <div class="title">Revenue Trend</div>
      <svg width="520" height="160" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="520" height="160" fill="#fff" stroke="#e5e7eb"/>
        <?php $pathR = linePath($revenue, 500, 120, $maxR); ?>
        <g transform="translate(10,20)">
          <polyline fill="none" stroke="#000" stroke-width="1" points="0,120 500,120"/>
          <path d="<?= $pathR ?>" stroke="#10b981" fill="none" stroke-width="2"/>
        </g>
      </svg>
    </div>
  </div>

  <!-- Daily table -->
  <table>
    <thead><tr><th>Date</th><th>Appointments</th><th style="text-align:right;">Revenue (RM)</th></tr></thead>
    <tbody>
      <?php foreach ($series as $row): ?>
        <tr>
          <td><?= $e($row['date']) ?></td>
          <td><?= (int)$row['count'] ?></td>
          <td style="text-align:right;"><?= number_format((float)$row['revenue'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
