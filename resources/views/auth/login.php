<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .social-login-btn {
            transition: all 0.3s ease;
        }
        .social-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
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
        
    <div class="login-container d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card login-card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-certificate fa-3x text-primary mb-3"></i>
                                <h2 class="fw-bold">Welcome Back</h2>
                                <p class="text-muted">Sign in to your account</p>
                            </div>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="needs-validation" novalidate>
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Email Address" required>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <div class="invalid-feedback">Please provide your email address.</div>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Password" required>
                                    <label for="password">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="invalid-feedback">Please provide your password.</div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                        <label class="form-check-label" for="remember_me">
                                            Remember me
                                        </label>
                                    </div>
                                    <a href="/auth/forgot-password" class="text-decoration-none">
                                        Forgot password?
                                    </a>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                            
                            <div class="text-center mb-3">
                                <span class="text-muted">Or continue with</span>
                            </div>
                            
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary w-100 social-login-btn">
                                        <i class="fab fa-google me-2"></i>Google
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary w-100 social-login-btn">
                                        <i class="fab fa-microsoft me-2"></i>Microsoft
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? 
                                    <a href="/register" class="text-decoration-none fw-bold">Sign up here</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="text-center mt-3">
                        <small class="text-white">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your data is protected with bank-level security
                        </small>
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
        
        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
        
        // Show/hide password toggle
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-link position-absolute end-0 top-50 translate-middle-y';
            toggleBtn.style.zIndex = '10';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            
            const parent = passwordField.parentElement;
            parent.style.position = 'relative';
            parent.appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', function() {
                const type = passwordField.type === 'password' ? 'text' : 'password';
                passwordField.type = type;
                toggleBtn.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        });
    </script>
</body>
</html>