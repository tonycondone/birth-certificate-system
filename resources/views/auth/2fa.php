<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .twofa-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .twofa-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .code-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 0.5rem;
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
        
    <div class="twofa-container d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card twofa-card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                                <h2 class="fw-bold">Two-Factor Authentication</h2>
                                <p class="text-muted">Enter the 6-digit code from your authenticator app</p>
                            </div>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="code" class="form-label text-center d-block">
                                        <i class="fas fa-key me-2"></i>Verification Code
                                    </label>
                                    <input type="text" class="form-control form-control-lg code-input" 
                                           id="code" name="code" maxlength="6" 
                                           pattern="[0-9]{6}" required>
                                    <div class="form-text text-center">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the 6-digit code from your authenticator app
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid 6-digit code.</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                                    <i class="fas fa-check me-2"></i>Verify Code
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <a href="/auth/logout" class="text-decoration-none">
                                    <i class="fas fa-sign-out-alt me-1"></i>Cancel Login
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="text-center mt-3">
                        <small class="text-white">
                            <i class="fas fa-shield-alt me-1"></i>
                            Enhanced security for your account
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
        
        // Auto-focus on code field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('code').focus();
        });
        
        // Auto-format code input
        document.getElementById('code').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    </script>
</body>
</html> 