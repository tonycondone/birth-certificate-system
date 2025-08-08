<?php require_once __DIR__ . '/../layouts/base.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Certificate Management</h1>
        <div>
            <a href="/admin/certificates/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Issue Certificate
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search certificates..." 
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($statusFilter == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="revoked" <?= ($statusFilter == 'revoked') ? 'selected' : '' ?>>Revoked</option>
                        <option value="expired" <?= ($statusFilter == 'expired') ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="/admin/certificates" class="btn btn-outline-secondary">Clear</a>
                    <button type="button" class="btn btn-outline-success" onclick="exportCertificates()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($certificates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
                    <h5>No certificates found</h5>
                    <p class="text-muted">No certificates match your search criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>Child Name</th>
                                <th>Parent</th>
                                <th>Status</th>
                                <th>Issue Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($cert['certificate_number'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="text-muted">App: <?= htmlspecialchars($cert['reference_number'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(($cert['child_first_name'] ?? '') . ' ' . ($cert['child_last_name'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars(($cert['parent_first_name'] ?? '') . ' ' . ($cert['parent_last_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($cert['email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($cert['status'] ?? 'active') {
                                            'active' => 'badge bg-success',
                                            'revoked' => 'badge bg-danger',
                                            'expired' => 'badge bg-warning text-dark',
                                            default => 'badge bg-secondary'
                                        };
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= ucfirst($cert['status'] ?? 'Active') ?></span>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($cert['issued_at'] ?? $cert['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/certificates/<?= $cert['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/certificates/download/<?= $cert['id'] ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <?php if (($cert['status'] ?? 'active') === 'active'): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="revokeCertificate(<?= $cert['id'] ?>)">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportCertificates() {
    window.location.href = '/admin/certificates/export?' + new URLSearchParams(window.location.search);
}

function revokeCertificate(id) {
    if (confirm('Are you sure you want to revoke this certificate? This action cannot be undone.')) {
        fetch(`/admin/certificates/${id}/revoke`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error revoking certificate: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error revoking certificate: ' + error.message);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 