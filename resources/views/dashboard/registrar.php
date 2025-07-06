<?php
$pageTitle = 'Registrar Dashboard - Digital Birth Certificate System';
$userRole = $_SESSION['role'] ?? 'registrar';

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Registrar Dashboard</h1>
    <p class="page-subtitle">Review and process birth certificate applications</p>
</div>

<!-- Priority Alerts -->
<?php if (($pendingReviews ?? 0) > 10): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>High Workload Alert:</strong> You have <?= $pendingReviews ?> applications pending review. Consider prioritizing urgent cases.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-value"><?= $pendingReviews ?? 0 ?></h3>
            <p class="stats-label">Pending Reviews</p>
            <div class="stats-change">
                <i class="fas fa-exclamation-circle"></i> Requires attention
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="stats-value"><?= $approvedToday ?? 0 ?></h3>
            <p class="stats-label">Approved Today</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> Great progress!
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <h3 class="stats-value"><?= $rejectedToday ?? 0 ?></h3>
            <p class="stats-label">Rejected Today</p>
            <div class="stats-change">
                <i class="fas fa-minus"></i> Quality control
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="stats-value"><?= $totalProcessed ?? 0 ?></h3>
            <p class="stats-label">Total Processed</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> Career total
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Daily Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyPerformanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Processing Time Analysis</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-success">2.3h</h4>
                        <small class="text-muted">Average Time</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">1.8h</h4>
                        <small class="text-muted">Fastest Time</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-warning">4.2h</h4>
                        <small class="text-muted">Slowest Time</small>
                    </div>
                </div>
                <hr>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" style="width: 65%"></div>
                    <div class="progress-bar bg-warning" style="width: 25%"></div>
                    <div class="progress-bar bg-danger" style="width: 10%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Fast (65%)</span>
                    <span>Average (25%)</span>
                    <span>Slow (10%)</span>
                </div>
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
                        <a href="/dashboard/pending" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <span>Review Pending</span>
                            <?php if (($pendingCount ?? 0) > 0): ?>
                                <span class="badge bg-light text-dark mt-1"><?= $pendingCount ?> waiting</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/registrar/batch-process" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-layer-group fa-2x mb-2"></i>
                            <span>Batch Process</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/dashboard/approved" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <span>View Approved</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/dashboard/reports" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <span>Generate Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Pending Applications -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Applications Requiring Review</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" onclick="batchApprove()" title="Batch Approve">
                        <i class="fas fa-check-double"></i>
                    </button>
                    <button class="btn btn-outline-primary" onclick="refreshApplications()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="/dashboard/pending" class="btn btn-outline-secondary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApplications)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Application #</th>
                                    <th>Child Details</th>
                                    <th>Hospital</th>
                                    <th>Priority</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pendingApplications, 0, 10) as $application): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="application-checkbox" value="<?= $application['id'] ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($application['reference_number'] ?? $application['application_number'] ?? 'N/A') ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($application['applicant_email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars(($application['child_name'] ?? ($application['child_first_name'] ?? 'N/A') . ' ' . ($application['child_last_name'] ?? ''))) ?></div>
                                                <small class="text-muted">
                                                    DOB: <?= date('M d, Y', strtotime($application['date_of_birth'] ?? 'now')) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($application['hospital_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $priority = $application['priority'] ?? 'normal';
                                            $priorityClass = $priority === 'urgent' ? 'warning' : ($priority === 'emergency' ? 'danger' : 'secondary');
                                            ?>
                                            <span class="badge bg-<?= $priorityClass ?>"><?= ucfirst($priority) ?></span>
                                        </td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($application['created_at'] ?? 'now')) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($application['created_at'] ?? 'now')) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/registrar/review/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm" title="Review">
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
                    
                    <!-- Batch Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span id="selectedCount">0</span> applications selected
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="batchApprove()" disabled id="batchApproveBtn">
                                <i class="fas fa-check me-1"></i>Batch Approve
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="batchReject()" disabled id="batchRejectBtn">
                                <i class="fas fa-times me-1"></i>Batch Reject
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">All Caught Up!</h5>
                        <p class="text-muted">No applications pending review at the moment</p>
                        <a href="/dashboard/approved" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>View Recent Approvals
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Registrar Sidebar -->
    <div class="col-lg-4">
        <!-- Today's Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Today's Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-1"><?= $approvedToday ?? 0 ?></h4>
                        <small class="text-muted">Approved</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-danger mb-1"><?= $rejectedToday ?? 0 ?></h4>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <?php
                    $total = ($approvedToday ?? 0) + ($rejectedToday ?? 0);
                    $approvedPercent = $total > 0 ? (($approvedToday ?? 0) / $total) * 100 : 0;
                    $rejectedPercent = $total > 0 ? (($rejectedToday ?? 0) / $total) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" style="width: <?= $approvedPercent ?>%"></div>
                    <div class="progress-bar bg-danger" style="width: <?= $rejectedPercent ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Approval Rate: <?= $total > 0 ? round($approvedPercent, 1) : 0 ?>%</span>
                    <span>Total: <?= $total ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Tools -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Quick Tools</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="openBulkProcessor()">
                        <i class="fas fa-layer-group me-2"></i>Bulk Processor
                    </button>
                    <button class="btn btn-outline-info" onclick="openSearchTool()">
                        <i class="fas fa-search me-2"></i>Advanced Search
                    </button>
                    <button class="btn btn-outline-success" onclick="generateDailyReport()">
                        <i class="fas fa-file-export me-2"></i>Daily Report
                    </button>
                    <button class="btn btn-outline-warning" onclick="openTemplateManager()">
                        <i class="fas fa-file-alt me-2"></i>Templates
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Approvals -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Recent Approvals</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($approvedCertificates)): ?>
                    <?php foreach (array_slice($approvedCertificates, 0, 5) as $certificate): ?>
                        <div class="approval-item d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="approval-icon me-3">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="approval-content">
                                <div class="fw-bold"><?= htmlspecialchars($certificate['child_name'] ?? 'N/A') ?></div>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($certificate['hospital_name'] ?? 'N/A') ?></p>
                                <small class="text-muted"><?= date('M d, H:i', strtotime($certificate['approved_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="/dashboard/approved" class="btn btn-sm btn-outline-primary">View All Approvals</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent approvals</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Add page-specific scripts
ob_start();
?>
<script>
// Chart.js configuration for daily performance
const ctx = document.getElementById('dailyPerformanceChart').getContext('2d');
const dailyPerformanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Approved',
            data: [12, 19, 15, 25, 22, 8, 5],
            borderColor: '#059669',
            backgroundColor: 'rgba(5, 150, 105, 0.1)',
            tension: 0.4
        }, {
            label: 'Rejected',
            data: [2, 3, 1, 4, 2, 1, 0],
            borderColor: '#dc2626',
            backgroundColor: 'rgba(220, 38, 38, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Selection management
let selectedApplications = [];

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.application-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    selectedApplications = Array.from(checkboxes).map(cb => cb.value);
    
    document.getElementById('selectedCount').textContent = selectedApplications.length;
    
    const batchApproveBtn = document.getElementById('batchApproveBtn');
    const batchRejectBtn = document.getElementById('batchRejectBtn');
    
    if (selectedApplications.length > 0) {
        batchApproveBtn.disabled = false;
        batchRejectBtn.disabled = false;
    } else {
        batchApproveBtn.disabled = true;
        batchRejectBtn.disabled = true;
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.application-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
});

// Quick Actions
function quickApprove(applicationId) {
    confirmAction('Are you sure you want to approve this application?', function() {
        const button = event.target.closest('button');
        const stopLoading = showLoading(button);
        
        fetch(`/registrar/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                application_id: applicationId,
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            stopLoading();
            if (data.success) {
                showSuccess('Application approved successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Failed to approve application');
            }
        })
        .catch(error => {
            stopLoading();
            showError('An error occurred while approving the application');
        });
    });
}

function quickReject(applicationId) {
    Swal.fire({
        title: 'Reject Application',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a detailed reason for rejection...',
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
            fetch(`/registrar/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    action: 'reject',
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

// Batch Actions
function batchApprove() {
    if (selectedApplications.length === 0) {
        showError('Please select applications to approve');
        return;
    }
    
    confirmAction(`Are you sure you want to approve ${selectedApplications.length} applications?`, function() {
        fetch(`/registrar/batch-process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                application_ids: selectedApplications,
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(`${selectedApplications.length} applications approved successfully`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Failed to approve applications');
            }
        })
        .catch(error => {
            showError('An error occurred while approving applications');
        });
    });
}

function batchReject() {
    if (selectedApplications.length === 0) {
        showError('Please select applications to reject');
        return;
    }
    
    Swal.fire({
        title: `Reject ${selectedApplications.length} Applications`,
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a reason for rejecting these applications...',
        inputAttributes: {
            'aria-label': 'Rejection reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject All',
        confirmButtonColor: '#dc2626',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/registrar/batch-process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    application_ids: selectedApplications,
                    action: 'reject',
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(`${selectedApplications.length} applications rejected successfully`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(data.message || 'Failed to reject applications');
                }
            })
            .catch(error => {
                showError('An error occurred while rejecting applications');
            });
        }
    });
}

// Quick Tools
function openBulkProcessor() {
    window.location.href = '/registrar/batch-process';
}

function openSearchTool() {
    Swal.fire({
        title: 'Advanced Search',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Application Number</label>
                    <input type="text" class="form-control" id="searchAppNumber" placeholder="Enter application number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Child Name</label>
                    <input type="text" class="form-control" id="searchChildName" placeholder="Enter child name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Hospital</label>
                    <select class="form-control" id="searchHospital">
                        <option value="">All Hospitals</option>
                        <option value="1">General Hospital</option>
                        <option value="2">City Medical Center</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control mb-2" id="searchStartDate">
                    <input type="date" class="form-control" id="searchEndDate">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Search',
        preConfirm: () => {
            const params = new URLSearchParams();
            const appNumber = document.getElementById('searchAppNumber').value;
            const childName = document.getElementById('searchChildName').value;
            const hospital = document.getElementById('searchHospital').value;
            const startDate = document.getElementById('searchStartDate').value;
            const endDate = document.getElementById('searchEndDate').value;
            
            if (appNumber) params.append('app_number', appNumber);
            if (childName) params.append('child_name', childName);
            if (hospital) params.append('hospital', hospital);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            window.location.href = `/dashboard/pending?${params.toString()}`;
        }
    });
}

function generateDailyReport() {
    const today = new Date().toISOString().split('T')[0];
    window.open(`/dashboard/reports?type=daily&date=${today}`, '_blank');
}

function openTemplateManager() {
    showError('Template manager is not yet implemented');
}

function refreshApplications() {
    location.reload();
}

// Auto-refresh every 2 minutes
setInterval(function() {
    if (selectedApplications.length === 0) {
        location.reload();
    }
}, 120000);
</script>
<?php
$scripts = ob_get_clean();

include BASE_PATH . '/resources/views/layouts/dashboard.php';
?>
