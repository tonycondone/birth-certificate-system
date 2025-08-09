<?php
$pageTitle = $pageTitle ?? 'Registrar Reports';
$reportType = $reportType ?? ($_GET['type'] ?? 'monthly');
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-t');
$reportData = $reportData ?? [];
$reportChartData = $reportChartData ?? json_encode(['labels'=>[],'datasets'=>[]]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <style>
    .page-header{background:#f8f9fa;border-radius:8px;padding:16px 20px;margin:16px 0}
    .stat-card{border-radius:8px;padding:16px}
    .chart-wrap{height:360px}
  </style>
</head>
<body class="bg-light">
<div class="container py-3">
  <div class="d-flex align-items-center justify-content-between page-header">
    <div>
      <h2 class="h4 mb-1">Registrar Reports</h2>
      <div class="text-muted">Analyze applications and performance</div>
    </div>
    <div>
      <a href="/registrar/dashboard" class="btn btn-outline-secondary"><i class="fa fa-tachometer-alt me-1"></i>Dashboard</a>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-3 align-items-end" method="get" action="/registrar/reports">
        <div class="col-sm-3">
          <label for="type" class="form-label">Report Type</label>
          <select id="type" name="type" class="form-select">
            <option value="daily" <?= $reportType==='daily'?'selected':'' ?>>Daily</option>
            <option value="weekly" <?= $reportType==='weekly'?'selected':'' ?>>Weekly</option>
            <option value="monthly" <?= $reportType==='monthly'?'selected':'' ?>>Monthly</option>
            <option value="performance" <?= $reportType==='performance'?'selected':'' ?>>Registrar Performance</option>
          </select>
        </div>
        <?php if ($reportType==='daily' || $reportType==='weekly'): ?>
          <div class="col-sm-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>">
          </div>
        <?php else: ?>
          <div class="col-sm-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
          </div>
          <div class="col-sm-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
          </div>
        <?php endif; ?>
        <div class="col-sm-3">
          <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter me-1"></i>Apply</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-3">
    <?php
      $tot = 0; $app = 0; $rej = 0; $pend = 0;
      if ($reportType==='performance') {
        // Aggregate performance
        foreach ($reportData as $r) { $tot += (int)($r['total_reviewed'] ?? 0); $app += (int)($r['approved'] ?? 0); $rej += (int)($r['rejected'] ?? 0); }
      } else {
        foreach ($reportData as $r) { $tot += (int)($r['total'] ?? 0); $app += (int)($r['approved'] ?? 0); $rej += (int)($r['rejected'] ?? 0); $pend += (int)($r['pending'] ?? 0); }
      }
    ?>
    <div class="col-md-3"><div class="stat-card bg-primary text-white"><div class="small">Total</div><div class="h4 mb-0"><?= $tot ?></div></div></div>
    <div class="col-md-3"><div class="stat-card bg-success text-white"><div class="small">Approved</div><div class="h4 mb-0"><?= $app ?></div></div></div>
    <div class="col-md-3"><div class="stat-card bg-danger text-white"><div class="small">Rejected</div><div class="h4 mb-0"><?= $rej ?></div></div></div>
    <?php if ($reportType!=='performance'): ?>
    <div class="col-md-3"><div class="stat-card bg-warning"><div class="small">Pending</div><div class="h4 mb-0"><?= $pend ?></div></div></div>
    <?php endif; ?>
  </div>

  <!-- Chart -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="chart-wrap">
        <canvas id="reportChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card mb-5">
    <div class="card-body table-responsive">
      <table class="table table-striped align-middle">
        <thead>
        <?php if ($reportType==='performance'): ?>
          <tr>
            <th>Registrar</th><th>Total Reviewed</th><th>Approved</th><th>Rejected</th><th>Avg Hours</th>
          </tr>
        <?php else: ?>
          <tr>
            <th>Date</th><th>Total</th><th>Approved</th><th>Rejected</th><th>Pending</th>
          </tr>
        <?php endif; ?>
        </thead>
        <tbody>
        <?php if (!empty($reportData)): ?>
          <?php foreach ($reportData as $row): ?>
            <?php if ($reportType==='performance'): ?>
              <tr>
                <td><?= htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) ?></td>
                <td><?= (int)($row['total_reviewed'] ?? 0) ?></td>
                <td><?= (int)($row['approved'] ?? 0) ?></td>
                <td><?= (int)($row['rejected'] ?? 0) ?></td>
                <td><?= number_format((float)($row['avg_processing_hours'] ?? 0), 1) ?></td>
              </tr>
            <?php else: ?>
              <tr>
                <td><?= htmlspecialchars($row['date'] ?? '') ?></td>
                <td><?= (int)($row['total'] ?? 0) ?></td>
                <td><?= (int)($row['approved'] ?? 0) ?></td>
                <td><?= (int)($row['rejected'] ?? 0) ?></td>
                <td><?= (int)($row['pending'] ?? 0) ?></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No data for the selected range</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const chartData = <?= $reportChartData ?>;
const ctx = document.getElementById('reportChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: chartData,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' } },
    scales: { x: { stacked: false }, y: { beginAtZero: true } }
  }
});
</script>
</body>
</html> 