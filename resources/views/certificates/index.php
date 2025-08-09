<?php
// resources/views/certificates/index.php
$pageTitle = $pageTitle ?? 'Certificates';
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
        .certificate-card {
            transition: transform 0.2s ease;
            margin-bottom: 20px;
        }
        .certificate-card:hover {
            transform: translateY(-3px);
        }
        .certificate-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            z-index: 10;
        }
        .certificate-details {
            padding: 15px;
        }
        .certificate-number {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .qr-preview {
            text-align: center;
            margin-top: 10px;
        }
        .certificate-actions {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
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
                <p class="text-muted">View and manage birth certificates</p>
            </div>
            <div>
                <a href="/dashboard" class="btn btn-outline-secondary me-2">
                    <i class="fa fa-tachometer"></i> Dashboard
                </a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="/reports/certificates" class="btn btn-outline-primary">
                    <i class="fa fa-bar-chart"></i> Certificate Reports
                </a>
                <?php endif; ?>
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
                <form action="/certificates" method="get">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search by certificate #, name" name="search" value="<?= htmlspecialchars($search ?? '') ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="revoked" <?= ($status ?? '') === 'revoked' ? 'selected' : '' ?>>Revoked</option>
                                <option value="expired" <?= ($status ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="date" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                <option value="today" <?= ($date ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="week" <?= ($date ?? '') === 'week' ? 'selected' : '' ?>>This Week</option>
                                <option value="month" <?= ($date ?? '') === 'month' ? 'selected' : '' ?>>This Month</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Certificates Display -->
        <?php if (!empty($certificates)): ?>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Child Name</th>
                                    <th>Date of Birth</th>
                                    <th>Issue Date</th>
                                    <th>Status</th>
                                    <th>Issued By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certificates as $cert): ?>
                                    <tr>
                                        <td>
                                            <span class="certificate-number"><?= htmlspecialchars($cert['certificate_number']) ?></span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cert['child_first_name']) ?> <?= htmlspecialchars($cert['child_last_name']) ?>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($cert['date_of_birth'] ?? 'now')) ?></td>
                                        <td><?= date('M j, Y', strtotime($cert['issued_at'] ?? 'now')) ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = 'bg-success';
                                                if (($cert['status'] ?? '') === 'revoked') {
                                                    $statusClass = 'bg-danger';
                                                } elseif (($cert['status'] ?? '') === 'expired') {
                                                    $statusClass = 'bg-secondary';
                                                }
                                            ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= ucfirst($cert['status'] ?? 'Active') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cert['issued_by_first_name'] ?? '') ?> <?= htmlspecialchars($cert['issued_by_last_name'] ?? '') ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/certificates/<?= $cert['id'] ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="View Certificate">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="/certificates/<?= $cert['id'] ?>/download" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'registrar'])): ?>
                                                    <?php if (($cert['status'] ?? '') === 'active'): ?>
                                                        <a href="/certificates/<?= $cert['id'] ?>/revoke" class="btn btn-outline-danger revoke-btn" data-bs-toggle="tooltip" title="Revoke Certificate">
                                                            <i class="fa fa-ban"></i>
                                                        </a>
                                                    <?php elseif (($cert['status'] ?? '') === 'revoked'): ?>
                                                        <a href="/certificates/<?= $cert['id'] ?>/activate" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="Activate Certificate">
                                                            <i class="fa fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <a href="/certificates/<?= $cert['id'] ?>/verify" class="btn btn-outline-info" data-bs-toggle="tooltip" title="Verify">
                                                    <i class="fa fa-check-circle"></i>
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
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fa fa-certificate fa-4x text-muted"></i>
                </div>
                <h5>No certificates found</h5>
                <p class="text-muted">No certificates match your current filters</p>
                <a href="/certificates" class="btn btn-outline-primary">Clear Filters</a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if (!empty($certificates) && isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= ($page ?? 1) - 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&date=<?= urlencode($date ?? '') ?>">
                                <i class="fa fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, ($page ?? 1) - 2);
                        $endPage = min($totalPages, ($page ?? 1) + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&date=<?= urlencode($date ?? '') ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($page ?? 1) == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&date=<?= urlencode($date ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&date=<?= urlencode($date ?? '') ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="page-item <?= ($page ?? 1) >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= ($page ?? 1) + 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&date=<?= urlencode($date ?? '') ?>">
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
            
            // Confirmation for revocation
            document.querySelectorAll('.revoke-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to revoke this certificate? This action will invalidate the certificate and can only be reversed by an administrator.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html> 