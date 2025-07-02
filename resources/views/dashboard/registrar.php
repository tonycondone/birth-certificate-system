<?php
$pageTitle = 'Registrar Dashboard';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-tie fa-3x text-primary me-3"></i>
                        <div>
                            <h5 class="mb-1">
                                <?php 
                                if (isset($_SESSION['user']) && isset($_SESSION['user']['first_name']) && isset($_SESSION['user']['last_name'])) {
                                    echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']);
                                } else {
                                    echo 'Registrar';
                                }
                                ?>
                            </h5>
                            <small class="text-muted">Registrar Portal</small>
                        </div>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/registrar/dashboard">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/registrar/pending">
                                <i class="fas fa-clock me-2"></i> Pending Reviews
                                <?php if (isset($pendingReviews) && $pendingReviews > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $pendingReviews; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/registrar/approved">
                                <i class="fas fa-check-circle me-2"></i> Approved Certificates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/registrar/reports">
                                <i class="fas fa-chart-bar me-2"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/registrar/settings">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-warning border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Pending Review</h6>
                                    <h3 class="mb-0"><?php echo $pendingReviews ?? 0; ?></h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Approved Today</h6>
                                    <h3 class="mb-0"><?php echo $approvedToday ?? 0; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Rejected Today</h6>
                                    <h3 class="mb-0"><?php echo $rejectedToday ?? 0; ?></h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Processed</h6>
                                    <h3 class="mb-0"><?php echo $totalProcessed ?? 0; ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-file-alt fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form id="searchForm" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchTerm" 
                                   placeholder="Search by name or reference...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="pending">Pending Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="filterDate">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pending Reviews -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Reviews</h5>
                        <a href="/registrar/pending" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingApplications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>All Caught Up!</h5>
                            <p class="text-muted">No pending applications to review</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Child Name</th>
                                        <th>Hospital</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingApplications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($app['child_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['hospital_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-warning">Pending Review</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/registrar/review/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        Review
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal<?php echo $app['id']; ?>">
                                                        Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal<?php echo $app['id']; ?>">
                                                        Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Approvals -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recently Approved</h5>
                        <a href="/registrar/approved" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($approvedCertificates)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                            <h5>No Recent Approvals</h5>
                            <p class="text-muted">You haven't approved any applications recently</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Child Name</th>
                                        <th>Hospital</th>
                                        <th>Approved On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvedCertificates as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($app['child_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['hospital_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($app['approved_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/registrar/view/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                    <a href="/registrar/print/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-print"></i> Print
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentActivities)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5>No Recent Activity</h5>
                            <p class="text-muted">Your recent activities will appear here</p>
                        </div>
                    <?php else: ?>
                    <div class="timeline">
                            <?php foreach ($recentActivities as $activity): ?>
                            <div class="timeline-item">
                                    <?php 
                                    $statusClass = 'primary';
                                    if (strpos(strtolower($activity['action']), 'approve') !== false) {
                                        $statusClass = 'success';
                                    } elseif (strpos(strtolower($activity['action']), 'reject') !== false) {
                                        $statusClass = 'danger';
                                    } elseif (strpos(strtolower($activity['action']), 'review') !== false) {
                                        $statusClass = 'warning';
                                    }
                                    ?>
                                    <div class="timeline-marker bg-<?php echo $statusClass; ?>"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y h:i A', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal Template -->
<?php foreach ($pendingApplications ?? [] as $app): ?>
    <div class="modal fade" id="approveModal<?php echo $app['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/registrar/approve/<?php echo $app['id']; ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="certificateNumber" class="form-label">Certificate Number</label>
                            <input type="text" class="form-control" id="certificateNumber" 
                                   name="certificate_number" required readonly 
                                   value="<?php echo htmlspecialchars($app['generated_certificate_number']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="approvalNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve & Generate Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Reject Modal Template -->
<?php foreach ($pendingApplications ?? [] as $app): ?>
    <div class="modal fade" id="rejectModal<?php echo $app['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/registrar/reject/<?php echo $app['id']; ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                            <div class="form-text">
                                Please provide a clear reason for rejection. This will be communicated to the applicant.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 7px;
    top: 15px;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-content {
    padding-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            // Implement search functionality
            const searchTerm = document.getElementById('searchTerm').value;
            const status = document.getElementById('filterStatus').value;
            const date = document.getElementById('filterDate').value;
            
            // Update URL with search params
            const params = new URLSearchParams(window.location.search);
            if (searchTerm) params.set('search', searchTerm);
            if (status) params.set('status', status);
            if (date) params.set('date', date);
            
            window.location.search = params.toString();
        });
    }

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>