<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Birth Certificate - Digital Birth Certificate System</title>
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
                <a class="nav-link" href="/auth/logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Birth Certificate Application
                        </h3>
                    </div>
                    <div class="card-body">
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
                            <!-- Child Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-baby me-2"></i>Child Information
                                    </h5>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="child_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Child's Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="child_name" name="child_name" 
                                           value="<?= htmlspecialchars($_POST['child_name'] ?? '') ?>" required>
                                    <div class="invalid-feedback">Please enter the child's full name.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Date of Birth *
                                    </label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>" required>
                                    <div class="invalid-feedback">Please select the date of birth.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars me-1"></i>Gender *
                                    </label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($_POST['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select the gender.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="place_of_birth" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Place of Birth *
                                    </label>
                                    <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" 
                                           value="<?= htmlspecialchars($_POST['place_of_birth'] ?? '') ?>" 
                                           placeholder="City, State/Province" required>
                                    <div class="invalid-feedback">Please enter the place of birth.</div>
                                </div>
                            </div>

                            <!-- Parent Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-users me-2"></i>Parent Information
                                    </h5>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="mother_name" class="form-label">
                                        <i class="fas fa-female me-1"></i>Mother's Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="mother_name" name="mother_name" 
                                           value="<?= htmlspecialchars($_POST['mother_name'] ?? '') ?>" required>
                                    <div class="invalid-feedback">Please enter the mother's full name.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="father_name" class="form-label">
                                        <i class="fas fa-male me-1"></i>Father's Full Name
                                    </label>
                                    <input type="text" class="form-control" id="father_name" name="father_name" 
                                           value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>">
                                    <div class="form-text">Optional</div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-address-book me-2"></i>Contact Information
                                    </h5>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="parent_email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Parent's Email
                                    </label>
                                    <input type="email" class="form-control" id="parent_email" name="parent_email" 
                                           value="<?= htmlspecialchars($_POST['parent_email'] ?? '') ?>">
                                    <div class="form-text">For notifications about application status</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="parent_phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Parent's Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="parent_phone" name="parent_phone" 
                                           value="<?= htmlspecialchars($_POST['parent_phone'] ?? '') ?>">
                                    <div class="form-text">For urgent communications</div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-home me-1"></i>Current Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="Enter full address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                    <div class="form-text">Current residential address</div>
                                </div>
                            </div>

                            <!-- Declaration -->
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Declaration</h6>
                                <p class="mb-0">
                                    I hereby declare that all the information provided in this application is true and accurate to the best of my knowledge. 
                                    I understand that providing false information may result in legal consequences.
                                </p>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/certificates" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </form>
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
        
        // Date validation - ensure date is not in the future
        document.getElementById('date_of_birth').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            if (selectedDate > today) {
                this.setCustomValidity('Date of birth cannot be in the future');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Phone number formatting
        document.getElementById('parent_phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            this.value = value;
        });
    </script>
</body>
</html> 