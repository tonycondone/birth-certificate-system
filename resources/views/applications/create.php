<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'New Application' ?> - Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .required {
            color: #dc3545;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            margin: 0 5px;
            border-radius: 5px;
            position: relative;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-certificate me-2"></i>Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="/applications">
                    <i class="fas fa-list me-1"></i>My Applications
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['email'] ?? 'User') ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="dropdown-item" href="/auth/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>
                            New Birth Certificate Application
                        </h1>
                        <p class="text-muted mb-0">Complete the form below to apply for a birth certificate</p>
                    </div>
                    <a href="/applications" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Applications
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Application Form -->
        <form method="POST" id="applicationForm" enctype="multipart/form-data">
            <!-- Child Information Section -->
            <div class="form-section">
                <h4 class="mb-3">
                    <i class="fas fa-baby me-2 text-primary"></i>
                    Child Information
                </h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="child_first_name" class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="child_first_name" name="child_first_name" 
                               value="<?= htmlspecialchars($_POST['child_first_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="child_middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="child_middle_name" name="child_middle_name"
                               value="<?= htmlspecialchars($_POST['child_middle_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="child_last_name" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="child_last_name" name="child_last_name"
                               value="<?= htmlspecialchars($_POST['child_last_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_of_birth" class="form-label">
                            Date of Birth <span class="required">*</span>
                        </label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                               value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="time_of_birth" class="form-label">Time of Birth</label>
                        <input type="time" class="form-control" id="time_of_birth" name="time_of_birth"
                               value="<?= htmlspecialchars($_POST['time_of_birth'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="gender" class="form-label">
                            Gender <span class="required">*</span>
                        </label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($_POST['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="place_of_birth" class="form-label">
                            Place of Birth <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="place_of_birth" name="place_of_birth"
                               placeholder="City, State/Province, Country"
                               value="<?= htmlspecialchars($_POST['place_of_birth'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="weight_at_birth" class="form-label">Weight at Birth (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="weight_at_birth" name="weight_at_birth"
                               value="<?= htmlspecialchars($_POST['weight_at_birth'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="length_at_birth" class="form-label">Length at Birth (cm)</label>
                        <input type="number" step="0.1" class="form-control" id="length_at_birth" name="length_at_birth"
                               value="<?= htmlspecialchars($_POST['length_at_birth'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Mother Information Section -->
            <div class="form-section">
                <h4 class="mb-3">
                    <i class="fas fa-female me-2 text-primary"></i>
                    Mother Information
                </h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mother_first_name" class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="mother_first_name" name="mother_first_name"
                               value="<?= htmlspecialchars($_POST['mother_first_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mother_last_name" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="mother_last_name" name="mother_last_name"
                               value="<?= htmlspecialchars($_POST['mother_last_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="mother_national_id" class="form-label">National ID</label>
                        <input type="text" class="form-control" id="mother_national_id" name="mother_national_id"
                               value="<?= htmlspecialchars($_POST['mother_national_id'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mother_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="mother_phone" name="mother_phone"
                               value="<?= htmlspecialchars($_POST['mother_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mother_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="mother_email" name="mother_email"
                               value="<?= htmlspecialchars($_POST['mother_email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Father Information Section -->
            <div class="form-section">
                <h4 class="mb-3">
                    <i class="fas fa-male me-2 text-primary"></i>
                    Father Information
                </h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="father_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="father_first_name" name="father_first_name"
                               value="<?= htmlspecialchars($_POST['father_first_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="father_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="father_last_name" name="father_last_name"
                               value="<?= htmlspecialchars($_POST['father_last_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="father_national_id" class="form-label">National ID</label>
                        <input type="text" class="form-control" id="father_national_id" name="father_national_id"
                               value="<?= htmlspecialchars($_POST['father_national_id'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="father_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="father_phone" name="father_phone"
                               value="<?= htmlspecialchars($_POST['father_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="father_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="father_email" name="father_email"
                               value="<?= htmlspecialchars($_POST['father_email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Hospital Information Section -->
            <div class="form-section">
                <h4 class="mb-3">
                    <i class="fas fa-hospital me-2 text-primary"></i>
                    Hospital/Medical Information
                </h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hospital_name" class="form-label">Hospital/Medical Facility Name</label>
                        <input type="text" class="form-control" id="hospital_name" name="hospital_name"
                               value="<?= htmlspecialchars($_POST['hospital_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="attending_physician" class="form-label">Attending Physician</label>
                        <input type="text" class="form-control" id="attending_physician" name="attending_physician"
                               value="<?= htmlspecialchars($_POST['attending_physician'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="physician_license" class="form-label">Physician License Number</label>
                        <input type="text" class="form-control" id="physician_license" name="physician_license"
                               value="<?= htmlspecialchars($_POST['physician_license'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-section">
                <h4 class="mb-3">
                    <i class="fas fa-file-contract me-2 text-primary"></i>
                    Declaration and Consent
                </h4>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="declaration" name="declaration" required>
                    <label class="form-check-label" for="declaration">
                        <strong>I hereby declare that:</strong>
                        <ul class="mt-2 mb-0">
                            <li>All information provided in this application is true and accurate to the best of my knowledge</li>
                            <li>I understand that providing false information is a criminal offense</li>
                            <li>I consent to the processing of this personal data for the purpose of issuing a birth certificate</li>
                            <li>I understand that this application will be reviewed by authorized personnel</li>
                        </ul>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="history.back()">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 Birth Certificate System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="text-muted me-3">Privacy Policy</a>
                    <a href="/terms" class="text-muted me-3">Terms of Service</a>
                    <a href="/contact" class="text-muted">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const declaration = document.getElementById('declaration');
            if (!declaration.checked) {
                e.preventDefault();
                alert('Please accept the declaration and consent before submitting.');
                declaration.focus();
                return false;
            }
        });

        // Auto-calculate age
        document.getElementById('date_of_birth').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            
            if (age > 1) {
                const ageInfo = document.createElement('small');
                ageInfo.className = 'text-muted';
                ageInfo.textContent = `Age: ${age} years`;
                
                // Remove existing age info
                const existing = this.parentNode.querySelector('.age-info');
                if (existing) existing.remove();
                
                ageInfo.className += ' age-info d-block mt-1';
                this.parentNode.appendChild(ageInfo);
            }
        });

        // Auto-format phone numbers
        document.querySelectorAll('input[type="tel"]').forEach(function(input) {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 10) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                }
                this.value = value;
            });
        });
    </script>
</body>
</html>
