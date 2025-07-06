<?php
$pageTitle = 'Dashboard - Digital Birth Certificate System';
$userRole = $_SESSION['role'] ?? 'parent';
$userName = ($_SESSION['first_name'] ?? 'User') . ' ' . ($_SESSION['last_name'] ?? '');

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>!</h1>
    <p class="page-subtitle">Manage your birth certificate applications and track their progress</p>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['totalApplications'] ?? 0 ?></h3>
            <p class="stats-label">Total Applications</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +2 this month
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['pendingApplications'] ?? 0 ?></h3>
            <p class="stats-label">Pending Applications</p>
            <div class="stats-change">
                <i class="fas fa-minus"></i> No change
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['approvedApplications'] ?? 0 ?></h3>
            <p class="stats-label">Approved Applications</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +1 this week
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-certificate"></i>
            </div>
            <h3 class="stats-value"><?= count($certificates ?? []) ?></h3>
            <p class="stats-label">Certificates Issued</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +1 this week
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/applications/new" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>New Application</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/track" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span>Track Application</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/certificates" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-download fa-2x mb-2"></i>
                            <span>Download Certificates</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/verify" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <span>Verify Certificate</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Applications and Certificates -->
<div class="row">
    <!-- Recent Applications -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Applications</h5>
                <a href="/applications" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($applications)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($applications, 0, 5) as $application): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($application['reference_number'] ?? $application['application_number'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($application['purpose'] ?? 'Birth Certificate') ?></td>
                                        <td>
                                            <span class="badge status-<?= $application['status'] ?? 'pending' ?>">
                                                <?= ucfirst($application['status'] ?? 'Pending') ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($application['created_at'] ?? 'now')) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/applications/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (($application['status'] ?? '') === 'pending'): ?>
                                                    <a href="/applications/<?= $application['id'] ?>/edit" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Applications Yet</h5>
                        <p class="text-muted">Start by creating your first birth certificate application</p>
                        <a href="/applications/new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Application
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Recent Notifications -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Recent Notifications</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($notifications)): ?>
                    <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                        <div class="notification-item d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="notification-icon me-3">
                                <?php
                                $iconClass = 'fas fa-info-circle text-info';
                                switch ($notification['type'] ?? 'info') {
                                    case 'success': $iconClass = 'fas fa-check-circle text-success'; break;
                                    case 'warning': $iconClass = 'fas fa-exclamation-triangle text-warning'; break;
                                    case 'error': $iconClass = 'fas fa-exclamation-circle text-danger'; break;
                                }
                                ?>
                                <i class="<?= $iconClass ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h6 class="mb-1"><?= htmlspecialchars($notification['title'] ?? 'Notification') ?></h6>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                <small class="text-muted"><?= date('M d, H:i', strtotime($notification['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="/notifications" class="btn btn-sm btn-outline-primary">View All Notifications</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-bell fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No new notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="/applications/new" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-plus-circle text-primary me-3"></i>
                        <div>
                            <div class="fw-bold">New Application</div>
                            <small class="text-muted">Start a new birth certificate application</small>
                        </div>
                    </a>
                    <a href="/track" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-search text-info me-3"></i>
                        <div>
                            <div class="fw-bold">Track Application</div>
                            <small class="text-muted">Check the status of your application</small>
                        </div>
                    </a>
                    <a href="/verify" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-shield-alt text-success me-3"></i>
                        <div>
                            <div class="fw-bold">Verify Certificate</div>
                            <small class="text-muted">Verify the authenticity of a certificate</small>
                        </div>
                    </a>
                    <a href="/profile" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-user text-secondary me-3"></i>
                        <div>
                            <div class="fw-bold">Update Profile</div>
                            <small class="text-muted">Manage your account information</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Help & Support</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="/faq" class="list-group-item list-group-item-action">
                        <i class="fas fa-question-circle me-2"></i>
                        Frequently Asked Questions
                    </a>
                    <a href="/contact" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope me-2"></i>
                        Contact Support
                    </a>
                    <a href="/api-docs" class="list-group-item list-group-item-action">
                        <i class="fas fa-book me-2"></i>
                        Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Certificates -->
<?php if (!empty($certificates)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Certificates</h5>
                <a href="/certificates" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>Type</th>
                                <th>Issued Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($certificates, 0, 5) as $certificate): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($certificate['certificate_number'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($certificate['application_purpose'] ?? 'Birth Certificate') ?></td>
                                    <td><?= date('M d, Y', strtotime($certificate['issued_at'] ?? 'now')) ?></td>
                                    <td>
                                        <span class="badge status-<?= $certificate['status'] ?? 'active' ?>">
                                            <?= ucfirst($certificate['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/certificates/<?= $certificate['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/certificates/download/<?= $certificate['id'] ?>" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="/verify/certificate/<?= $certificate['certificate_number'] ?>" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-shield-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include BASE_PATH . '/resources/views/layouts/dashboard.php';
?>
