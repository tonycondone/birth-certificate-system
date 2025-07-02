
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
$pageTitle = '429 - Too Many Requests';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="display-1 fw-bold text-info mb-4">429</div>
            <h2 class="mb-3">Too Many Requests</h2>
            <p class="lead text-muted mb-4">
                You've made too many requests in a short period. Please wait a moment before trying again.
            </p>
            
            <div class="alert alert-info" role="alert">
                <i class="fas fa-clock me-2"></i>
                <strong>Rate Limit Exceeded:</strong>
                <ul class="list-unstyled mt-2 mb-0">
                    <li>• Please wait 15 minutes before trying again</li>
                    <li>• This helps protect our system from abuse</li>
                    <li>• If you need immediate access, contact support</li>
                    <li>• Consider using our API for bulk operations</li>
                </ul>
            </div>
            
            <div class="countdown-timer mb-4">
                <div class="h4 text-primary">Please wait:</div>
                <div class="display-6 fw-bold text-primary" id="countdown">15:00</div>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progress-bar" style="width: 100%"></div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <button onclick="window.location.reload()" class="btn btn-primary btn-lg me-md-2" id="retry-btn" disabled>
                    <i class="fas fa-redo me-2"></i>Try Again
                </button>
                <a href="/home" class="btn btn-outline-primary btn-lg me-md-2">
                    <i class="fas fa-home me-2"></i>Go to Home
                </a>
                <a href="/contact" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
            
            <div class="mt-5">
                <small class="text-muted">
                    Rate limit reference: <code><?php echo htmlspecialchars(uniqid('429-')); ?></code>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer functionality
let timeLeft = 15 * 60; // 15 minutes in seconds
const countdownElement = document.getElementById('countdown');
const progressBar = document.getElementById('progress-bar');
const retryBtn = document.getElementById('retry-btn');

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    const progress = (timeLeft / (15 * 60)) * 100;
    progressBar.style.width = progress + '%';
    
    if (timeLeft <= 0) {
        retryBtn.disabled = false;
        retryBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Try Again';
        countdownElement.textContent = 'Ready!';
        progressBar.style.width = '0%';
        return;
    }
    
    timeLeft--;
    setTimeout(updateCountdown, 1000);
}

updateCountdown();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 