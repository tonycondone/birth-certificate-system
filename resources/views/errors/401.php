<?php
$pageTitle = '401 - Unauthorized';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="display-1 fw-bold text-warning mb-4">401</div>
            <h2 class="mb-3">Unauthorized Access</h2>
            <p class="lead text-muted mb-4">
                You need to be authenticated to access this resource. Please log in with valid credentials.
            </p>
            
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Authentication Required:</strong>
                <ul class="list-unstyled mt-2 mb-0">
                    <li>• Please log in with your account credentials</li>
                    <li>• Make sure your session hasn't expired</li>
                    <li>• Verify you have the correct username and password</li>
                    <li>• Contact support if you've forgotten your credentials</li>
                </ul>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/login" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Now
                </a>
                <a href="/register" class="btn btn-outline-primary btn-lg me-md-2">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
                <a href="/auth/forgot-password" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-key me-2"></i>Forgot Password
                </a>
            </div>
            
            <div class="mt-5">
                <small class="text-muted">
                    Reference ID: <code><?php echo htmlspecialchars(uniqid('401-')); ?></code>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 