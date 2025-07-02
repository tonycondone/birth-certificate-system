
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
        <?php
$pageTitle = 'Hospital Dashboard';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-hospital fa-3x text-primary me-3"></i>
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['user']['hospital_name']); ?></h5>
                            <small class="text-muted">Hospital Staff Portal</small>
                        </div>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/hospital/dashboard">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/hospital/records/new">
                                <i class="fas fa-plus-circle me-2"></i> New Birth Record
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/hospital/verifications">
                                <i class="fas fa-check-circle me-2"></i> Pending Verifications
                                <?php if (isset($pendingVerifications) && $pendingVerifications > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $pendingVerifications; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/hospital/records">
                                <i class="fas fa-folder me-2"></i> All Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/hospital/settings">
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
                    <div class="card border-primary border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Records</h6>
                                    <h3 class="mb-0"><?php echo $totalRecords ?? 0; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-folder fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Pending Verifications</h6>
                                    <h3 class="mb-0"><?php echo $pendingVerifications ?? 0; ?></h3>
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
                                    <h6 class="text-muted mb-2">Verified Today</h6>
                                    <h3 class="mb-0"><?php echo $verifiedToday ?? 0; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
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
                                    <h6 class="text-muted mb-2">This Month</h6>
                                    <h3 class="mb-0"><?php echo $recordsThisMonth ?? 0; ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-calendar fa-2x text-info"></i>
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
                                <option value="pending">Pending Verification</option>
                                <option value="verified">Verified</option>
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

            <!-- Pending Verifications -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Verifications</h5>
                        <a href="/hospital/verifications" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingApplications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>All Caught Up!</h5>
                            <p class="text-muted">No pending verifications at the moment</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Parent Name</th>
                                        <th>Child Name</th>
                                        <th>Date of Birth</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingApplications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($app['parent_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['child_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($app['date_of_birth'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/hospital/verify/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-check me-1"></i> Verify
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal<?php echo $app['id']; ?>">
                                                        <i class="fas fa-times me-1"></i> Reject
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

            <!-- Recent Records -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Birth Records</h5>
                        <a href="/hospital/records" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recentRecords)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No Records Yet</h5>
                            <p class="text-muted">Start by submitting your first birth record</p>
                            <a href="/hospital/records/new" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>New Record
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Child Name</th>
                                        <th>Date of Birth</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRecords as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($record['child_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($record['date_of_birth'])); ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo ucfirst(htmlspecialchars($record['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['created_by']); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/hospital/records/<?php echo $record['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/hospital/records/<?php echo $record['id']; ?>/edit" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
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
        </div>
    </div>
</div>

<!-- Reject Modal Template -->
<?php foreach ($pendingApplications ?? [] as $app): ?>
    <div class="modal fade" id="rejectModal<?php echo $app['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/hospital/reject/<?php echo $app['id']; ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
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