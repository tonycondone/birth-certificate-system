<?php
$pageTitle = 'Register - Digital Birth Certificate System';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Create Your Account
                    </h4>
                    <p class="mb-0 mt-2 opacity-75">Join our secure birth certificate system</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="registrationForm" method="POST" action="/auth/register" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <!-- Progress Indicator -->
                        <div class="progress mb-4" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                        </div>
                        
                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-users me-1"></i>Register as
                            </label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="role" id="parent" value="parent" checked>
                                <label class="btn btn-outline-primary" for="parent">
                                    <i class="fas fa-home me-1"></i>Parent
                                </label>
                                
                                <input type="radio" class="btn-check" name="role" id="hospital" value="hospital">
                                <label class="btn btn-outline-primary" for="hospital">
                                    <i class="fas fa-hospital me-1"></i>Hospital
                                </label>
                                
                                <input type="radio" class="btn-check" name="role" id="registrar" value="registrar">
                                <label class="btn btn-outline-primary" for="registrar">
                                    <i class="fas fa-user-tie me-1"></i>Registrar
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required
                                       placeholder="Enter your first name"
                                       pattern="[A-Za-z\s]{2,50}">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>Please enter your first name (2-50 characters).
                                </div>
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">
                                    <i class="fas fa-user me-1"></i>Last Name
                                </label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required
                                       placeholder="Enter your last name"
                                       pattern="[A-Za-z\s]{2,50}">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>Please enter your last name (2-50 characters).
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter your email address"
                                   autocomplete="email">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid email address.
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Phone Number
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone_number" required
                                   placeholder="Enter your phone number"
                                   pattern="[\+]?[1-9][\d]{0,15}">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid phone number.
                            </div>
                        </div>

                        <!-- Role-specific fields -->
                        <div id="parentFields" class="mb-3">
                            <label for="nationalId" class="form-label">
                                <i class="fas fa-id-card me-1"></i>National ID
                            </label>
                            <input type="text" class="form-control" id="nationalId" name="national_id"
                                   placeholder="Enter your National ID number"
                                   pattern="[A-Z0-9]{6,20}">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid National ID (6-20 characters).
                            </div>
                        </div>

                        <div id="hospitalFields" class="mb-3 d-none">
                            <label for="hospitalId" class="form-label">
                                <i class="fas fa-hospital me-1"></i>Hospital Registration Number
                            </label>
                            <input type="text" class="form-control" id="hospitalId" name="hospital_id"
                                   placeholder="Enter hospital registration number"
                                   pattern="[A-Z0-9]{4,15}">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid hospital registration number.
                            </div>
                        </div>

                        <div id="registrarFields" class="mb-3 d-none">
                            <label for="registrarId" class="form-label">
                                <i class="fas fa-user-tie me-1"></i>Registrar ID
                            </label>
                            <input type="text" class="form-control" id="registrarId" name="registrar_id"
                                   placeholder="Enter your registrar ID"
                                   pattern="[A-Z0-9]{4,15}">
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid registrar ID.
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required 
                                       placeholder="Create a strong password"
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" id="passwordStrengthBar" role="progressbar"></div>
                                </div>
                                <small class="text-muted" id="passwordStrengthText">Password strength</small>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>Password must be at least 8 characters long and include letters and numbers.
                            </div>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Please enter a valid password.
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">
                                <i class="fas fa-lock me-1"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" required
                                       placeholder="Confirm your password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle me-1"></i>Passwords do not match.
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank" class="text-decoration-none">Terms and Conditions</a>
                                    and <a href="/privacy" target="_blank" class="text-decoration-none">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>You must agree to the terms and conditions.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                <i class="fas fa-user-plus me-2"></i>
                                <span id="registerBtnText">Create Account</span>
                                <span id="registerBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info border-0 py-2 mb-0" role="alert">
                            <small>
                                <i class="fas fa-shield-alt me-1"></i>
                                Your account will be protected by advanced security measures.
                            </small>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2">Already have an account?</p>
                        <a href="/login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const progressBar = document.getElementById('progressBar');
    const registerBtn = document.getElementById('registerBtn');
    const registerBtnText = document.getElementById('registerBtnText');
    const registerBtnSpinner = document.getElementById('registerBtnSpinner');
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Role-specific fields toggle
    roleInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('parentFields').classList.add('d-none');
            document.getElementById('hospitalFields').classList.add('d-none');
            document.getElementById('registrarFields').classList.add('d-none');
            
            const selectedRole = this.value;
            document.getElementById(selectedRole + 'Fields').classList.remove('d-none');
            
            // Update required attributes
            document.getElementById('nationalId').required = (selectedRole === 'parent');
            document.getElementById('hospitalId').required = (selectedRole === 'hospital');
            document.getElementById('registrarId').required = (selectedRole === 'registrar');
            
            updateProgress();
        });
    });

    // Password strength checker
    password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        updatePasswordStrength(strength);
        updateProgress();
    });

    // Confirm password validation
    confirmPassword.addEventListener('input', function() {
        if (this.value !== password.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
        updateProgress();
    });

    // Real-time validation for all fields
    const inputs = form.querySelectorAll('input[required], input[pattern]');
    inputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('blur', updateProgress);
    });

    // Form submission with loading state
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            // Show loading state
            registerBtn.disabled = true;
            registerBtnText.textContent = 'Creating Account...';
            registerBtnSpinner.style.display = 'inline-block';
        }
        form.classList.add('was-validated');
    });

    // Password strength checker function
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        return Math.min(strength, 100);
    }

    // Update password strength indicator
    function updatePasswordStrength(strength) {
        const bar = document.getElementById('passwordStrengthBar');
        const text = document.getElementById('passwordStrengthText');
        
        bar.style.width = strength + '%';
        
        if (strength < 25) {
            bar.className = 'progress-bar bg-danger';
            text.textContent = 'Very Weak';
        } else if (strength < 50) {
            bar.className = 'progress-bar bg-warning';
            text.textContent = 'Weak';
        } else if (strength < 75) {
            bar.className = 'progress-bar bg-info';
            text.textContent = 'Good';
        } else {
            bar.className = 'progress-bar bg-success';
            text.textContent = 'Strong';
        }
    }

    // Update progress bar
    function updateProgress() {
        const requiredFields = form.querySelectorAll('input[required]');
        const filledFields = Array.from(requiredFields).filter(field => {
            if (field.type === 'checkbox') {
                return field.checked;
            }
            return field.value.trim() !== '';
        });
        
        const progress = (filledFields.length / requiredFields.length) * 100;
        progressBar.style.width = progress + '%';
        
        if (progress === 100) {
            progressBar.className = 'progress-bar bg-success';
        } else if (progress >= 75) {
            progressBar.className = 'progress-bar bg-info';
        } else if (progress >= 50) {
            progressBar.className = 'progress-bar bg-warning';
        } else {
            progressBar.className = 'progress-bar bg-danger';
        }
    }

    // Initialize progress
    updateProgress();

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

    // Auto-focus first field
    document.getElementById('firstName').focus();
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

.password-strength .progress {
    border-radius: 2px;
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

.btn-group .btn {
    border-radius: 8px;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>