
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
$pageTitle = 'My Applications - Digital Birth Certificate System';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-list me-2"></i>
                    My Applications
                </h2>
                <a href="/applications/new" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>
                    New Application
                </a>
            </div>

            <div class="card shadow">
                <div class="card-body">
                    <?php if (empty($applications ?? [])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Applications Found</h4>
                            <p class="text-muted">You haven't submitted any birth certificate applications yet.</p>
                            <a href="/applications/new" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>
                                Submit Your First Application
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Child's Name</th>
                                        <th>Date of Birth</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo htmlspecialchars($app['id']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['child_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['date_of_birth']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($app['status']) {
                                                    case 'pending':
                                                        $statusClass = 'badge bg-warning';
                                                        $statusText = 'Pending Review';
                                                        break;
                                                    case 'approved':
                                                        $statusClass = 'badge bg-success';
                                                        $statusText = 'Approved';
                                                        break;
                                                    case 'rejected':
                                                        $statusClass = 'badge bg-danger';
                                                        $statusText = 'Rejected';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'badge bg-info';
                                                        $statusText = 'Processing';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge bg-secondary';
                                                        $statusText = ucfirst($app['status']);
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/applications/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($app['status'] === 'pending'): ?>
                                                        <a href="/applications/<?php echo $app['id']; ?>/edit" 
                                                           class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteApplication(<?php echo $app['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($app['status'] === 'approved'): ?>
                                                        <a href="/certificates/download/<?php echo $app['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
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
                        location.reload();
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