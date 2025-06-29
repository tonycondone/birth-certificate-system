<?php
$pageTitle = '404 - Page Not Found';
require_once __DIR__ . '/../layouts/base.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="display-1 fw-bold text-primary mb-4">404</div>
            <h2 class="mb-3">Oops! Page Not Found</h2>
            <p class="lead text-muted mb-4">
                The page you are looking for does not exist, was moved, or is temporarily unavailable.
            </p>
            <a href="/home" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-home me-2"></i>Go to Home
            </a>
            <a href="/contact" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-envelope me-2"></i>Contact Support
            </a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>