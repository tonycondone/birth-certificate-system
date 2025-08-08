
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
$pageTitle = 'My Applications - Dashboard';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-circle fa-3x text-primary me-3"></i>
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
                            <small class="text-muted">Parent Portal</small>
                        </div>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/applications/new">
                                <i class="fas fa-plus-circle me-2"></i> New Application
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/profile">
                                <i class="fas fa-user-cog me-2"></i> Profile Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/notifications">
                                <i class="fas fa-bell me-2"></i> Notifications
                                <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $unreadNotifications; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Applications</h6>
                                    <h3 class="mb-0"><?php echo $totalApplications ?? 0; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-file-alt fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Approved</h6>
                                    <h3 class="mb-0"><?php echo $approvedApplications ?? 0; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning border-start border-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Pending</h6>
                                    <h3 class="mb-0"><?php echo $pendingApplications ?? 0; ?></h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="/applications" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No Applications Yet</h5>
                            <p class="text-muted">Start by submitting your first application</p>
                            <a href="/applications/new" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>New Application
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Child's Name</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($app['child_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'review' => 'info'
                                                ][$app['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($app['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/applications/<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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

            <!-- Application Timeline -->
            <?php if (!empty($latestApplication)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Latest Application Progress</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($latestApplication['timeline'] as $event): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?php echo $event['status_class']; ?>"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y h:i A', strtotime($event['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 7px;
    top: 15px;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-content {
    padding-bottom: 10px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>