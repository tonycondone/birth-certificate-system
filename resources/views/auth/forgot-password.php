<?php
$pageTitle = 'Forgot Password - Digital Birth Certificate System';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Reset Password
                    </h4>
                    <p class="mb-0 mt-2 opacity-75">Enter your email to receive reset instructions</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="forgotPasswordForm" method="POST" action="/auth/reset-password" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required
                                   placeholder="Enter your registered email address"
                                   autocomplete="email">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid email address.
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="resetBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span id="resetBtnText">Send Reset Link</span>
                                <span id="resetBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info border-0 py-2 mb-0" role="alert">
                            <small>
                                <i class="fas fa-shield-alt me-1"></i>
                                Reset link will be sent to your email and expire in 1 hour.
                            </small>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2">Remember your password?</p>
                        <a href="/login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Back to Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Info Cards -->
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center py-3">
                            <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                            <h6 class="card-title">Quick Process</h6>
                            <small class="text-muted">Reset link sent within minutes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center py-3">
                            <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                            <h6 class="card-title">Secure Reset</h6>
                            <small class="text-muted">One-time use secure tokens</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const email = document.getElementById('email');
    const resetBtn = document.getElementById('resetBtn');
    const resetBtnText = document.getElementById('resetBtnText');
    const resetBtnSpinner = document.getElementById('resetBtnSpinner');
    
    // Real-time email validation
    email.addEventListener('input', function() {
        const emailValue = this.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailValue && !emailRegex.test(emailValue)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Form submission with loading state
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            // Show loading state
            resetBtn.disabled = true;
            resetBtnText.textContent = 'Sending...';
            resetBtnSpinner.style.display = 'inline-block';
        }
        form.classList.add('was-validated');
    });

    // Auto-focus email field
    email.focus();

    // Clear error messages after 8 seconds
    const alertElements = document.querySelectorAll('.alert');
    alertElements.forEach(alert => {
        setTimeout(function() {
            alert.classList.add('fade');
            setTimeout(function() {
                alert.remove();
            }, 150);
        }, 8000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter to submit form
        if (e.ctrlKey && e.key === 'Enter') {
            form.dispatchEvent(new Event('submit'));
        }
    });

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});
</script>

<style>
.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-lg {
    border-radius: 10px;
}

.alert {
    border-radius: 10px;
}

.opacity-75 {
    opacity: 0.75;
}

/* Loading animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.btn:disabled {
    animation: pulse 1.5s infinite;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 