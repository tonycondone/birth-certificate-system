
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
$pageTitle = 'System Error - Birth Certificate System';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-icon mb-4">
                <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
            </div>
            <h1 class="display-4 fw-bold text-primary mb-3">System Error</h1>
            <h2 class="mb-3">Something went wrong</h2>
            <p class="lead text-muted mb-4">
                We're experiencing technical difficulties. Please try again in a few moments.
            </p>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-info border-0 mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Error Details:</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/home" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-home me-2"></i>Go to Home
                </a>
                <a href="/contact" class="btn btn-outline-primary btn-lg me-md-2">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
                <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-redo me-2"></i>Try Again
                </button>
            </div>
            
            <div class="mt-5">
                <h5 class="text-muted mb-3">What you can do:</h5>
                <div class="row text-start">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                <h6>Wait a moment</h6>
                                <small class="text-muted">Try again in a few minutes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-browser fa-2x text-primary mb-2"></i>
                                <h6>Refresh the page</h6>
                                <small class="text-muted">Press F5 or click refresh</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                                <h6>Contact support</h6>
                                <small class="text-muted">Get help from our team</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 