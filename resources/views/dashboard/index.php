<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Citizen Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .notification-item {
            transition: background-color 0.3s ease;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .activity-timeline {
            position: relative;
        }
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .activity-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 1rem;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #007bff;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #007bff;
        }
        .quick-action-btn {
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            
            <div class="navbar-nav ms-auto">
                <!-- Notifications Dropdown -->
                <div class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notifications ?? []) > 0): ?>
                            <span class="badge bg-danger"><?= count($notifications) ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-0 small"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                            <small class="text-muted"><?= date('M j, g:i A', strtotime($notification['created_at'] ?? '')) ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dropdown-item text-muted">No new notifications</div>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">View all notifications</a>
                    </div>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($user['email'] ?? 'User') ?>
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
    </nav>

    <div class="container-fluid mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-sun me-2"></i>
                                    Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, 
                                    <?= htmlspecialchars(ucfirst($user['first_name'] ?? 'User')) ?>!
                                </h2>
                                <p class="mb-0">Welcome to your citizen dashboard. Here's what's happening with your applications.</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="h4 mb-0"><?= date('l, F j, Y') ?></div>
                                <small>Last login: <?= date('g:i A', strtotime($user['last_login_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-file-alt fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Total Applications</h6>
                                <h3 class="mb-0"><?= number_format($statistics['totalApplications'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Approved Applications</h6>
                                <h3 class="mb-0"><?= number_format($statistics['approvedApplications'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Pending Applications</h6>
                                <h3 class="mb-0"><?= number_format($statistics['pendingApplications'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Rejected Applications</h6>
                                <h3 class="mb-0"><?= number_format($statistics['rejectedApplications'] ?? 0) ?></h3>
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
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="/certificate/apply" class="btn btn-primary w-100 quick-action-btn">
                                    <i class="fas fa-plus me-2"></i>New Application
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/verify" class="btn btn-success w-100 quick-action-btn">
                                    <i class="fas fa-search me-2"></i>Verify Certificate
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/certificates" class="btn btn-info w-100 quick-action-btn">
                                    <i class="fas fa-list me-2"></i>View All Certificates
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/profile" class="btn btn-secondary w-100 quick-action-btn">
                                    <i class="fas fa-user me-2"></i>Update Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Applications -->
                    <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>My Applications
                            </h5>
                        <a href="/applications" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                        <?php if (!empty($applications)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Child Name</th>
                                            <th>Date of Birth</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($applications as $app): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($app['child_first_name'] . ' ' . $app['child_last_name']) ?>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($app['date_of_birth'])) ?></td>
                                                <td>
                                                    <?php if ($app['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif ($app['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Approved</span>
                                                    <?php elseif ($app['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= ucfirst($app['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                                                <td>
                                                    <a href="/applications/<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                You haven't submitted any applications yet. 
                                <a href="/certificate/apply" class="alert-link">Apply for a birth certificate now</a>.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- My Certificates -->
                    <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                            <i class="fas fa-certificate me-2"></i>My Certificates
                            </h5>
                        <a href="/certificates" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                        <?php if (!empty($certificates)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                            <th>Certificate #</th>
                                                <th>Child Name</th>
                                            <th>Issued Date</th>
                                                <th>Status</th>
                                            <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($certificates as $cert): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cert['certificate_number']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($cert['child_first_name'] . ' ' . $cert['child_last_name']) ?>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($cert['issued_at'])) ?></td>
                                                <td>
                                                    <?php if ($cert['status'] == 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php elseif ($cert['status'] == 'revoked'): ?>
                                                        <span class="badge bg-danger">Revoked</span>
                                                    <?php elseif ($cert['status'] == 'expired'): ?>
                                                        <span class="badge bg-warning">Expired</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= ucfirst($cert['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/certificate/download?id=<?= $cert['id'] ?>" class="btn btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <a href="/certificates/<?= $cert['id'] ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                You don't have any certificates yet. Certificates will appear here once your application is approved.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Activities -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <p class="mb-1 fw-bold"><?= htmlspecialchars($activity['action']) ?></p>
                                        <p class="mb-0 small text-muted"><?= htmlspecialchars($activity['description']) ?></p>
                                        <small class="text-muted"><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent activities to display.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>Help & Support
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Need Assistance?</h6>
                            <p class="small text-muted">Our support team is here to help you with any questions or issues.</p>
                            <a href="/contact" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Contact Support
                            </a>
                        </div>
                        <div class="mb-3">
                            <h6>FAQs</h6>
                            <p class="small text-muted">Find answers to commonly asked questions about birth certificates.</p>
                            <a href="/faq" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-question me-1"></i>View FAQs
                            </a>
                        </div>
                        <div>
                            <h6>User Guide</h6>
                            <p class="small text-muted">Learn how to use the system effectively with our comprehensive guide.</p>
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-book me-1"></i>View Guide
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 Birth Certificate System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="text-muted me-3">Privacy Policy</a>
                    <a href="/terms" class="text-muted me-3">Terms of Service</a>
                    <a href="/contact" class="text-muted">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
</body>
</html> 