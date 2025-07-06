<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Registrar Dashboard' ?> - Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .pending-card {
            border-left: 4px solid #ffc107;
        }
        .approved-card {
            border-left: 4px solid #28a745;
        }
        .rejected-card {
            border-left: 4px solid #dc3545;
        }
        .total-card {
            border-left: 4px solid #007bff;
        }
        .quick-action-btn {
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
        }
        .activity-timeline {
            max-height: 400px;
            overflow-y: auto;
        }
        .activity-item {
            border-left: 3px solid #e9ecef;
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #007bff;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .priority-high {
            border-left-color: #dc3545 !important;
        }
        .priority-medium {
            border-left-color: #ffc107 !important;
        }
        .priority-low {
            border-left-color: #28a745 !important;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/registrar/dashboard">
                <i class="fas fa-certificate me-2"></i>Registrar Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/registrar/dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/registrar/pending">
                            <i class="fas fa-clock me-1"></i>Pending Applications
                            <?php if (($statistics['pending_applications'] ?? 0) > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?= $statistics['pending_applications'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/registrar/reports">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/verify">
                            <i class="fas fa-search me-1"></i>Verify Certificate
                        </a>
                    </li>
                </ul>
                
                <!-- Notifications Dropdown -->
                <div class="navbar-nav">
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if (!empty($notifications)): ?>
                                <span class="notification-badge"><?= count($notifications) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                            <h6 class="dropdown-header">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </h6>
                            <?php if (!empty($notifications)): ?>
                                <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-0 small"><?= htmlspecialchars($notification['message']) ?></p>
                                                <small class="text-muted"><?= date('M j, g:i A', strtotime($notification['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="/notifications">View all notifications</a>
                            <?php else: ?>
                                <div class="dropdown-item text-muted">No new notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- User Profile Dropdown -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['email'] ?? 'Registrar') ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="dropdown-item" href="/settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/auth/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white border-0">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-sun me-2"></i>
                                    Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, 
                                    Registrar!
                                </h2>
                                <p class="mb-0">Welcome to your registrar dashboard. Here's an overview of today's activities.</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="h4 mb-0"><?= date('l, F j, Y') ?></div>
                                <small><?= date('g:i A') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card total-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Total Applications</h6>
                                <h3 class="mb-0"><?= number_format($statistics['total_applications'] ?? 0) ?></h3>
                                <small class="text-muted">All time</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card pending-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Pending Review</h6>
                                <h3 class="mb-0"><?= number_format($statistics['pending_applications'] ?? 0) ?></h3>
                                <small class="text-muted">Awaiting action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card approved-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Approved Today</h6>
                                <h3 class="mb-0"><?= number_format($statistics['approved_today'] ?? 0) ?></h3>
                                <small class="text-muted">This month: <?= number_format($statistics['my_approvals_month'] ?? 0) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card rejected-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Rejected Today</h6>
                                <h3 class="mb-0"><?= number_format($statistics['rejected_today'] ?? 0) ?></h3>
                                <small class="text-muted">Avg. time: <?= $statistics['avg_processing_time'] ?? 0 ?>h</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="/registrar/pending" class="btn btn-warning w-100 quick-action-btn">
                                    <i class="fas fa-clock me-2"></i>Review Pending Applications
                                    <?php if (($statistics['pending_applications'] ?? 0) > 0): ?>
                                        <span class="badge bg-dark ms-2"><?= $statistics['pending_applications'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/verify" class="btn btn-success w-100 quick-action-btn">
                                    <i class="fas fa-search me-2"></i>Verify Certificate
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/registrar/reports" class="btn btn-info w-100 quick-action-btn">
                                    <i class="fas fa-chart-bar me-2"></i>Generate Reports
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary w-100 quick-action-btn" onclick="showBatchProcessModal()">
                                    <i class="fas fa-layer-group me-2"></i>Batch Processing
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Applications -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Pending Applications
                        </h5>
                        <a href="/registrar/pending" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pendingApplications)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Child Name</th>
                                            <th>Applicant</th>
                                            <th>Submitted</th>
                                            <th>Days Pending</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingApplications as $app): ?>
                                            <?php
                                            $priority = 'low';
                                            if ($app['days_pending'] > 7) $priority = 'high';
                                            elseif ($app['days_pending'] > 3) $priority = 'medium';
                                            ?>
                                            <tr class="priority-<?= $priority ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($app['child_name']) ?></strong><br>
                                                    <small class="text-muted"><?= date('M j, Y', strtotime($app['date_of_birth'])) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($app['applicant_first_name'] . ' ' . $app['applicant_last_name']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($app['applicant_email']) ?></small>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($app['submitted_at'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'success') ?>">
                                                        <?= $app['days_pending'] ?> days
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning text-dark' : 'success') ?>">
                                                        <?= ucfirst($priority) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/registrar/review/<?= $app['id'] ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button class="btn btn-outline-success" onclick="quickApprove(<?= $app['id'] ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="quickReject(<?= $app['id'] ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>All Caught Up!</h5>
                                <p class="text-muted">No pending applications at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Activities -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?= ucwords(str_replace('_', ' ', $activity['action'])) ?></h6>
                                                <p class="mb-0 small text-muted"><?= htmlspecialchars($activity['description']) ?></p>
                                            </div>
                                            <small class="text-muted"><?= date('g:i A', strtotime($activity['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">No recent activities</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Performance Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Processing Speed</span>
                                <span class="small"><?= $statistics['avg_processing_time'] ?? 0 ?>h avg</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Approval Rate</span>
                                <span class="small">92%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 92%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Monthly Target</span>
                                <span class="small">78%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 78%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Quick Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h4 text-primary mb-0"><?= $statistics['my_approvals_month'] ?? 0 ?></div>
                                <small class="text-muted">My Approvals</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h4 text-info mb-0"><?= round(($statistics['avg_processing_time'] ?? 0), 1) ?>h</div>
                                <small class="text-muted">Avg. Time</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-success mb-0">98%</div>
                                <small class="text-muted">Accuracy</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-warning mb-0">5</div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Modals -->
    <!-- Batch Processing Modal -->
    <div class="modal fade" id="batchProcessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group me-2"></i>Batch Processing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select applications for batch processing:</p>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="batchAction" id="batchApprove" value="approve">
                        <label class="form-check-label" for="batchApprove">
                            <i class="fas fa-check text-success me-2"></i>Batch Approve
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="batchAction" id="batchReject" value="reject">
                        <label class="form-check-label" for="batchReject">
                            <i class="fas fa-times text-danger me-2"></i>Batch Reject
                        </label>
                    </div>
                    <div class="mt-3">
                        <label for="batchComments" class="form-label">Comments (optional)</label>
                        <textarea class="form-control" id="batchComments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="/registrar/pending" class="btn btn-primary">Go to Pending Applications</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Show batch processing modal
        function showBatchProcessModal() {
            new bootstrap.Modal(document.getElementById('batchProcessModal')).show();
        }

        // Quick approve function
        function quickApprove(applicationId) {
            if (confirm('Are you sure you want to approve this application?')) {
                processApplication(applicationId, 'approve', '');
            }
        }

        // Quick reject function
        function quickReject(applicationId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason !== null) {
                processApplication(applicationId, 'reject', reason);
            }
        }

        // Process application
        function processApplication(applicationId, action, comments) {
            const formData = new FormData();
            formData.append('application_id', applicationId);
            formData.append('action', action);
            formData.append('comments', comments);

            fetch('/registrar/process', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error', 'An error occurred while processing the application');
            });
        }

        // Show notification
        function showNotification(type, title, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                <strong>${title}:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }

        // Auto-refresh pending applications count
        setInterval(() => {
            fetch('/api/registrar/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.pending_applications !== undefined) {
                        const badges = document.querySelectorAll('.badge');
                        badges.forEach(badge => {
                            if (badge.textContent.match(/^\d+$/)) {
                                badge.textContent = data.pending_applications;
                            }
                        });
                    }
                })
                .catch(error => console.log('Stats update failed:', error));
        }, 30000); // Update every 30 seconds
    </script>
</body>
</html>
