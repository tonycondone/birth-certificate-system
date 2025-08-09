<?php
// resources/views/registrar/batch-process.php
$pageTitle = $pageTitle ?? 'Batch Process Applications';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .page-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 0.85em;
            padding: 3px 8px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .batch-actions {
            position: sticky;
            bottom: 0;
            background-color: white;
            padding: 15px 0;
            border-top: 1px solid #dee2e6;
            z-index: 100;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .select-all-container {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= htmlspecialchars($pageTitle) ?></h2>
                    <p class="text-muted">Process multiple applications at once</p>
                </div>
                <div>
                    <a href="/registrar/pending" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Pending
                    </a>
                </div>
            </div>
        </div>

        <!-- Batch Processing Form -->
        <form id="batchProcessForm" action="/registrar/batch-process" method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <!-- Select All Option -->
            <div class="select-all-container">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label fw-bold" for="selectAll">
                        Select All Applications
                    </label>
                </div>
                <p class="text-muted small mt-1">
                    <i class="fa fa-info-circle"></i> 
                    Use this option to select or deselect all applications at once
                </p>
            </div>
            
            <!-- Applications Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pending Applications</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($applications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40px" class="text-center">Select</th>
                                        <th>Application #</th>
                                        <th>Child Name</th>
                                        <th>Date of Birth</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Applicant</th>
                                        <th class="text-center">Days Pending</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td class="text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input app-checkbox" type="checkbox" name="application_ids[]" value="<?= $app['id'] ?>">
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($app['reference_number'] ?? $app['application_number'] ?? $app['id']) ?></td>
                                            <td>
                                                <a href="/registrar/review/<?= $app['id'] ?>" class="text-decoration-none" target="_blank">
                                                    <?= htmlspecialchars($app['child_first_name'] ?? '') ?> <?= htmlspecialchars($app['child_last_name'] ?? '') ?>
                                                </a>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($app['date_of_birth'] ?? 'now')) ?></td>
                                            <td><?= date('M j, Y', strtotime($app['created_at'] ?? $app['submitted_at'] ?? 'now')) ?></td>
                                            <td>
                                                <span class="badge <?= ($app['status'] ?? '') === 'submitted' ? 'bg-warning text-dark' : 'bg-info' ?> status-badge">
                                                    <?= ucwords(str_replace('_', ' ', $app['status'] ?? 'pending')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($app['applicant_email'] ?? '') ?>">
                                                    <?= htmlspecialchars($app['applicant_first_name'] ?? '') ?> <?= htmlspecialchars($app['applicant_last_name'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= ($app['days_pending'] ?? 0) > 7 ? 'bg-danger' : 'bg-secondary' ?>">
                                                    <?= $app['days_pending'] ?? 0 ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fa fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h5>No pending applications</h5>
                            <p class="text-muted">There are no applications waiting for review</p>
                            <a href="/registrar/dashboard" class="btn btn-primary">Return to Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($applications)): ?>
                <!-- Batch Actions -->
                <div class="batch-actions">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="comments" class="form-label"><strong>Batch Comments</strong></label>
                                        <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Add comments to apply to all selected applications..."></textarea>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <label class="form-label"><strong>Action:</strong></label>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="action" id="actionApprove" value="approve" checked>
                                            <label class="btn btn-outline-success" for="actionApprove">
                                                <i class="fa fa-check"></i> Approve Selected
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="action" id="actionReject" value="reject">
                                            <label class="btn btn-outline-danger" for="actionReject">
                                                <i class="fa fa-times"></i> Reject Selected
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" id="processBatchBtn" class="btn btn-primary w-100 mt-3 mt-md-0" disabled>
                                        <i class="fa fa-play-circle"></i> Process <span id="selectedCount">0</span> Applications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Select all functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const appCheckboxes = document.querySelectorAll('.app-checkbox');
            const processBatchBtn = document.getElementById('processBatchBtn');
            const selectedCountEl = document.getElementById('selectedCount');
            
            function updateSelectedCount() {
                const checkedBoxes = document.querySelectorAll('.app-checkbox:checked');
                const count = checkedBoxes.length;
                selectedCountEl.textContent = count;
                processBatchBtn.disabled = count === 0;
            }
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    appCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedCount();
                });
            }
            
            appCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                    
                    // Update select all checkbox state
                    const allChecked = document.querySelectorAll('.app-checkbox:not(:checked)').length === 0;
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                    }
                });
            });
            
            // Form submission validation
            document.getElementById('batchProcessForm').addEventListener('submit', function(e) {
                const checkedBoxes = document.querySelectorAll('.app-checkbox:checked');
                const action = document.querySelector('input[name="action"]:checked').value;
                const comments = document.getElementById('comments').value.trim();
                
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one application to process.');
                    return;
                }
                
                if (action === 'reject' && comments === '') {
                    e.preventDefault();
                    alert('Comments are required when rejecting applications. Please provide a reason for rejection.');
                    return;
                }
                
                if (checkedBoxes.length > 10 && !confirm(`Are you sure you want to ${action} ${checkedBoxes.length} applications at once?`)) {
                    e.preventDefault();
                    return;
                }
            });
            
            // Initial count update
            updateSelectedCount();
        });
    </script>
</body>
</html> 