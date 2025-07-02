<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Birth Certificate - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .verification-result {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .valid-certificate {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .invalid-certificate {
            border-color: #dc3545;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }
        .certificate-number {
            font-family: 'Courier New', monospace;
            font-size: 1.2em;
            font-weight: bold;
            color: #495057;
        }
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
        
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="/login">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h1 class="display-5">
                        <i class="fas fa-search me-3"></i>Verify Birth Certificate
                    </h1>
                    <p class="lead text-muted">
                        Enter the certificate number to verify its authenticity and view certificate details
                    </p>
                </div>

                <!-- Verification Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-8">
                                <label for="certificate_number" class="form-label">
                                    <i class="fas fa-hashtag me-2"></i>Certificate Number
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="certificate_number" name="certificate_number" 
                                       value="<?= htmlspecialchars($_GET['certificate_number'] ?? '') ?>"
                                       placeholder="e.g., BC2024001ABC123" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Verify
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Verification Result -->
                <?php if (isset($certificate)): ?>
                    <?php if ($certificate['is_valid']): ?>
                        <div class="verification-result valid-certificate">
                            <div class="text-center mb-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h3 class="text-success">Certificate Verified</h3>
                                <p class="text-muted">This is a valid birth certificate issued by our system.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-baby me-2"></i>Child Information
                                    </h5>
                                    <div class="mb-3">
                                        <strong>Name:</strong><br>
                                        <?= htmlspecialchars($certificate['child_name']) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Date of Birth:</strong><br>
                                        <?= date('F j, Y', strtotime($certificate['date_of_birth'])) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Gender:</strong><br>
                                        <?= ucfirst($certificate['gender']) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Place of Birth:</strong><br>
                                        <?= htmlspecialchars($certificate['place_of_birth']) ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-users me-2"></i>Parent Information
                                    </h5>
                                    <div class="mb-3">
                                        <strong>Mother's Name:</strong><br>
                                        <?= htmlspecialchars($certificate['mother_name']) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Father's Name:</strong><br>
                                        <?= htmlspecialchars($certificate['father_name'] ?: 'Not specified') ?>
                                    </div>
                                    
                                    <h5 class="border-bottom pb-2 mt-4">
                                        <i class="fas fa-certificate me-2"></i>Certificate Details
                                    </h5>
                                    <div class="mb-3">
                                        <strong>Certificate Number:</strong><br>
                                        <span class="certificate-number"><?= htmlspecialchars($certificate['certificate_number']) ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Issue Date:</strong><br>
                                        <?= date('F j, Y', strtotime($certificate['issued_at'])) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Status:</strong><br>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Verification Details:</strong> This certificate was verified on 
                                    <?= date('F j, Y \a\t g:i A') ?> and is currently active in our system.
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="verification-result invalid-certificate">
                            <div class="text-center">
                                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                <h3 class="text-danger">Certificate Not Found</h3>
                                <p class="text-muted">
                                    The certificate number "<?= htmlspecialchars($_GET['certificate_number']) ?>" 
                                    was not found in our system.
                                </p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Possible reasons:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>The certificate number may be incorrect</li>
                                        <li>The certificate may not have been issued yet</li>
                                        <li>The certificate may have been revoked or expired</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif (isset($_GET['certificate_number']) && empty($_GET['certificate_number'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Please enter a certificate number to verify.
                    </div>
                <?php endif; ?>

                <!-- Information Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>About Certificate Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-shield-alt me-2"></i>Security Features</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Unique certificate numbers</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Digital verification system</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Real-time status checking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tamper-proof records</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-question-circle me-2"></i>How to Use</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Enter the certificate number</li>
                                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Click the verify button</li>
                                    <li><i class="fas fa-arrow-right text-primary me-2"></i>View verification results</li>
                                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Download or print if needed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h6><i class="fas fa-headset me-2"></i>Need Help?</h6>
                        <p class="text-muted mb-0">
                            If you're having trouble verifying a certificate or need assistance, 
                            please contact our support team at 
                            <a href="mailto:support@birthcertificate.gov">support@birthcertificate.gov</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-certificate me-2"></i>
                Digital Birth Certificate System - Secure Verification Portal
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on certificate number input
        document.getElementById('certificate_number').focus();
        
        // Certificate number formatting
        document.getElementById('certificate_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>