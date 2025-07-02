<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-certificate"></i> Verification Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-search"></i> Search Applications
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Certificate Verification</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-sort"></i> Sort
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pending Applications Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Child Name</th>
                                <th>Parent Name</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApplications as $application): ?>
                            <tr data-application-id="<?= htmlspecialchars($application['id']) ?>">
                                <td><?= htmlspecialchars($application['id']) ?></td>
                                <td>
                                    <?= htmlspecialchars($application['child_first_name'] . ' ' . $application['child_last_name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($application['parent_first_name'] . ' ' . $application['parent_last_name']) ?>
                                </td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($application['created_at']))) ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars($application['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-success verify-btn"
                                            data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        >
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-danger reject-btn"
                                            data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        >
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-info view-details-btn"
                                            data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        >
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- No Pending Applications -->
                <?php if (empty($pendingApplications)): ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-info-circle"></i> No pending applications for verification.
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="verificationForm">
                        <input type="hidden" id="verificationApplicationId" name="application_id">
                        <div class="mb-3">
                            <label for="verificationNotes" class="form-label">Verification Notes</label>
                            <textarea class="form-control" id="verificationNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmVerificationBtn">Confirm Verification</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        <input type="hidden" id="rejectionApplicationId" name="application_id">
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectionReason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectionBtn">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
        const rejectionModal = new bootstrap.Modal(document.getElementById('rejectionModal'));

        // Verification Button Handler
        document.querySelectorAll('.verify-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-application-id');
                document.getElementById('verificationApplicationId').value = applicationId;
                verificationModal.show();
            });
        });

        // Confirm Verification Handler
        document.getElementById('confirmVerificationBtn').addEventListener('click', function() {
            const applicationId = document.getElementById('verificationApplicationId').value;
            const notes = document.getElementById('verificationNotes').value;

            fetch(`/certificates/verify/${applicationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                },
                body: JSON.stringify({ notes: notes })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Application verified successfully');
                    location.reload();
                } else {
                    alert('Verification failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during verification');
            });
        });

        // Rejection Button Handler
        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-application-id');
                document.getElementById('rejectionApplicationId').value = applicationId;
                rejectionModal.show();
            });
        });

        // Confirm Rejection Handler
        document.getElementById('confirmRejectionBtn').addEventListener('click', function() {
            const applicationId = document.getElementById('rejectionApplicationId').value;
            const reason = document.getElementById('rejectionReason').value;

            fetch(`/certificates/reject/${applicationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Application rejected successfully');
                    location.reload();
                } else {
                    alert('Rejection failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during rejection');
            });
        });
    });
    </script>
</body>
</html> 