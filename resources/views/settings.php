<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Digital Birth Certificate System</title>
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
                <a class="nav-link" href="/profile">
                    <i class="fas fa-user me-1"></i>Profile
                </a>
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">
                    <i class="fas fa-cog me-2"></i>Account Settings
                </h2>
                
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

                <!-- Password Change Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-key me-2"></i>Current Password
                                </label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>New Password
                                </label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required>
                                <div class="form-text">Minimum 8 characters</div>
                                <div class="invalid-feedback">Please enter a new password.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                                <div class="invalid-feedback">Please confirm your new password.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Account Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                                <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></p>
                                <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>First Name:</strong> <?= htmlspecialchars($user['first_name'] ?? '') ?></p>
                                <p><strong>Last Name:</strong> <?= htmlspecialchars($user['last_name'] ?? '') ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            These actions cannot be undone. Please be careful.
                        </p>
                        
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">
                        <strong>Warning:</strong> This action cannot be undone. All your data will be permanently deleted.
                    </p>
                    <p>To confirm deletion, please enter your password:</p>
                    
                    <form method="post" action="/user/delete-account">
                        <div class="mb-3">
                            <label for="delete_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="delete_password" 
                                   name="password" required>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Delete Account
                            </button>
                        </div>
                    </form>
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
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 