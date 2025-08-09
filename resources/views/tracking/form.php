<?php
$pageTitle = 'Track Application - Digital Birth Certificate System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .tracking-form {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .tracking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .tracking-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="tracking-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="tracking-card text-center">
                        <div class="tracking-icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h2 class="mb-4">Track Your Application</h2>
                        <p class="text-muted mb-4">Enter your application number to track the status of your birth certificate application.</p>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="/track/search" method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="tracking_number" class="form-label">Application Number</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="tracking_number" 
                                       name="tracking_number" 
                                       placeholder="Enter your application number (e.g., APP2025080339)"
                                       value="<?= htmlspecialchars($trackingNumber ?? '') ?>"
                                       required>
                                <div class="form-text">
                                    Your application number was provided when you submitted your application.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa fa-search me-2"></i>Track Application
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="fa fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                        
                        <div class="mt-4 pt-4 border-top">
                            <h6>Need Help?</h6>
                            <p class="small text-muted mb-2">
                                If you can't find your application number, please check:
                            </p>
                            <ul class="small text-muted text-start">
                                <li>Your email confirmation</li>
                                <li>SMS notifications</li>
                                <li>Contact our support team</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 