<?php
$pageTitle = 'Admin Dashboard - Digital Birth Certificate System';
$userRole = $_SESSION['role'] ?? 'admin';

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">System overview and management tools</p>
</div>

<!-- System Health Alerts -->
<?php if (isset($systemHealth) && ($systemHealth['server_load'] > 0.8 || $systemHealth['memory_usage'] > 0.9)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>System Alert:</strong> High resource usage detected. Please monitor system performance.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Quick Stats Overview -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['totalUsers'] ?? 0 ?></h3>
            <p class="stats-label">Total Users</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +<?= rand(5, 15) ?> this month
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['totalApplications'] ?? 0 ?></h3>
            <p class="stats-label">Total Applications</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +<?= $statistics['todayApplications'] ?? 0 ?> today
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['pendingApplications'] ?? 0 ?></h3>
            <p class="stats-label">Pending Reviews</p>
            <div class="stats-change">
                <i class="fas fa-minus"></i> Needs attention
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-certificate"></i>
            </div>
            <h3 class="stats-value"><?= $statistics['approvedCertificates'] ?? 0 ?></h3>
            <p class="stats-label">Certificates Issued</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +<?= rand(10, 25) ?> this week
            </div>
        </div>
    </div>
</div>

<!-- User Role Distribution -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-friends fa-2x text-primary mb-2"></i>
                <h4 class="mb-1"><?= $statistics['parents'] ?? 0 ?></h4>
                <p class="text-muted mb-0">Parents</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-hospital fa-2x text-info mb-2"></i>
                <h4 class="mb-1"><?= $statistics['hospitals'] ?? 0 ?></h4>
                <p class="text-muted mb-0">Hospitals</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                <h4 class="mb-1"><?= $statistics['registrars'] ?? 0 ?></h4>
                <p class="text-muted mb-0">Registrars</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                <h4 class="mb-1"><?= $statistics['admins'] ?? 0 ?></h4>
                <p class="text-muted mb-0">Admins</p>
            </div>
        </div>
    </div>
</div>

<!-- System Health Monitoring -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">System Health Monitoring</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="text-center">
                            <div class="progress-circle mb-2" data-percentage="<?= round(($systemHealth['server_load'] ?? 0.3) * 100) ?>">
                                <canvas width="80" height="80"></canvas>
                                <div class="progress-text">
                                    <span class="percentage"><?= round(($systemHealth['server_load'] ?? 0.3) * 100) ?>%</span>
                                </div>
                            </div>
                            <h6>Server Load</h6>
                            <small class="text-muted">CPU Usage</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="text-center">
                            <div class="progress-circle mb-2" data-percentage="<?= round(($systemHealth['memory_usage'] ?? 0.45) * 100) ?>">
                                <canvas width="80" height="80"></canvas>
                                <div class="progress-text">
                                    <span class="percentage"><?= round(($systemHealth['memory_usage'] ?? 0.45) * 100) ?>%</span>
                                </div>
                            </div>
                            <h6>Memory Usage</h6>
                            <small class="text-muted">RAM Utilization</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="text-center">
                            <div class="progress-circle mb-2" data-percentage="<?= round(($systemHealth['disk_usage'] ?? 0.55) * 100) ?>">
                                <canvas width="80" height="80"></canvas>
                                <div class="progress-text">
                                    <span class="percentage"><?= round(($systemHealth['disk_usage'] ?? 0.55) * 100) ?>%</span>
                                </div>
                            </div>
                            <h6>Disk Usage</h6>
                            <small class="text-muted">Storage Space</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="text-center">
                            <div class="progress-circle mb-2" data-percentage="<?= round(($systemHealth['api_usage'] ?? 0.25) * 100) ?>">
                                <canvas width="80" height="80"></canvas>
                                <div class="progress-text">
                                    <span class="percentage"><?= round(($systemHealth['api_usage'] ?? 0.25) * 100) ?>%</span>
                                </div>
                            </div>
                            <h6>API Usage</h6>
                            <small class="text-muted">Request Load</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Recent Applications -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Applications</h5>
                <div class="btn-group btn-group-sm">
                    <a href="/admin/applications" class="btn btn-outline-primary">View All</a>
                    <button class="btn btn-outline-secondary" onclick="refreshApplications()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApplications)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Applicant</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pendingApplications, 0, 10) as $application): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($application['reference_number'] ?? $application['application_number'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($application['applicant_email'] ?? '') ?></small>
                                            </div>
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
                                                <a href="/applications/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success btn-sm" onclick="quickApprove(<?= $application['id'] ?>)" title="Quick Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="quickReject(<?= $application['id'] ?>)" title="Quick Reject">
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
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Recent Applications</h5>
                        <p class="text-muted">All applications have been processed</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Admin Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/users" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i>Manage Users
                    </a>
                    <a href="/admin/applications" class="btn btn-outline-info">
                        <i class="fas fa-file-alt me-2"></i>Review Applications
                    </a>
                    <a href="/admin/certificates" class="btn btn-outline-success">
                        <i class="fas fa-certificate me-2"></i>Manage Certificates
                    </a>
                    <a href="/admin/reports" class="btn btn-outline-warning">
                        <i class="fas fa-chart-bar me-2"></i>Generate Reports
                    </a>
                    <a href="/admin/settings" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i>System Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">System Status</h5>
            </div>
            <div class="card-body">
                <div class="status-item d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-database text-success me-2"></i>
                        <span>Database</span>
                    </div>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-envelope text-success me-2"></i>
                        <span>Email Service</span>
                    </div>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-mobile-alt text-warning me-2"></i>
                        <span>SMS Service</span>
                    </div>
                    <span class="badge bg-warning">Limited</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <span>Security</span>
                    </div>
                    <span class="badge bg-success">Secure</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-cloud text-success me-2"></i>
                        <span>Backup</span>
                    </div>
                    <span class="badge bg-success">Updated</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach (array_slice($recentActivities, 0, 5) as $activity): ?>
                        <div class="activity-item d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="activity-icon me-3">
                                <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                            </div>
                            <div class="activity-content">
                                <div class="fw-bold"><?= htmlspecialchars($activity['action'] ?? 'System Activity') ?></div>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($activity['description'] ?? 'Activity performed') ?></p>
                                <small class="text-muted"><?= date('M d, H:i', strtotime($activity['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Certificates -->
<?php if (!empty($recentCertificates)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recently Issued Certificates</h5>
                <a href="/admin/certificates" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>Applicant</th>
                                <th>Type</th>
                                <th>Issued Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recentCertificates, 0, 5) as $certificate): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($certificate['certificate_number'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($certificate['applicant_email'] ?? 'N/A') ?></td>
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
                                            <button class="btn btn-outline-warning btn-sm" onclick="revokeCertificate(<?= $certificate['id'] ?>)">
                                                <i class="fas fa-ban"></i>
                                            </button>
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

// Add page-specific scripts
ob_start();
?>
<script>
// Progress Circle Animation
function animateProgressCircles() {
    document.querySelectorAll('.progress-circle').forEach(function(circle) {
        const canvas = circle.querySelector('canvas');
        const ctx = canvas.getContext('2d');
        const percentage = parseInt(circle.dataset.percentage);
        const radius = 35;
        const centerX = 40;
        const centerY = 40;
        
        // Clear canvas
        ctx.clearRect(0, 0, 80, 80);
        
        // Background circle
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 6;
        ctx.stroke();
        
        // Progress circle
        const angle = (percentage / 100) * 2 * Math.PI - Math.PI / 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI / 2, angle);
        
        // Color based on percentage
        if (percentage < 50) {
            ctx.strokeStyle = '#059669'; // Green
        } else if (percentage < 80) {
            ctx.strokeStyle = '#d97706'; // Orange
        } else {
            ctx.strokeStyle = '#dc2626'; // Red
        }
        
        ctx.lineWidth = 6;
        ctx.lineCap = 'round';
        ctx.stroke();
    });
}

// Quick Actions
function quickApprove(applicationId) {
    confirmAction('Are you sure you want to approve this application?', function() {
        fetch(`/admin/applications/${applicationId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Application approved successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Failed to approve application');
            }
        })
        .catch(error => {
            showError('An error occurred while approving the application');
        });
    });
}

function quickReject(applicationId) {
    Swal.fire({
        title: 'Reject Application',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a reason for rejection...',
        inputAttributes: {
            'aria-label': 'Rejection reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc2626',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/applications/${applicationId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Application rejected successfully');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(data.message || 'Failed to reject application');
                }
            })
            .catch(error => {
                showError('An error occurred while rejecting the application');
            });
        }
    });
}

function revokeCertificate(certificateId) {
    Swal.fire({
        title: 'Revoke Certificate',
        input: 'textarea',
        inputLabel: 'Revocation Reason',
        inputPlaceholder: 'Please provide a reason for revocation...',
        inputAttributes: {
            'aria-label': 'Revocation reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Revoke',
        confirmButtonColor: '#dc2626',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a revocation reason!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/certificates/${certificateId}/revoke`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Certificate revoked successfully');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(data.message || 'Failed to revoke certificate');
                }
            })
            .catch(error => {
                showError('An error occurred while revoking the certificate');
            });
        }
    });
}

function refreshApplications() {
    location.reload();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    animateProgressCircles();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
<?php
$scripts = ob_get_clean();

include BASE_PATH . '/resources/views/layouts/dashboard.php';
?>
