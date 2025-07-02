
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
$pageTitle = '403 - Access Denied';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="display-1 fw-bold text-danger mb-4">403</div>
            <h2 class="mb-3">Access Denied</h2>
            <p class="lead text-muted mb-4">
                You don't have permission to access this page. Please contact an administrator if you believe this is an error.
            </p>
            
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Possible reasons:</strong>
                <ul class="list-unstyled mt-2 mb-0">
                    <li>• You're not logged in</li>
                    <li>• Your account doesn't have the required permissions</li>
                    <li>• Your session has expired</li>
                    <li>• You're trying to access a restricted area</li>
                </ul>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/login" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a href="/home" class="btn btn-outline-primary btn-lg me-md-2">
                    <i class="fas fa-home me-2"></i>Go to Home
                </a>
                <a href="/contact" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
            
            <div class="mt-5">
                <small class="text-muted">
                    If you believe this is an error, please contact support with reference: 
                    <code><?php echo htmlspecialchars(uniqid('403-')); ?></code>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 