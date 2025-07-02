<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="/certificates">
                    <i class="fas fa-list me-1"></i>Certificates
                </a>
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
            </h2>
            <div>
                <a href="/dashboard" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?= $quickStats['applications_today'] ?? 0 ?></h4>
                                <small>Applications Today</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?= $quickStats['certificates_today'] ?? 0 ?></h4>
                                <small>Certificates Issued Today</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-certificate fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?= $quickStats['pending_applications'] ?? 0 ?></h4>
                                <small>Pending Applications</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?= $quickStats['avg_processing_days'] ?? 0 ?></h4>
                                <small>Avg Processing Days</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Generate Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="row g-3">
                            <div class="col-md-4">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value="">Select Report Type</option>
                                    <option value="applications_summary">Applications Summary</option>
                                    <option value="certificates_issued">Certificates Issued</option>
                                    <option value="processing_times">Processing Times</option>
                                    <option value="user_activity">User Activity</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?= htmlspecialchars($_GET['start_date'] ?? date('Y-m-01')) ?>" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?= htmlspecialchars($_GET['end_date'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="format" class="form-label">Format</label>
                                <select class="form-select" id="format" name="format">
                                    <option value="html">HTML</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report Results -->
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $reportType => $data): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-table me-2"></i>
                                    <?= ucwords(str_replace('_', ' ', $reportType)) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <?php foreach (array_keys($data[0]) as $header): ?>
                                                        <th><?= ucwords(str_replace('_', ' ', $header)) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $row): ?>
                                                    <tr>
                                                        <?php foreach ($row as $value): ?>
                                                            <td><?= htmlspecialchars($value) ?></td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                        <p>No data available for the selected criteria.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Monthly Trend Chart -->
                <?php if (!empty($quickStats['monthly_trend'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-area me-2"></i>Monthly Applications Trend
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/reports/export?type=applications" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download me-2"></i>Export Applications
                            </a>
                            <a href="/reports/export?type=certificates" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download me-2"></i>Export Certificates
                            </a>
                            <a href="/reports/export?type=users" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-download me-2"></i>Export Users
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>System Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Report Generated</small><br>
                            <strong><?= date('F j, Y g:i A') ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Data Range</small><br>
                            <strong><?= date('M j, Y', strtotime($_GET['start_date'] ?? date('Y-m-01'))) ?> - 
                                   <?= date('M j, Y', strtotime($_GET['end_date'] ?? date('Y-m-d'))) ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Generated By</small><br>
                            <strong><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly trend chart
        <?php if (!empty($quickStats['monthly_trend'])): ?>
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        const monthlyData = <?= json_encode($quickStats['monthly_trend']) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Applications',
                    data: monthlyData.map(item => item.applications),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Date validation
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date').value;
            const endDate = this.value;
            
            if (startDate && endDate && startDate > endDate) {
                alert('End date must be after start date');
                this.value = '';
            }
        });
    </script>
</body>
</html> 