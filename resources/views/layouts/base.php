<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Digital Birth Certificate Registration System">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Digital Birth Certificate System'); ?></title>
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com; font-src 'self' cdn.jsdelivr.net fonts.googleapis.com fonts.gstatic.com">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="/css/app.min.css" as="style">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/app.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    
    <!-- Meta Tags for Social Sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle ?? 'Digital Birth Certificate System'); ?>">
    <meta property="og:description" content="Secure Digital Birth Registration & Certificate Management System">
    <meta property="og:image" content="/images/og-image.svg">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:card" content="summary_large_image">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/images/logo.svg" alt="Birth Certificate System Logo" class="logo-img me-2">
                Birth Certificate System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" data-mobile-toggle>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" data-mobile-menu>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dashboardDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dashboardDropdown">
                                <li><a class="dropdown-item" href="/dashboard">Overview</a></li>
                                <li><a class="dropdown-item" href="/dashboard/pending">Pending Reviews</a></li>
                                <li><a class="dropdown-item" href="/dashboard/approved">Approved Certificates</a></li>
                                <li><a class="dropdown-item" href="/dashboard/reports">Reports</a></li>
                                <li><a class="dropdown-item" href="/dashboard/settings">Settings</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user']['role'] === 'parent'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/applications/new">
                                    <i class="fas fa-plus-circle me-1"></i> New Application
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/applications">
                                    <i class="fas fa-list me-1"></i> My Applications
                                </a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] === 'hospital'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/hospital/submissions">
                                    <i class="fas fa-hospital me-1"></i> Birth Records
                                </a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] === 'registrar'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/registrar/applications">
                                    <i class="fas fa-tasks me-1"></i> Pending Applications
                                </a>
                            </li>
                        <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/users">
                                    <i class="fas fa-users me-1"></i> Users
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/verify">
                            <i class="fas fa-check-circle me-1"></i> Verify Certificate
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/profile">
                                        <i class="fas fa-id-card me-2"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/auth/logout">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
        <?php if (isset($content)) echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/about" class="text-decoration-none">About Us</a></li>
                        <li><a href="/contact" class="text-decoration-none">Contact</a></li>
                        <li><a href="/faq" class="text-decoration-none">FAQ</a></li>
                        <li><a href="/privacy" class="text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> +1234567890</li>
                        <li><i class="fas fa-envelope me-2"></i> support@birthcert.gov</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Government St</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-decoration-none me-2"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-decoration-none me-2"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-decoration-none me-2"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small>&copy; <?php echo date('Y'); ?> Digital Birth Certificate System. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/app.min.js"></script>
</body>
</html>