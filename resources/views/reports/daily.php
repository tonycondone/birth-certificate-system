<?php
// resources/views/reports/daily.php
$pageTitle = $pageTitle ?? 'Daily Report';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <style>
        .report-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .report-meta {
            color: #6c757d;
            font-size: 0.9em;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .blue-card {
            background-color: #cfe2ff;
            color: #084298;
        }
        .green-card {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .red-card {
            background-color: #f8d7da;
            color: #842029;
        }
        .yellow-card {
            background-color: #fff3cd;
            color: #664d03;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        .report-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Report Header -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1"><?= htmlspecialchars($pageTitle) ?></h1>
                    <p class="report-meta mb-0">
                        <i class="fa fa-calendar me-1"></i>
                        Generated on: <?= date('F j, Y, g:i a') ?>
                    </p>
                </div>
                <div>
                    <a href="/dashboard/reports" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to Reports
                    </a>
                    <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-outline-primary ms-2">
                        <i class="fa fa-refresh me-1"></i> Today's Report
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Date Picker -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-calendar-alt me-2"></i> Select Date
                    </h5>
                    <div>
                        <form class="d-flex" action="/dashboard/reports/daily" method="get">
                            <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($reportDate ?? date('Y-m-d')) ?>">
                            <button type="submit" class="btn btn-primary ms-2">
                                <i class="fa fa-search me-1"></i> View Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card blue-card">
                    <div class="stat-icon">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                    <div class="stat-value"><?= $reportData['applications'][0]['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card green-card">
                    <div class="stat-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?= $reportData['applications'][0]['approved'] ?? 0 ?></div>
                    <div class="stat-label">Approved Applications</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card red-card">
                    <div class="stat-icon">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?= $reportData['applications'][0]['rejected'] ?? 0 ?></div>
                    <div class="stat-label">Rejected Applications</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card yellow-card">
                    <div class="stat-icon">
                        <i class="fa fa-certificate"></i>
                    </div>
                    <div class="stat-value"><?= $reportData['certificates'][0]['total'] ?? 0 ?></div>
                    <div class="stat-label">Certificates Issued</div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="row">
            <!-- Applications Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Applications Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="applicationsChart"></canvas>
                        </div>
                        <table class="table table-striped table-bordered report-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalApps = $reportData['applications'][0]['total'] ?? 0;
                                $statuses = [
                                    'Approved' => $reportData['applications'][0]['approved'] ?? 0,
                                    'Rejected' => $reportData['applications'][0]['rejected'] ?? 0,
                                    'Pending' => $reportData['applications'][0]['pending'] ?? 0
                                ];
                                
                                foreach ($statuses as $status => $count):
                                    $percentage = $totalApps > 0 ? round(($count / $totalApps) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?= $status ?></td>
                                    <td><?= $count ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Certificates Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Certificates Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="certificatesChart"></canvas>
                        </div>
                        <table class="table table-striped table-bordered report-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalCerts = $reportData['certificates'][0]['total'] ?? 0;
                                $certStatuses = [
                                    'Active' => $reportData['certificates'][0]['active'] ?? 0,
                                    'Revoked' => $reportData['certificates'][0]['revoked'] ?? 0,
                                    'Expired' => $reportData['certificates'][0]['expired'] ?? 0
                                ];
                                
                                foreach ($certStatuses as $status => $count):
                                    $percentage = $totalCerts > 0 ? round(($count / $totalCerts) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?= $status ?></td>
                                    <td><?= $count ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (($_SESSION['role'] ?? '') === 'admin' && !empty($reportData['performance'])): ?>
        <!-- Performance Report (Admin Only) -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Registrar Performance</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered report-table">
                    <thead>
                        <tr>
                            <th>Registrar Name</th>
                            <th>Total Processed</th>
                            <th>Approved</th>
                            <th>Rejected</th>
                            <th>Avg Processing Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['performance'] as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= $user['total_reviewed'] ?></td>
                            <td><?= $user['approved'] ?></td>
                            <td><?= $user['rejected'] ?></td>
                            <td><?= round($user['avg_processing_hours'], 1) ?> hours</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Export Options -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Export Report</h5>
                <div class="d-flex gap-2">
                    <a href="/reports/export?type=daily&format=pdf&date=<?= urlencode($reportDate ?? date('Y-m-d')) ?>" class="btn btn-outline-danger">
                        <i class="fa fa-file-pdf-o me-1"></i> Export as PDF
                    </a>
                    <a href="/reports/export?type=daily&format=csv&date=<?= urlencode($reportDate ?? date('Y-m-d')) ?>" class="btn btn-outline-success">
                        <i class="fa fa-file-excel-o me-1"></i> Export as CSV
                    </a>
                    <a href="/reports/export?type=daily&format=print&date=<?= urlencode($reportDate ?? date('Y-m-d')) ?>" class="btn btn-outline-dark" target="_blank">
                        <i class="fa fa-print me-1"></i> Print Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Applications Chart
            const appCtx = document.getElementById('applicationsChart').getContext('2d');
            const appChart = new Chart(appCtx, {
                type: 'pie',
                data: {
                    labels: ['Approved', 'Rejected', 'Pending'],
                    datasets: [{
                        data: [
                            <?= $reportData['applications'][0]['approved'] ?? 0 ?>,
                            <?= $reportData['applications'][0]['rejected'] ?? 0 ?>,
                            <?= $reportData['applications'][0]['pending'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#198754',
                            '#dc3545',
                            '#ffc107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Certificates Chart
            const certCtx = document.getElementById('certificatesChart').getContext('2d');
            const certChart = new Chart(certCtx, {
                type: 'pie',
                data: {
                    labels: ['Active', 'Revoked', 'Expired'],
                    datasets: [{
                        data: [
                            <?= $reportData['certificates'][0]['active'] ?? 0 ?>,
                            <?= $reportData['certificates'][0]['revoked'] ?? 0 ?>,
                            <?= $reportData['certificates'][0]['expired'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#0d6efd',
                            '#dc3545',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 