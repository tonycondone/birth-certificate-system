<?php
$pageTitle = 'Login - Digital Birth Certificate System';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" method="POST" action="/auth/login" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : ''; ?>">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me"
                                       <?php echo isset($_COOKIE['remember_email']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="rememberMe">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="/auth/forgot-password" class="text-decoration-none">Forgot Password?</a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        Don't have an account? <a href="/register" class="text-decoration-none">Register here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Clear error messages after 5 seconds
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(function() {
            alertElement.classList.add('fade');
            setTimeout(function() {
                alertElement.remove();
            }, 150);
        }, 5000);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>