
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
$pageTitle = 'Application Details - Digital Birth Certificate System';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-file-alt me-2"></i>
                    Application Details
                </h2>
                <div>
                    <a href="/applications" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Applications
                    </a>
                    <?php if (isset($application) && $application['status'] === 'approved'): ?>
                        <a href="/certificates/download/<?php echo $application['id']; ?>" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>
                            Download Certificate
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($application)): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-baby me-2"></i>
                                    Child Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Child's Name:</strong><br>
                                        <?php echo htmlspecialchars($application['child_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date of Birth:</strong><br>
                                        <?php echo htmlspecialchars($application['date_of_birth']); ?></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Place of Birth:</strong><br>
                                        <?php echo htmlspecialchars($application['place_of_birth']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Gender:</strong><br>
                                        <?php echo ucfirst(htmlspecialchars($application['gender'])); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($application['notes'])): ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <p><strong>Additional Notes:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($application['notes'])); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($documents ?? [])): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-paperclip me-2"></i>
                                        Supporting Documents
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($documents as $doc): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <i class="fas fa-file me-2"></i>
                                                            <?php echo htmlspecialchars($doc['file_name']); ?>
                                                        </h6>
                                                        <p class="card-text text-muted">
                                                            Uploaded: <?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?>
                                                        </p>
                                                        <a href="/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-download me-1"></i>
                                                            View
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Application Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($application['status']) {
                                    case 'pending':
                                        $statusClass = 'badge bg-warning fs-6';
                                        $statusText = 'Pending';
                                        break;
                                    case 'submitted':
                                        $statusClass = 'badge bg-info fs-6';
                                        $statusText = 'In Progress';
                                        break;
                                    case 'approved':
                                        $statusClass = 'badge bg-success fs-6';
                                        $statusText = 'Approved';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'badge bg-danger fs-6';
                                        $statusText = 'Rejected';
                                        break;
                                    case 'processing':
                                        $statusClass = 'badge bg-info fs-6';
                                        $statusText = 'Processing';
                                        break;
                                    default:
                                        $statusClass = 'badge bg-secondary fs-6';
                                        $statusText = ucfirst($application['status']);
                                }
                                ?>
                                <div class="text-center mb-3">
                                    <span class="<?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <p><strong>Application ID:</strong><br>
                                    #<?php echo htmlspecialchars($application['id']); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <p><strong>Submitted:</strong><br>
                                    <?php echo date('M j, Y g:i A', strtotime($application['created_at'])); ?></p>
                                </div>
                                
                                <?php if (!empty($application['updated_at']) && $application['updated_at'] !== $application['created_at']): ?>
                                    <div class="mb-3">
                                        <p><strong>Last Updated:</strong><br>
                                        <?php echo date('M j, Y g:i A', strtotime($application['updated_at'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application['reviewer_notes'])): ?>
                                    <div class="mb-3">
                                        <p><strong>Reviewer Notes:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($application['reviewer_notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($canPay)): ?>
                            <div class="card shadow">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-edit me-2"></i>
                                        Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="/applications/<?php echo $application['id']; ?>/pay"
                                           class="btn btn-success">
                                            <i class="fas fa-credit-card me-2"></i>
                                            Pay Now
                                        </a>
                                        <a href="/applications/<?php echo $application['id']; ?>/edit" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-edit me-2"></i>
                                            Edit Application
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                onclick="deleteApplication(<?php echo $application['id']; ?>)">
                                            <i class="fas fa-trash me-2"></i>
                                            Delete Application
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4>Application Not Found</h4>
                        <p class="text-muted">The application you're looking for doesn't exist or you don't have permission to view it.</p>
                        <a href="/applications" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Applications
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteApplication(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/applications/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Deleted!',
                        'Your application has been deleted.',
                        'success'
                    ).then(() => {
                        window.location.href = '/applications';
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.error || 'An error occurred while deleting the application.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'An error occurred while deleting the application.',
                    'error'
                );
            });
        }
    });
}
</script> 