<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'My Applications' ?> - Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .application-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.85em;
            padding: 6px 12px;
            border-radius: 15px;
        }
        .quick-actions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="/applications">
                    <i class="fas fa-list me-1"></i>My Applications
                </a>
                <a class="nav-link" href="/track">
                    <i class="fas fa-search me-1"></i>Track Application
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['email'] ?? 'User') ?>
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

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-file-alt me-2 text-primary"></i>
                            My Applications
                        </h1>
                        <p class="text-muted mb-0">Manage and track your birth certificate applications</p>
                    </div>
                    <a href="/applications/new" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Application
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2">
                        <i class="fas fa-rocket me-2"></i>Quick Actions
                    </h4>
                    <p class="mb-0">Get started with common tasks or find what you're looking for</p>
                </div>
                <div class="col-md-4">
                    <div class="d-grid gap-2">
                        <a href="/applications/new" class="btn btn-light">
                            <i class="fas fa-plus me-2"></i>New Application
                        </a>
                        <a href="/track" class="btn btn-outline-light">
                            <i class="fas fa-search me-2"></i>Track Application
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <?php if (!empty($applications)): ?>
            <?php
            $totalApps = count($applications);
            $pendingApps = count(array_filter($applications, fn($app) => $app['status'] === 'submitted' || $app['status'] === 'under_review'));
            $approvedApps = count(array_filter($applications, fn($app) => $app['status'] === 'approved' || $app['status'] === 'certificate_issued'));
            $rejectedApps = count(array_filter($applications, fn($app) => $app['status'] === 'rejected'));
            ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="h3 text-primary mb-1"><?= $totalApps ?></div>
                        <div class="text-muted">Total Applications</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="h3 text-warning mb-1"><?= $pendingApps ?></div>
                        <div class="text-muted">In Progress</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="h3 text-success mb-1"><?= $approvedApps ?></div>
                        <div class="text-muted">Approved</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="h3 text-danger mb-1"><?= $rejectedApps ?></div>
                        <div class="text-muted">Rejected</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Applications List -->
        <?php if (!empty($applications)): ?>
            <div class="row">
                <?php foreach ($applications as $application): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card application-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-baby me-2"></i>
                                    <?= htmlspecialchars($application['child_name']) ?>
                                </h6>
                                <?php
                                $statusColors = [
                                    'submitted' => 'bg-info text-white',
                                    'under_review' => 'bg-warning text-dark',
                                    'approved' => 'bg-success text-white',
                                    'rejected' => 'bg-danger text-white',
                                    'certificate_issued' => 'bg-success text-white',
                                    'pending' => 'bg-warning text-dark',
                                ];
                                $statusColor = $statusColors[$application['status']] ?? 'bg-secondary text-white';
                                $statusLabels = [
                                    'submitted' => 'In Progress',
                                    'under_review' => 'Under Review',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'certificate_issued' => 'Certificate Issued',
                                    'pending' => 'Pending',
                                ];
                                $statusText = $statusLabels[$application['status']] ?? ucwords(str_replace('_', ' ', $application['status']));
                                ?>
                                <span class="badge <?= $statusColor ?> status-badge">
                                    <?= $statusText ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Application Number</small>
                                        <div class="font-monospace small"><?= htmlspecialchars($application['application_number']) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Date of Birth</small>
                                        <div><?= date('M j, Y', strtotime($application['date_of_birth'])) ?></div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Submitted</small>
                                        <div><?= date('M j, Y', strtotime($application['created_at'])) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Gender</small>
                                        <div><?= ucfirst($application['gender']) ?></div>
                                    </div>
                                </div>
                                <?php if (!empty($application['tracking_number'])): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Tracking Number</small>
                                        <div class="font-monospace small text-primary">
                                            <?= htmlspecialchars($application['tracking_number']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Progress Indicator -->
                                <?php
                                $progressSteps = ['submitted', 'under_review', 'approved', 'certificate_issued'];
                                $currentStep = array_search($application['status'], $progressSteps);
                                if ($currentStep === false) $currentStep = 0;
                                $progressPercent = (($currentStep + 1) / count($progressSteps)) * 100;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Progress</small>
                                        <small class="text-muted"><?= round($progressPercent) ?>%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $progressPercent ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex gap-2">
                                    <a href="/applications/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                    <?php if (!empty($application['tracking_number'])): ?>
                                        <a href="/track?tracking_number=<?= urlencode($application['tracking_number']) ?>" 
                                           class="btn btn-outline-info btn-sm flex-fill">
                                            <i class="fas fa-search me-1"></i>Track
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($application['status'] === 'certificate_issued'): ?>
                                        <a href="/certificates/download/<?= $application['id'] ?>" 
                                           class="btn btn-success btn-sm flex-fill">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination (if needed) -->
            <?php if (count($applications) >= 10): ?>
                <nav aria-label="Applications pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h4>No Applications Yet</h4>
                        <p class="text-muted mb-4">
                            You haven't submitted any birth certificate applications yet. 
                            Get started by creating your first application.
                        </p>
                        <a href="/applications/new" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Your First Application
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Need Help?
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h6><i class="fas fa-book me-2"></i>Application Guide</h6>
                        <p class="text-muted small">Learn how to complete your application correctly</p>
                        <a href="/guide" class="btn btn-outline-primary btn-sm">View Guide</a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6><i class="fas fa-clock me-2"></i>Processing Times</h6>
                        <p class="text-muted small">Understand how long each step takes</p>
                        <a href="/processing-times" class="btn btn-outline-info btn-sm">Learn More</a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6><i class="fas fa-headset me-2"></i>Contact Support</h6>
                        <p class="text-muted small">Get help from our support team</p>
                        <a href="/contact" class="btn btn-outline-success btn-sm">Contact Us</a>
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
    <script>
        // Auto-refresh for applications in progress
        const hasInProgressApps = <?= json_encode(!empty(array_filter($applications ?? [], fn($app) => in_array($app['status'], ['submitted', 'under_review'])))) ?>;
        
        if (hasInProgressApps) {
            // Show refresh notification after 2 minutes
            setTimeout(function() {
                const refreshAlert = document.createElement('div');
                refreshAlert.className = 'alert alert-info alert-dismissible fade show';
                refreshAlert.innerHTML = `
                    <i class="fas fa-sync-alt me-2"></i>
                    <strong>Status Update Available:</strong> 
                    <a href="javascript:location.reload()" class="alert-link">Refresh page</a> 
                    to see the latest application status.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(refreshAlert, document.querySelector('.container').firstChild.nextSibling);
            }, 120000); // 2 minutes
        }

        // Copy tracking number to clipboard
        document.querySelectorAll('.font-monospace.text-primary').forEach(function(element) {
            element.style.cursor = 'pointer';
            element.title = 'Click to copy tracking number';
            element.addEventListener('click', function() {
                navigator.clipboard.writeText(this.textContent.trim()).then(function() {
                    // Show tooltip or notification
                    const originalText = element.textContent;
                    element.textContent = 'Copied!';
                    element.classList.add('text-success');
                    setTimeout(function() {
                        element.textContent = originalText;
                        element.classList.remove('text-success');
                        element.classList.add('text-primary');
                    }, 2000);
                });
            });
        });

        // Filter applications by status
        function filterApplications(status) {
            const cards = document.querySelectorAll('.application-card');
            cards.forEach(function(card) {
                const badge = card.querySelector('.status-badge');
                if (status === 'all' || badge.textContent.toLowerCase().includes(status.toLowerCase())) {
                    card.closest('.col-lg-6').style.display = 'block';
                } else {
                    card.closest('.col-lg-6').style.display = 'none';
                }
            });
        }

        // Add filter buttons if there are applications
        <?php if (!empty($applications)): ?>
            const filterContainer = document.createElement('div');
            filterContainer.className = 'mb-4';
            filterContainer.innerHTML = `
                <div class="btn-group" role="group" aria-label="Filter applications">
                    <button type="button" class="btn btn-outline-primary active" onclick="filterApplications('all')">All</button>
                    <button type="button" class="btn btn-outline-warning" onclick="filterApplications('submitted')">In Progress</button>
                    <button type="button" class="btn btn-outline-success" onclick="filterApplications('approved')">Approved</button>
                    <button type="button" class="btn btn-outline-danger" onclick="filterApplications('rejected')">Rejected</button>
                </div>
            `;
            
            const applicationsRow = document.querySelector('.row:has(.application-card)');
            if (applicationsRow) {
                applicationsRow.parentNode.insertBefore(filterContainer, applicationsRow);
            }
        <?php endif; ?>
    </script>
</body>
</html>
