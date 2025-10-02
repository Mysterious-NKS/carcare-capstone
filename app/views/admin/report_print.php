<?php
/** @var array $rows */
/** @var array $filters */
/** @var array $sum */
/** @var string $title */
$e = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= $e($title ?? 'Report') ?></title>
  <style>
    @page { margin: 16mm; }
    html, body { height:100%; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#111; margin:0; }
    .container { margin:24px; }
    h1 { margin:0 0 8px 0; font-size:22px; }
    .muted { color:#666; font-size:12px; }
    table { width:100%; border-collapse: collapse; margin-top:16px; }
    th, td { border:1px solid #ddd; padding:8px; font-size:13px; }
    th { background:#f6f7f9; text-align:left; }
    tfoot td { font-weight:bold; }
    .right { text-align:right; }
  </style>
</head>
<body>
  <div class="container">
    <h1><?= $e($title ?? 'Report') ?></h1>
    <div class="muted">
      Generated: <?= date('Y-m-d H:i') ?> •
      <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
        Range:
        <?= $e($filters['date_from'] ?: '—') ?> → <?= $e($filters['date_to'] ?: '—') ?>
      <?php else: ?>
        (no date filter)
      <?php endif; ?>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Status</th>
          <th>Service</th>
          <th class="right">Price</th>
          <th>Vehicle</th>
          <th>Plate</th>
          <th>Staff</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= date('Y-m-d H:i', strtotime($r['scheduled_at'])) ?></td>
            <td><?= $e($r['status']) ?></td>
            <td><?= $e($r['service_name']) ?></td>
            <td class="right"><?= number_format((float)$r['service_price'], 2) ?></td>
            <td><?= $e(trim(($r['year'] ?? '').' '.($r['make'] ?? '').' '.($r['model'] ?? ''))) ?></td>
            <td><?= $e($r['plate_no']) ?></td>
            <td><?= $e($r['staff_name'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4">Totals</td>
          <td class="right">RM <?= number_format((float)$sum['totalRevenue'], 2) ?></td>
          <td colspan="3">Appointments: <?= (int)$sum['totalAppointments'] ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <script>
    // You can uncomment this to auto-open the browser print dialog:
    // window.print();
  </script>
</body>
</html>
