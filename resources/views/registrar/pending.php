<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Pending Applications' ?> - Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .application-row {
            transition: all 0.3s ease;
        }
        .application-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .priority-high {
            border-left: 4px solid #dc3545;
        }
        .priority-medium {
            border-left: 4px solid #ffc107;
        }
        .priority-low {
            border-left: 4px solid #28a745;
        }
        .batch-actions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .action-btn {
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: scale(1.1);
        }
        .selected-row {
            background-color: #e3f2fd !important;
        }
        .bulk-actions-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 15px 30px;
            z-index: 1000;
            display: none;
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
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/registrar/dashboard">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="/registrar/pending">
                    <i class="fas fa-clock me-1"></i>Pending Applications
                </a>
                <a class="nav-link" href="/registrar/reports">
                    <i class="fas fa-chart-bar me-1"></i>Reports
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['email'] ?? 'Registrar') ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="dropdown-item" href="/auth/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-clock me-2 text-warning"></i>
                            Pending Applications
                        </h1>
                        <p class="text-muted mb-0">Review and process birth certificate applications</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshApplications()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <button class="btn btn-primary" onclick="showBatchModal()">
                            <i class="fas fa-layer-group me-2"></i>Batch Actions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Applications</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search ?? '') ?>"
                                   placeholder="Child name, application number, or email">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="date_filter" class="form-label">Date Filter</label>
                        <select class="form-select" id="date_filter" name="date_filter">
                            <option value="">All Time</option>
                            <option value="today" <?= ($dateFilter ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= ($dateFilter ?? '') === 'week' ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= ($dateFilter ?? '') === 'month' ? 'selected' : '' ?>>This Month</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority_filter" class="form-label">Priority</label>
                        <select class="form-select" id="priority_filter" onchange="filterByPriority(this.value)">
                            <option value="">All Priorities</option>
                            <option value="high">High Priority (7+ days)</option>
                            <option value="medium">Medium Priority (3-7 days)</option>
                            <option value="low">Low Priority (< 3 days)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Batch Actions Bar -->
        <div class="batch-actions">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="fas fa-layer-group me-2"></i>Batch Processing
                    </h5>
                    <p class="mb-0">Select multiple applications below to process them together</p>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light" onclick="selectAll()">
                            <i class="fas fa-check-square me-2"></i>Select All
                        </button>
                        <button class="btn btn-outline-light" onclick="clearSelection()">
                            <i class="fas fa-square me-2"></i>Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Applications Awaiting Review
                    <span class="badge bg-warning text-dark ms-2"><?= $totalCount ?? 0 ?></span>
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="sortTable('date')">
                        <i class="fas fa-sort-amount-down me-1"></i>Sort by Date
                    </button>
                    <button class="btn btn-outline-secondary" onclick="sortTable('priority')">
                        <i class="fas fa-exclamation-triangle me-1"></i>Sort by Priority
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($applications)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="applicationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                        </div>
                                    </th>
                                    <th>Child Information</th>
                                    <th>Applicant</th>
                                    <th>Submitted</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <?php
                                    $priority = 'low';
                                    $priorityClass = 'success';
                                    if ($app['days_pending'] > 7) {
                                        $priority = 'high';
                                        $priorityClass = 'danger';
                                    } elseif ($app['days_pending'] > 3) {
                                        $priority = 'medium';
                                        $priorityClass = 'warning';
                                    }
                                    ?>
                                    <tr class="application-row priority-<?= $priority ?>" data-priority="<?= $priority ?>" data-app-id="<?= $app['id'] ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input application-checkbox" type="checkbox" 
                                                       value="<?= $app['id'] ?>" onchange="updateBulkActions()">
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($app['child_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?= !empty($app['date_of_birth']) ? date('M j, Y', strtotime($app['date_of_birth'])) : '—' ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-hashtag me-1"></i>
                                                    <?= htmlspecialchars($app['application_number']) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <?= htmlspecialchars($app['applicant_first_name'] . ' ' . $app['applicant_last_name']) ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <?= htmlspecialchars($app['applicant_email']) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                                                                            <div>
                                                    <?php if (!empty($app['submitted_at'])): ?>
                                                        <?= date('M j, Y', strtotime($app['submitted_at'])) ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= date('g:i A', strtotime($app['submitted_at'])) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </div>
                                        </td>
                                        <td>
                                                                                            <span class="badge bg-<?= $priorityClass ?> <?= $priorityClass === 'warning' ? 'text-dark' : '' ?>">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= (int)($app['days_pending'] ?? 0) ?> days
                                                </span>
                                            <br>
                                            <small class="text-<?= $priorityClass ?>">
                                                <?= ucfirst($priority) ?> Priority
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= ucwords(str_replace('_', ' ', $app['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/registrar/review/<?= $app['id'] ?>" 
                                                   class="btn btn-outline-primary action-btn" 
                                                   title="Review Application">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success action-btn" 
                                                        onclick="quickApprove(<?= $app['id'] ?>)"
                                                        title="Quick Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger action-btn" 
                                                        onclick="quickReject(<?= $app['id'] ?>)"
                                                        title="Quick Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary dropdown-toggle action-btn" 
                                                            data-bs-toggle="dropdown" title="More Actions">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="/applications/<?= $app['id'] ?>">
                                                                <i class="fas fa-file-alt me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="requestMoreInfo(<?= $app['id'] ?>)">
                                                                <i class="fas fa-question-circle me-2"></i>Request Info
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="assignToOther(<?= $app['id'] ?>)">
                                                                <i class="fas fa-user-plus me-2"></i>Assign to Other
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Applications pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>All Caught Up!</h4>
                        <p class="text-muted">No pending applications match your current filters.</p>
                        <a href="/registrar/pending" class="btn btn-outline-primary">
                            <i class="fas fa-refresh me-2"></i>Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar (Fixed Position) -->
    <div class="bulk-actions-bar" id="bulkActionsBar">
        <div class="d-flex align-items-center gap-3">
            <span id="selectedCount">0 selected</span>
            <div class="btn-group">
                <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="fas fa-check me-2"></i>Approve Selected
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkReject()">
                    <i class="fas fa-times me-2"></i>Reject Selected
                </button>
                <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Batch Processing Modal -->
    <div class="modal fade" id="batchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group me-2"></i>Batch Processing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="batchForm">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="batchAction" id="batchApproveRadio" value="approve">
                                <label class="form-check-label" for="batchApproveRadio">
                                    <i class="fas fa-check text-success me-2"></i>Approve Selected Applications
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="batchAction" id="batchRejectRadio" value="reject">
                                <label class="form-check-label" for="batchRejectRadio">
                                    <i class="fas fa-times text-danger me-2"></i>Reject Selected Applications
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="batchComments" class="form-label">Comments</label>
                            <textarea class="form-control" id="batchComments" name="comments" rows="3" 
                                      placeholder="Enter comments for this batch action..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="batchSelectedCount">0</span> applications will be processed.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processBatch()">
                        <i class="fas fa-cog me-2"></i>Process Batch
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedApplications = [];

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.application-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkActions();
        }

        // Select all applications
        function selectAll() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
            updateBulkActions();
        }

        // Clear selection
        function clearSelection() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            updateBulkActions();
        }

        // Update bulk actions bar
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.application-checkbox:checked');
            const count = checkboxes.length;
            const bulkBar = document.getElementById('bulkActionsBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            
            selectedApplications = Array.from(checkboxes).map(cb => cb.value);
            
            if (count > 0) {
                selectedCountSpan.textContent = `${count} selected`;
                bulkBar.style.display = 'block';
            } else {
                bulkBar.style.display = 'none';
            }
            
            // Update batch modal count
            const batchCount = document.getElementById('batchSelectedCount');
            if (batchCount) {
                batchCount.textContent = count;
            }
        }

        // Quick approve
        function quickApprove(applicationId) {
            if (confirm('Are you sure you want to approve this application?')) {
                processApplication(applicationId, 'approve', '');
            }
        }

        // Quick reject
        function quickReject(applicationId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason !== null && reason.trim() !== '') {
                processApplication(applicationId, 'reject', reason);
            }
        }

        // Process single application
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
                    // Remove the row from table
                    const row = document.querySelector(`tr[data-app-id="${applicationId}"]`);
                    if (row) {
                        row.style.transition = 'all 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-100%)';
                        setTimeout(() => row.remove(), 500);
                    }
                } else {
                    showNotification('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error', 'An error occurred while processing the application');
            });
        }

        // Bulk approve
        function bulkApprove() {
            if (selectedApplications.length === 0) {
                alert('Please select applications to approve.');
                return;
            }
            
            if (confirm(`Are you sure you want to approve ${selectedApplications.length} applications?`)) {
                processBatchApplications('approve', '');
            }
        }

        // Bulk reject
        function bulkReject() {
            if (selectedApplications.length === 0) {
                alert('Please select applications to reject.');
                return;
            }
            
            const reason = prompt('Please provide a reason for rejecting these applications:');
            if (reason !== null && reason.trim() !== '') {
                processBatchApplications('reject', reason);
            }
        }

        // Show batch modal
        function showBatchModal() {
            if (selectedApplications.length === 0) {
                alert('Please select applications first.');
                return;
            }
            
            new bootstrap.Modal(document.getElementById('batchModal')).show();
        }

        // Process batch from modal
        function processBatch() {
            const action = document.querySelector('input[name="batchAction"]:checked')?.value;
            const comments = document.getElementById('batchComments').value;
            
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            if (selectedApplications.length === 0) {
                alert('No applications selected.');
                return;
            }
            
            processBatchApplications(action, comments);
            bootstrap.Modal.getInstance(document.getElementById('batchModal')).hide();
        }

        // Process batch applications
        function processBatchApplications(action, comments) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('comments', comments);
            selectedApplications.forEach(id => {
                formData.append('application_ids[]', id);
            });

            fetch('/registrar/batch-process', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Success', data.message);
                    // Remove processed rows
                    selectedApplications.forEach(id => {
                        const row = document.querySelector(`tr[data-app-id="${id}"]`);
                        if (row) {
                            row.style.transition = 'all 0.5s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-100%)';
                            setTimeout(() => row.remove(), 500);
                        }
                    });
                    clearSelection();
                } else {
                    showNotification('error', 'Error', data.message || 'Batch processing failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error', 'An error occurred during batch processing');
            });
        }

        // Filter by priority
        function filterByPriority(priority) {
            const rows = document.querySelectorAll('.application-row');
            rows.forEach(row => {
                if (priority === '' || row.dataset.priority === priority) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Sort table
        function sortTable(criteria) {
            const tbody = document.querySelector('#applicationsTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                if (criteria === 'date') {
                    const dateA = new Date(a.cells[3].textContent.trim());
                    const dateB = new Date(b.cells[3].textContent.trim());
                    return dateA - dateB;
                } else if (criteria === 'priority') {
                    const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
                    const priorityA = priorityOrder[a.dataset.priority] || 0;
                    const priorityB = priorityOrder[b.dataset.priority] || 0;
                    return priorityB - priorityA;
                }
                return 0;
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }

        // Refresh applications
        function refreshApplications() {
            location.reload();
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

        // Request more info
        function requestMoreInfo(applicationId) {
            const message = prompt('What
