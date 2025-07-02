<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="/certificates">
                    <i class="fas fa-list me-1"></i>Certificates
                </a>
                <a class="nav-link" href="/settings">
                    <i class="fas fa-cog me-1"></i>Settings
                </a>
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-user me-2"></i>My Profile
                    </h2>
                    <a href="/settings" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
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

                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-edit me-2"></i>Profile Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">
                                                <i class="fas fa-user me-1"></i>First Name *
                                            </label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                            <div class="invalid-feedback">Please enter your first name.</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">
                                                <i class="fas fa-user me-1"></i>Last Name *
                                            </label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                            <div class="invalid-feedback">Please enter your last name.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-1"></i>Email Address
                                        </label>
                                        <input type="email" class="form-control" id="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                                        <div class="form-text">Email address cannot be changed. Contact support if needed.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        <div class="form-text">For important notifications and communications</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">
                                            <i class="fas fa-user-tag me-1"></i>Account Role
                                        </label>
                                        <input type="text" class="form-control" id="role" 
                                               value="<?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?>" readonly>
                                        <div class="form-text">Your account role determines your system permissions</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Summary -->
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                                <h5><?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></h5>
                                <p class="text-muted"><?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></p>
                                <span class="badge bg-success">Active Account</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Account Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Member Since</small><br>
                                    <strong><?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Last Login</small><br>
                                    <strong><?= isset($_SESSION['login_time']) ? date('F j, Y g:i A', $_SESSION['login_time']) : 'N/A' ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Account Status</small><br>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="/certificate/apply" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>New Application
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/certificates" class="btn btn-outline-info w-100">
                                    <i class="fas fa-list me-2"></i>View Certificates
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/settings" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-lock me-2"></i>Change Password
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/dashboard" class="btn btn-outline-success w-100">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>Account Security
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-lock me-2"></i>Password</h6>
                                <p class="text-muted">Last changed: <?= date('F j, Y') ?></p>
                                <a href="/settings" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-key me-1"></i>Change Password
                                </a>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-mobile-alt me-2"></i>Two-Factor Authentication</h6>
                                <p class="text-muted">Status: <span class="badge bg-secondary">Not Enabled</span></p>
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="fas fa-cog me-1"></i>Setup 2FA
                                </button>
                            </div>
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
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            this.value = value;
        });
    </script>
</body>
</html>