<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-floating {
            margin-bottom: 1rem;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #dc3545; }
        .strength-medium { background: #ffc107; }
        .strength-strong { background: #28a745; }
    </style>
</head>
<body class="bg-light">
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
        
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Create Account</h2>
                            <p class="text-muted">Join our secure birth certificate system</p>
                        </div>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               placeholder="First Name" required>
                                        <label for="first_name">
                                            <i class="fas fa-user me-2"></i>First Name
                                        </label>
                                        <div class="invalid-feedback">Please provide your first name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               placeholder="Last Name" required>
                                        <label for="last_name">
                                            <i class="fas fa-user me-2"></i>Last Name
                                        </label>
                                        <div class="invalid-feedback">Please provide your last name.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Email Address" required>
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Phone Number">
                                <label for="phone">
                                    <i class="fas fa-phone me-2"></i>Phone Number (Optional)
                                </label>
                                <div class="invalid-feedback">Please provide a valid phone number.</div>
                            </div>
                            
                            <div class="form-floating">
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="parent">Parent/Guardian</option>
                                    <option value="hospital">Hospital Staff</option>
                                    <option value="registrar">Government Registrar</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                <label for="role">
                                    <i class="fas fa-user-tag me-2"></i>Role
                                </label>
                                <div class="invalid-feedback">Please select your role.</div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Password" required>
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="password-strength" id="passwordStrength"></div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Minimum 8 characters with letters and numbers
                                </div>
                                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm Password" required>
                                <label for="confirm_password">
                                    <i class="fas fa-lock me-2"></i>Confirm Password
                                </label>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank">Terms of Service</a> and 
                                    <a href="/privacy" target="_blank">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">You must agree before submitting.</div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Subscribe to our newsletter for updates and announcements
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="/login" class="text-decoration-none">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength < 3) {
                strengthBar.classList.add('strength-weak');
            } else if (strength < 5) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = '(' + value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3);
                } else {
                    value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
                }
            }
            this.value = value;
        });
    </script>
</body>
</html>