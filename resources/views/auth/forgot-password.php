<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .forgot-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .forgot-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
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
        
    <div class="forgot-container d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card forgot-card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-key fa-3x text-primary mb-3"></i>
                                <h2 class="fw-bold">Forgot Password?</h2>
                                <p class="text-muted">Enter your email to reset your password</p>
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
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" 
                                           id="email" name="email" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        We'll send you a link to reset your password
                                    </div>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <a href="/login" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="text-center mt-3">
                        <small class="text-white">
                            <i class="fas fa-shield-alt me-1"></i>
                            Reset links expire in 1 hour for security
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
    </script>
</body>
</html> 