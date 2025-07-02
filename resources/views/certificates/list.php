<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
        
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-list me-2"></i>Certificates & Applications
            </h2>
            <a href="/certificate/apply" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Application
            </a>
        </div>

        <!-- Applications Section -->
        <?php if (!empty($applications)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    <?= $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar' ? 'All Applications' : 'My Applications' ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                                <th>Place of Birth</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar'): ?>
                                    <th>Applicant</th>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $application): ?>
                                <tr>
                                    <td><?= htmlspecialchars($application['child_name'] ?? '') ?></td>
                                    <td><?= date('M j, Y', strtotime($application['date_of_birth'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($application['gender'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($application['place_of_birth'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $application['status'] === 'approved' ? 'success' : ($application['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($application['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($application['created_at'] ?? '')) ?></td>
                                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar'): ?>
                                        <td><?= htmlspecialchars($application['applicant_email'] ?? '') ?></td>
                                        <td>
                                            <?php if ($application['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-success approve-btn" 
                                                        data-id="<?= $application['id'] ?>"
                                                        data-name="<?= htmlspecialchars($application['child_name']) ?>">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger reject-btn" 
                                                        data-id="<?= $application['id'] ?>"
                                                        data-name="<?= htmlspecialchars($application['child_name']) ?>">
                                                    <i class="fas fa-times me-1"></i>Reject
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Processed</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>No Applications Found</h5>
                <p class="text-muted">You haven't submitted any applications yet.</p>
                <a href="/certificate/apply" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Submit Your First Application
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certificates Section -->
        <?php if (!empty($certificates)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-certificate me-2"></i>
                    <?= $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar' ? 'All Certificates' : 'My Certificates' ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Certificate Number</th>
                                <th>Child Name</th>
                                <th>Date of Birth</th>
                                <th>Issue Date</th>
                                <th>Status</th>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar'): ?>
                                    <th>Applicant</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $certificate): ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($certificate['certificate_number'] ?? '') ?></code>
                                    </td>
                                    <td><?= htmlspecialchars($certificate['child_name'] ?? '') ?></td>
                                    <td><?= date('M j, Y', strtotime($certificate['date_of_birth'] ?? '')) ?></td>
                                    <td><?= date('M j, Y', strtotime($certificate['issued_at'] ?? '')) ?></td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar'): ?>
                                        <td><?= htmlspecialchars($certificate['applicant_email'] ?? '') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="/certificate/download?id=<?= $certificate['id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                        <a href="/verify?certificate_number=<?= $certificate['certificate_number'] ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-search me-1"></i>Verify
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                <h5>No Certificates Found</h5>
                <p class="text-muted">No certificates have been issued yet.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <span id="actionType"></span> the application for <strong id="childName"></strong>?</p>
                    <div id="rejectComments" style="display: none;">
                        <label for="comments" class="form-label">Rejection Comments (Optional)</label>
                        <textarea class="form-control" id="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentAction = '';
        let currentApplicationId = '';
        
        // Approve button click
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentAction = 'approve';
                currentApplicationId = this.dataset.id;
                document.getElementById('actionType').textContent = 'approve';
                document.getElementById('childName').textContent = this.dataset.name;
                document.getElementById('rejectComments').style.display = 'none';
                document.getElementById('confirmBtn').className = 'btn btn-success';
                document.getElementById('confirmBtn').textContent = 'Approve';
                new bootstrap.Modal(document.getElementById('approvalModal')).show();
            });
        });
        
        // Reject button click
        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentAction = 'reject';
                currentApplicationId = this.dataset.id;
                document.getElementById('actionType').textContent = 'reject';
                document.getElementById('childName').textContent = this.dataset.name;
                document.getElementById('rejectComments').style.display = 'block';
                document.getElementById('confirmBtn').className = 'btn btn-danger';
                document.getElementById('confirmBtn').textContent = 'Reject';
                new bootstrap.Modal(document.getElementById('approvalModal')).show();
            });
        });
        
        // Confirm action
        document.getElementById('confirmBtn').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('application_id', currentApplicationId);
            formData.append('action', currentAction);
            
            if (currentAction === 'reject') {
                formData.append('comments', document.getElementById('comments').value);
            }
            
            fetch('/certificate/approve', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html> 