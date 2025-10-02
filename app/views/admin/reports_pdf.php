<?php
/** @var array  $rows */
/** @var array  $filters */
/** @var array  $sum */
/** @var string $title */
/** @var string $___chartDataUri  Base64 PNG data URI for the chart (may be empty) */

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= $e($title ?? 'Report') ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; margin:24px; }
    h1 { margin:0 0 6px 0; font-size:22px; }
    .muted { color:#666; font-size:12px; margin-bottom: 10px; }
    .chart { margin:12px 0 16px; }
    .chart img { width:100%; max-width:1000px; display:block; margin:0 auto; border:1px solid #eee; }

    table { width:100%; border-collapse: collapse; margin-top:12px; }
    th, td { border:1px solid #ddd; padding:8px; font-size:12px; vertical-align: top; }
    th { background:#f6f7f9; text-align:left; }
    tfoot td { font-weight:bold; }
    .right { text-align:right; }
    .nowrap { white-space: nowrap; }
  </style>
</head>
<body>

  <h1><?= $e($title ?? 'Report') ?></h1>
  <div class="muted">
    Generated: <?= date('Y-m-d H:i') ?> •
    <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
      Range: <?= $e($filters['date_from'] ?: '—') ?> → <?= $e($filters['date_to'] ?: '—') ?>
    <?php else: ?>
      (no date filter)
    <?php endif; ?>
  </div>

  <?php if (!empty($___chartDataUri)): ?>
    <div class="chart">
      <img src="<?= $___chartDataUri ?>" alt="Daily Trend (Appointments & Revenue)">
    </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th class="nowrap">ID</th>
        <th class="nowrap">Date</th>
        <th>Status</th>
        <th>Service</th>
        <th class="right nowrap">Price</th>
        <th>Vehicle</th>
        <th class="nowrap">Plate</th>
        <th>Staff</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="nowrap"><?= (int)$r['id'] ?></td>
          <td class="nowrap"><?= $e(date('Y-m-d H:i', strtotime($r['scheduled_at']))) ?></td>
          <td><?= $e($r['status']) ?></td>
          <td><?= $e($r['service_name']) ?></td>
          <td class="right nowrap"><?= number_format((float)$r['service_price'], 2) ?></td>
          <td><?= $e(trim(($r['year'] ?? '').' '.($r['make'] ?? '').' '.($r['model'] ?? ''))) ?></td>
          <td class="nowrap"><?= $e($r['plate_no']) ?></td>
          <td><?= $e($r['staff_name'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4">Totals</td>
        <td class="right nowrap">RM <?= number_format((float)$sum['totalRevenue'], 2) ?></td>
        <td colspan="3">Appointments: <?= (int)$sum['totalAppointments'] ?></td>
      </tr>
    </tfoot>
  </table>

</body>
</html>
