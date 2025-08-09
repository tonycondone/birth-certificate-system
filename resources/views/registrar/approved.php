<?php
// resources/views/registrar/approved.php
$pageTitle = $pageTitle ?? 'Approved Applications';
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
        .search-container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .status-badge {
            font-size: 0.85em;
            padding: 3px 8px;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .table th {
            white-space: nowrap;
        }
        .search-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?= htmlspecialchars($pageTitle) ?></h2>
                <p class="text-muted">View and manage approved birth certificate applications</p>
            </div>
            <div>
                <a href="/registrar/dashboard" class="btn btn-outline-secondary me-2">
                    <i class="fa fa-tachometer"></i> Dashboard
                </a>
                <a href="/registrar/pending" class="btn btn-outline-primary">
                    <i class="fa fa-clock-o"></i> Pending Applications
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Search & Filters</h5>
                <span class="search-toggle text-primary" data-bs-toggle="collapse" data-bs-target="#searchCollapse">
                    <i class="fa fa-sliders"></i> Toggle Filters
                </span>
            </div>
            <div class="collapse show" id="searchCollapse">
                <form action="/dashboard/registrar/approved" method="get">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search by name, application #, or email" name="search" value="<?= htmlspecialchars($search ?? '') ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="date_filter" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                <option value="today" <?= ($dateFilter ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="week" <?= ($dateFilter ?? '') === 'week' ? 'selected' : '' ?>>This Week</option>
                                <option value="month" <?= ($dateFilter ?? '') === 'month' ? 'selected' : '' ?>>This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($applications)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Application #</th>
                                    <th>Child Name</th>
                                    <th>Date of Birth</th>
                                    <th>Applicant</th>
                                    <th>Certificate #</th>
                                    <th>Approval Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['reference_number'] ?? $app['application_number'] ?? $app['id']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($app['child_first_name']) ?> <?= htmlspecialchars($app['child_last_name']) ?>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($app['date_of_birth'] ?? 'now')) ?></td>
                                        <td>
                                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($app['applicant_email']) ?>">
                                                <?= htmlspecialchars($app['applicant_first_name']) ?> <?= htmlspecialchars($app['applicant_last_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['certificate_number'])): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($app['certificate_number']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Not Generated</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($app['reviewed_at'] ?? $app['updated_at'] ?? 'now')) ?></td>
                                        <td class="action-buttons">
                                            <div class="btn-group btn-group-sm">
                                                <a href="/registrar/review/<?= $app['id'] ?>" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <?php if (!empty($app['certificate_number'])): ?>
                                                    <a href="/certificates/download/<?= $app['id'] ?>" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Download Certificate">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/certificate/generate/<?= $app['id'] ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Generate Certificate">
                                                        <i class="fa fa-file-text"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/certificates/email/<?= $app['id'] ?>" class="btn btn-outline-info" data-bs-toggle="tooltip" title="Email Certificate">
                                                    <i class="fa fa-envelope"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fa fa-file-text-o fa-4x text-muted"></i>
                        </div>
                        <h5>No approved applications found</h5>
                        <p class="text-muted">No applications match your current filters</p>
                        <a href="/dashboard/registrar/approved" class="btn btn-outline-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (!empty($applications) && isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= ($page ?? 1) - 1 ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">
                                <i class="fa fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, ($page ?? 1) - 2);
                        $endPage = min($totalPages, ($page ?? 1) + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($page ?? 1) == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="page-item <?= ($page ?? 1) >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= ($page ?? 1) + 1 ?>&search=<?= urlencode($search ?? '') ?>&date_filter=<?= urlencode($dateFilter ?? '') ?>">
                                Next <i class="fa fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html> 