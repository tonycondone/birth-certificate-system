<?php
$pageTitle = 'Pending Reviews - Registrar Dashboard';
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
                            <?php 
                            // Handle both session structures
                            if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
                                $firstName = $_SESSION['user']['first_name'] ?? 'User';
                                $lastName = $_SESSION['user']['last_name'] ?? '';
                            } else {
                                $firstName = $_SESSION['first_name'] ?? 'User';
                                $lastName = $_SESSION['last_name'] ?? '';
                            }
                            ?>
                            <h5 class="mb-1"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h5>
                            <small class="text-muted">Registrar Portal</small>
                        </div>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/registrar">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard/registrar/pending">
                                <i class="fas fa-clock me-2"></i> Pending Reviews
                                <?php if (isset($pendingCount) && $pendingCount > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $pendingCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/registrar/approved">
                                <i class="fas fa-check-circle me-2"></i> Approved Certificates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/registrar/reports">
                                <i class="fas fa-chart-bar me-2"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/registrar/settings">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Applications</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="refreshList">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="bulkActionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionDropdown">
                                <li><a class="dropdown-item" href="#" id="bulkApprove">Approve Selected</a></li>
                                <li><a class="dropdown-item" href="#" id="bulkReject">Reject Selected</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="bulkExport">Export Selected</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" name="search" 
                                       placeholder="Search applications..." 
                                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                <button class="btn btn-primary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterHospital" name="hospital">
                                <option value="">All Hospitals</option>
                                <?php if (isset($hospitals)): ?>
                                    <?php foreach ($hospitals as $hospital): ?>
                                        <option value="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" 
                                                <?php echo ($hospitalFilter == $hospital['hospital_name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterDate" name="date">
                                <option value="">All Dates</option>
                                <option value="today" <?php echo ($dateFilter == 'today') ? 'selected' : ''; ?>>Today</option>
                                <option value="yesterday" <?php echo ($dateFilter == 'yesterday') ? 'selected' : ''; ?>>Yesterday</option>
                                <option value="this_week" <?php echo ($dateFilter == 'this_week') ? 'selected' : ''; ?>>This Week</option>
                                <option value="last_week" <?php echo ($dateFilter == 'last_week') ? 'selected' : ''; ?>>Last Week</option>
                                <option value="this_month" <?php echo ($dateFilter == 'this_month') ? 'selected' : ''; ?>>This Month</option>
                                <option value="last_month" <?php echo ($dateFilter == 'last_month') ? 'selected' : ''; ?>>Last Month</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" id="clearFilters">
                                Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Applications Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th>Reference</th>
                                    <th>Child Name</th>
                                    <th>Date of Birth</th>
                                    <th>Hospital</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingApplications)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-5">
                                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                                <h5>All Caught Up!</h5>
                                                <p class="text-muted">No pending applications to review</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingApplications as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input application-checkbox" 
                                                           type="checkbox" 
                                                           value="<?php echo $app['id']; ?>" 
                                                           id="app<?php echo $app['id']; ?>">
                                                    <label class="form-check-label" for="app<?php echo $app['id']; ?>"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo htmlspecialchars($app['reference_number']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['child_first_name'] . ' ' . $app['child_last_name']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($app['date_of_birth'])); ?></td>
                                            <td><?php echo htmlspecialchars($app['hospital_name']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">Pending Review</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/registrar/review/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i> Review
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success approve-btn"
                                                            data-id="<?php echo $app['id']; ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger reject-btn"
                                                            data-id="<?php echo $app['id']; ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (!empty($pendingApplications) && isset($totalPages) && $totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($hospitalFilter) ? '&hospital=' . urlencode($hospitalFilter) : ''; ?><?php echo !empty($dateFilter) ? '&date=' . urlencode($dateFilter) : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($hospitalFilter) ? '&hospital=' . urlencode($hospitalFilter) : ''; ?><?php echo !empty($dateFilter) ? '&date=' . urlencode($dateFilter) : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($hospitalFilter) ? '&hospital=' . urlencode($hospitalFilter) : ''; ?><?php echo !empty($dateFilter) ? '&date=' . urlencode($dateFilter) : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Birth Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm" action="/registrar/review/submit" method="post">
                    <input type="hidden" name="application_id" id="approveApplicationId">
                    <input type="hidden" name="action" value="approve">
                    
                    <div class="mb-3">
                        <label for="certificateNumber" class="form-label">Certificate Number</label>
                        <input type="text" class="form-control" id="certificateNumber" name="certificate_number" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="approvalNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approvalNotes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" checked>
                        <label class="form-check-label" for="sendNotification">
                            Send notification to parent
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="approveForm" class="btn btn-success">Approve Certificate</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" action="/registrar/review/submit" method="post">
                    <input type="hidden" name="application_id" id="rejectApplicationId">
                    <input type="hidden" name="action" value="reject">
                    
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason</label>
                        <select class="form-select" id="rejectionReason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="incomplete_information">Incomplete Information</option>
                            <option value="incorrect_information">Incorrect Information</option>
                            <option value="missing_documents">Missing Supporting Documents</option>
                            <option value="document_verification_failed">Document Verification Failed</option>
                            <option value="duplicate_application">Duplicate Application</option>
                            <option value="other">Other (Please specify)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejectionDetails" class="form-label">Additional Details</label>
                        <textarea class="form-control" id="rejectionDetails" name="details" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allowResubmission" name="allow_resubmission" checked>
                        <label class="form-check-label" for="allowResubmission">
                            Allow resubmission with corrections
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="rejectForm" class="btn btn-danger">Reject Application</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for search functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const filterHospital = document.getElementById('filterHospital');
    const filterDate = document.getElementById('filterDate');
    const clearFilters = document.getElementById('clearFilters');
    
    // Search form submission
    function submitSearch() {
        const searchValue = searchInput.value.trim();
        const hospitalValue = filterHospital.value;
        const dateValue = filterDate.value;
        
        let url = window.location.pathname + '?';
        
        if (searchValue) {
            url += 'search=' + encodeURIComponent(searchValue) + '&';
        }
        
        if (hospitalValue) {
            url += 'hospital=' + encodeURIComponent(hospitalValue) + '&';
        }
        
        if (dateValue) {
            url += 'date=' + encodeURIComponent(dateValue) + '&';
        }
        
        // Remove trailing &
        if (url.endsWith('&')) {
            url = url.slice(0, -1);
        }
        
        window.location.href = url;
    }
    
    // Event listeners
    searchButton.addEventListener('click', submitSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitSearch();
        }
    });
    
    filterHospital.addEventListener('change', submitSearch);
    filterDate.addEventListener('change', submitSearch);
    
    // Clear filters
    clearFilters.addEventListener('click', function() {
        window.location.href = window.location.pathname;
    });
    
    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        });
    }
    
    // Approve and reject buttons
    const approveButtons = document.querySelectorAll('.approve-btn');
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.getAttribute('data-id');
            document.getElementById('approveApplicationId').value = applicationId;
        });
    });
    
    const rejectButtons = document.querySelectorAll('.reject-btn');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.getAttribute('data-id');
            document.getElementById('rejectApplicationId').value = applicationId;
        });
    });
    
    // Bulk actions
    document.getElementById('bulkApprove')?.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) {
            alert('Please select at least one application to approve.');
            return;
        }
        
        if (confirm(`Are you sure you want to approve ${selectedIds.length} selected application(s)?`)) {
            // Submit form with selected IDs
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/registrar/bulk-approve';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'application_ids';
            input.value = JSON.stringify(selectedIds);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    document.getElementById('bulkReject')?.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) {
            alert('Please select at least one application to reject.');
            return;
        }
        
        if (confirm(`Are you sure you want to reject ${selectedIds.length} selected application(s)?`)) {
            // Submit form with selected IDs
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/registrar/bulk-reject';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'application_ids';
            input.value = JSON.stringify(selectedIds);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    document.getElementById('bulkExport')?.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) {
            alert('Please select at least one application to export.');
            return;
    }
    
        // Submit form with selected IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/registrar/export';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'application_ids';
        input.value = JSON.stringify(selectedIds);
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });
    
    function getSelectedApplicationIds() {
        const checkboxes = document.querySelectorAll('.application-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
