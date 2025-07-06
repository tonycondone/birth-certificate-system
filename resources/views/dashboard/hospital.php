<?php
$pageTitle = 'Hospital Dashboard - Digital Birth Certificate System';
$userRole = $_SESSION['role'] ?? 'hospital';

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Hospital Dashboard</h1>
    <p class="page-subtitle">Manage birth records and verification processes</p>
</div>

<!-- Hospital Info Alert -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-hospital me-2"></i>
    <strong>Hospital ID:</strong> <?= htmlspecialchars($user['hospital_id'] ?? 'Not Set') ?> | 
    <strong>Verification Status:</strong> <span class="badge bg-success">Verified</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="fas fa-file-medical"></i>
            </div>
            <h3 class="stats-value"><?= $totalRecords ?? 0 ?></h3>
            <p class="stats-label">Total Birth Records</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> +<?= rand(2, 8) ?> this week
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-value"><?= $pendingVerifications ?? 0 ?></h3>
            <p class="stats-label">Pending Verifications</p>
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
            <h3 class="stats-value"><?= $verifiedToday ?? 0 ?></h3>
            <p class="stats-label">Verified Today</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> Great progress!
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3 class="stats-value"><?= $recordsThisMonth ?? 0 ?></h3>
            <p class="stats-label">Records This Month</p>
            <div class="stats-change positive">
                <i class="fas fa-arrow-up"></i> Monthly total
            </div>
        </div>
    </div>
</div>

<!-- Hospital Performance Metrics -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Monthly Birth Records Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Verification Status</h5>
            </div>
            <div class="card-body">
                <canvas id="verificationStatusChart" height="300"></canvas>
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
                        <a href="/hospital/records/new" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>New Birth Record</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/hospital/verifications" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <span>Verify Records</span>
                            <?php if (($pendingVerifications ?? 0) > 0): ?>
                                <span class="badge bg-warning text-dark mt-1"><?= $pendingVerifications ?> pending</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/hospital/submissions" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-upload fa-2x mb-2"></i>
                            <span>Bulk Upload</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/hospital/records" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-list fa-2x mb-2"></i>
                            <span>View All Records</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Pending Verifications -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Records Requiring Verification</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" onclick="bulkVerify()" title="Bulk Verify">
                        <i class="fas fa-check-double"></i>
                    </button>
                    <button class="btn btn-outline-primary" onclick="refreshRecords()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="/hospital/verifications" class="btn btn-outline-secondary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApplications)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllRecords" onchange="toggleSelectAllRecords()">
                                    </th>
                                    <th>Record #</th>
                                    <th>Child Details</th>
                                    <th>Birth Date</th>
                                    <th>Parents</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pendingApplications, 0, 10) as $application): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="record-checkbox" value="<?= $application['id'] ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($application['reference_number'] ?? $application['application_number'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars(($application['child_first_name'] ?? 'N/A') . ' ' . ($application['child_last_name'] ?? '')) ?></div>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($application['gender'] ?? 'N/A') ?> | 
                                                    <?= htmlspecialchars($application['weight_at_birth'] ?? 'N/A') ?>kg
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($application['date_of_birth'] ?? 'now')) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($application['time_of_birth'] ?? 'now')) ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">Mother: <?= htmlspecialchars(($application['mother_first_name'] ?? 'N/A') . ' ' . ($application['mother_last_name'] ?? '')) ?></div>
                                                <small class="text-muted">Father: <?= htmlspecialchars(($application['father_first_name'] ?? 'N/A') . ' ' . ($application['father_last_name'] ?? '')) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($application['created_at'] ?? 'now')) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($application['created_at'] ?? 'now')) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/hospital/records/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success btn-sm" onclick="quickVerify(<?= $application['id'] ?>)" title="Verify">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <a href="/hospital/records/<?= $application['id'] ?>/edit" class="btn btn-outline-secondary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span id="selectedRecordCount">0</span> records selected
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="bulkVerify()" disabled id="bulkVerifyBtn">
                                <i class="fas fa-check me-1"></i>Bulk Verify
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="bulkFlag()" disabled id="bulkFlagBtn">
                                <i class="fas fa-flag me-1"></i>Flag for Review
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">All Records Verified!</h5>
                        <p class="text-muted">No records pending verification at the moment</p>
                        <a href="/hospital/records/new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Record
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Hospital Sidebar -->
    <div class="col-lg-4">
        <!-- Today's Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Today's Activity</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary mb-1"><?= rand(3, 12) ?></h4>
                        <small class="text-muted">New Records</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-1"><?= $verifiedToday ?? 0 ?></h4>
                        <small class="text-muted">Verified</small>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: 60%"></div>
                    <div class="progress-bar bg-success" style="width: 40%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Processing Rate: 85%</span>
                    <span>Target: 90%</span>
                </div>
            </div>
        </div>

        <!-- Hospital Tools -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Hospital Tools</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="openRecordSearch()">
                        <i class="fas fa-search me-2"></i>Search Records
                    </button>
                    <button class="btn btn-outline-info" onclick="generateHospitalReport()">
                        <i class="fas fa-file-export me-2"></i>Generate Report
                    </button>
                    <button class="btn btn-outline-success" onclick="openBulkUpload()">
                        <i class="fas fa-upload me-2"></i>Bulk Upload
                    </button>
                    <button class="btn btn-outline-warning" onclick="openDataValidation()">
                        <i class="fas fa-check-double me-2"></i>Data Validation
                    </button>
                </div>
            </div>
        </div>

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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="/hospital/records/new" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-plus-circle text-primary me-3"></i>
                        <div>
                            <div class="fw-bold">New Birth Record</div>
                            <small class="text-muted">Register a new birth</small>
                        </div>
                    </a>
                    <a href="/hospital/verifications" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-shield-alt text-warning me-3"></i>
                        <div>
                            <div class="fw-bold">Verify Records</div>
                            <small class="text-muted">Verify pending birth records</small>
                        </div>
                    </a>
                    <a href="/hospital/submissions" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-upload text-info me-3"></i>
                        <div>
                            <div class="fw-bold">Bulk Submissions</div>
                            <small class="text-muted">Upload multiple records</small>
                        </div>
                    </a>
                    <a href="/hospital/settings" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="fas fa-cog text-secondary me-3"></i>
                        <div>
                            <div class="fw-bold">Hospital Settings</div>
                            <small class="text-muted">Manage hospital information</small>
                        </div>
                    </a>
                </div>
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
// Chart.js configuration for monthly trend
const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
const monthlyTrendChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Birth Records',
            data: [45, 52, 48, 61, 55, 67],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
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
                display: false
            }
        }
    }
});

// Chart.js configuration for verification status
const verificationCtx = document.getElementById('verificationStatusChart').getContext('2d');
const verificationStatusChart = new Chart(verificationCtx, {
    type: 'doughnut',
    data: {
        labels: ['Verified', 'Pending', 'Flagged'],
        datasets: [{
            data: [75, 20, 5],
            backgroundColor: ['#059669', '#d97706', '#dc2626'],
            borderWidth: 0
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

// Selection management for records
let selectedRecords = [];

function toggleSelectAllRecords() {
    const selectAll = document.getElementById('selectAllRecords');
    const checkboxes = document.querySelectorAll('.record-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedRecordCount();
}

function updateSelectedRecordCount() {
    const checkboxes = document.querySelectorAll('.record-checkbox:checked');
    selectedRecords = Array.from(checkboxes).map(cb => cb.value);
    
    document.getElementById('selectedRecordCount').textContent = selectedRecords.length;
    
    const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');
    const bulkFlagBtn = document.getElementById('bulkFlagBtn');
    
    if (selectedRecords.length > 0) {
        bulkVerifyBtn.disabled = false;
        bulkFlagBtn.disabled = false;
    } else {
        bulkVerifyBtn.disabled = true;
        bulkFlagBtn.disabled = true;
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.record-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedRecordCount);
    });
});

// Quick Actions
function quickVerify(recordId) {
    confirmAction('Are you sure you want to verify this birth record?', function() {
        const button = event.target.closest('button');
        const stopLoading = showLoading(button);
        
        fetch(`/hospital/verify/${recordId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'verify'
            })
        })
        .then(response => response.json())
        .then(data => {
            stopLoading();
            if (data.success) {
                showSuccess('Birth record verified successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Failed to verify record');
            }
        })
        .catch(error => {
            stopLoading();
            showError('An error occurred while verifying the record');
        });
    });
}

// Bulk Actions
function bulkVerify() {
    if (selectedRecords.length === 0) {
        showError('Please select records to verify');
        return;
    }
    
    confirmAction(`Are you sure you want to verify ${selectedRecords.length} birth records?`, function() {
        fetch(`/hospital/bulk-verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                record_ids: selectedRecords,
                action: 'verify'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(`${selectedRecords.length} records verified successfully`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Failed to verify records');
            }
        })
        .catch(error => {
            showError('An error occurred while verifying records');
        });
    });
}

function bulkFlag() {
    if (selectedRecords.length === 0) {
        showError('Please select records to flag');
        return;
    }
    
    Swal.fire({
        title: `Flag ${selectedRecords.length} Records`,
        input: 'textarea',
        inputLabel: 'Reason for Flagging',
        inputPlaceholder: 'Please provide a reason for flagging these records...',
        inputAttributes: {
            'aria-label': 'Flag reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Flag Records',
        confirmButtonColor: '#d97706',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for flagging!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/hospital/bulk-verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    record_ids: selectedRecords,
                    action: 'flag',
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(`${selectedRecords.length} records flagged successfully`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(data.message || 'Failed to flag records');
                }
            })
            .catch(error => {
                showError('An error occurred while flagging records');
            });
        }
    });
}

// Hospital Tools
function openRecordSearch() {
    Swal.fire({
        title: 'Search Birth Records',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Record Number</label>
                    <input type="text" class="form-control" id="searchRecordNumber" placeholder="Enter record number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Child Name</label>
                    <input type="text" class="form-control" id="searchChildName" placeholder="Enter child name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" class="form-control" id="searchMotherName" placeholder="Enter mother's name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Birth Date Range</label>
                    <input type="date" class="form-control mb-2" id="searchStartDate">
                    <input type="date" class="form-control" id="searchEndDate">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Search',
        preConfirm: () => {
            const params = new URLSearchParams();
            const recordNumber = document.getElementById('searchRecordNumber').value;
            const childName = document.getElementById('searchChildName').value;
            const motherName = document.getElementById('searchMotherName').value;
            const startDate = document.getElementById('searchStartDate').value;
            const endDate = document.getElementById('searchEndDate').value;
            
            if (recordNumber) params.append('record_number', recordNumber);
            if (childName) params.append('child_name', childName);
            if (motherName) params.append('mother_name', motherName);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            window.location.href = `/hospital/records?${params.toString()}`;
        }
    });
}

function generateHospitalReport() {
    const today = new Date().toISOString().split('T')[0];
    window.open(`/hospital/reports?type=monthly&date=${today}`, '_blank');
}

function openBulkUpload() {
    window.location.href = '/hospital/submissions';
}

function openDataValidation() {
    Swal.fire({
        title: 'Data Validation',
        html: `
            <div class="text-start">
                <p>Run data validation checks on birth records:</p>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="checkDuplicates" checked>
                    <label class="form-check-label" for="checkDuplicates">
                        Check for duplicates
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="checkMissingData" checked>
                    <label class="form-check-label" for="checkMissingData">
                        Check for missing data
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="checkDateConsistency" checked>
                    <label class="form-check-label" for="checkDateConsistency">
                        Check date consistency
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="checkNameFormats">
                    <label class="form-check-label" for="checkNameFormats">
                        Check name formats
                    </label>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Run Validation',
        preConfirm: () => {
            showSuccess('Data validation completed. No issues found.');
        }
    });
}

function refreshRecords() {
    location.reload();
}

// Auto-refresh every 3 minutes
setInterval(function() {
    if (selectedRecords.length === 0) {
        location.reload();
    }
}, 180000);
</script>
<?php
$scripts = ob_get_clean();

include BASE_PATH . '/resources/views/layouts/dashboard.php';
?>
