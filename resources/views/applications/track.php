<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Track Application' ?> - Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .progress-timeline {
            position: relative;
            padding-left: 30px;
        }
        .progress-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .progress-step {
            position: relative;
            margin-bottom: 30px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .progress-step::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 25px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e9ecef;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e9ecef;
        }
        .progress-step.completed::before {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }
        .progress-step.current::before {
            background: #007bff;
            box-shadow: 0 0 0 2px #007bff;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 2px #007bff; }
            50% { box-shadow: 0 0 0 6px rgba(0,123,255,0.3); }
            100% { box-shadow: 0 0 0 2px #007bff; }
        }
        .tracking-form {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .application-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .estimated-time {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-certificate me-2"></i>Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="/verify">
                    <i class="fas fa-search me-1"></i>Verify Certificate
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a class="nav-link" href="/auth/logout">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="/login">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="tracking-container">
            <!-- Header -->
            <div class="text-center mb-4">
                <h1 class="display-5">
                    <i class="fas fa-search me-3"></i>Track Your Application
                </h1>
                <p class="lead text-muted">
                    Enter your tracking number to see the current status of your birth certificate application
                </p>
            </div>

            <!-- Tracking Form -->
            <div class="tracking-form">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <label for="tracking_number" class="form-label">
                            <i class="fas fa-hashtag me-2"></i>Tracking Number
                        </label>
                        <input type="text" class="form-control form-control-lg" 
                               id="tracking_number" name="tracking_number" 
                               value="<?= htmlspecialchars($trackingNumber) ?>"
                               placeholder="e.g., TRK1234567890ABC" required>
                        <div class="form-text text-white-50">
                            Your tracking number was provided when you submitted your application
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-light btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Track Application
                        </button>
                    </div>
                </form>
            </div>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Application Information -->
            <?php if (isset($application) && $application): ?>
                <div class="application-info">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3">
                                <i class="fas fa-file-alt me-2 text-primary"></i>
                                Application Details
                            </h4>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <strong>Application Number:</strong><br>
                                    <span class="font-monospace"><?= htmlspecialchars($application['application_number']) ?></span>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <strong>Tracking Number:</strong><br>
                                    <span class="font-monospace"><?= htmlspecialchars($application['tracking_number']) ?></span>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <strong>Child Name:</strong><br>
                                    <?= htmlspecialchars($application['child_name']) ?>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <strong>Date of Birth:</strong><br>
                                    <?= date('F j, Y', strtotime($application['date_of_birth'])) ?>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <strong>Submitted:</strong><br>
                                    <?= date('F j, Y \a\t g:i A', strtotime($application['submitted_at'])) ?>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <strong>Current Status:</strong><br>
                                    <?php
                                    $statusColors = [
                                        'submitted' => 'bg-info',
                                        'under_review' => 'bg-warning',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'certificate_issued' => 'bg-success'
                                    ];
                                    $statusColor = $statusColors[$application['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $statusColor ?> status-badge">
                                        <?= ucwords(str_replace('_', ' ', $application['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="estimated-time">
                                <h6><i class="fas fa-clock me-2"></i>Estimated Processing Time</h6>
                                <div class="h5 text-primary mb-0">7-14 Business Days</div>
                                <small class="text-muted">From submission date</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Timeline -->
                <?php if (!empty($progress)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-route me-2"></i>
                                Application Progress
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="progress-timeline">
                                <?php foreach ($progress as $index => $step): ?>
                                    <?php
                                    $isCompleted = $step['completed'] ?? false;
                                    $isCurrent = !$isCompleted && $index === count($progress) - 1;
                                    $stepClass = $isCompleted ? 'completed' : ($isCurrent ? 'current' : '');
                                    ?>
                                    <div class="progress-step <?= $stepClass ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <?php if ($isCompleted): ?>
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php elseif ($isCurrent): ?>
                                                        <i class="fas fa-clock text-primary me-2"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-circle text-muted me-2"></i>
                                                    <?php endif; ?>
                                                    <?= ucwords(str_replace('_', ' ', $step['status'])) ?>
                                                </h6>
                                                <p class="mb-0 text-muted"><?= htmlspecialchars($step['description']) ?></p>
                                                <?php if ($isCurrent): ?>
                                                    <small class="text-primary">
                                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                                        Currently in progress...
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($step['created_at'])) ?><br>
                                                    <?= date('g:i A', strtotime($step['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                What Happens Next?
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($application['status'] === 'submitted'): ?>
                                <p>Your application is currently being reviewed by our team. We will verify all the information and documents provided.</p>
                                <ul class="mb-0">
                                    <li>Initial document review (1-2 business days)</li>
                                    <li>Hospital verification (2-3 business days)</li>
                                    <li>Registrar approval (3-5 business days)</li>
                                    <li>Certificate generation and delivery (1-2 business days)</li>
                                </ul>
                            <?php elseif ($application['status'] === 'under_review'): ?>
                                <p>Your application is currently under detailed review. Our registrar is verifying all information.</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You will be notified via email once the review is complete.
                                </div>
                            <?php elseif ($application['status'] === 'approved'): ?>
                                <p>Congratulations! Your application has been approved. Your birth certificate is being generated.</p>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You will receive an email with download instructions once the certificate is ready.
                                </div>
                            <?php elseif ($application['status'] === 'certificate_issued'): ?>
                                <p>Your birth certificate has been issued and is ready for download.</p>
                                <div class="alert alert-success">
                                    <i class="fas fa-download me-2"></i>
                                    <strong>Certificate Ready!</strong> You can now download your birth certificate from your dashboard.
                                </div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="/dashboard" class="btn btn-success">
                                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                    </a>
                                <?php endif; ?>
                            <?php elseif ($application['status'] === 'rejected'): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Application Rejected</strong><br>
                                    Your application has been rejected. Please contact our support team for more information.
                                </div>
                                <a href="/contact" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h6><i class="fas fa-headset me-2"></i>Need Help?</h6>
                        <p class="text-muted mb-3">
                            If you have questions about your application or need assistance, 
                            our support team is here to help.
                        </p>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <a href="/contact" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="/faq" class="btn btn-outline-info w-100">
                                    <i class="fas fa-question-circle me-2"></i>View FAQs
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="tel:+1-800-BIRTH-CERT" class="btn btn-outline-success w-100">
                                    <i class="fas fa-phone me-2"></i>Call Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- How to Track Section -->
            <?php if (!isset($application) || !$application): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>
                            How to Track Your Application
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-search me-2"></i>Finding Your Tracking Number</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Check your email confirmation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Look at your application receipt</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Log into your account dashboard</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Contact our support team</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle me-2"></i>Tracking Number Format</h6>
                                <p class="text-muted">
                                    Tracking numbers start with "TRK" followed by numbers and letters.
                                    Example: <code>TRK1234567890ABC</code>
                                </p>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-lightbulb me-2"></i>
                                        <strong>Tip:</strong> Tracking numbers are case-sensitive. 
                                        Make sure to enter them exactly as shown.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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
        // Auto-focus on tracking number input
        document.getElementById('tracking_number').focus();
        
        // Format tracking number input
        document.getElementById('tracking_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Auto-refresh for applications in progress
        <?php if (isset($application) && in_array($application['status'], ['submitted', 'under_review'])): ?>
            setTimeout(function() {
                const refreshBanner = document.createElement('div');
                refreshBanner.className = 'alert alert-info alert-dismissible fade show mt-3';
                refreshBanner.innerHTML = `
                    <i class="fas fa-sync-alt me-2"></i>
                    <strong>Auto-refresh available:</strong> 
                    <a href="javascript:location.reload()" class="alert-link">Click here to refresh</a> 
                    for the latest status updates.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.tracking-container').appendChild(refreshBanner);
            }, 30000); // Show after 30 seconds
        <?php endif; ?>
    </script>
</body>
</html>
