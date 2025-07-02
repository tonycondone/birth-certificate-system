<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/login">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a class="nav-link" href="/register">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">
                <i class="fas fa-certificate me-3"></i>
                Digital Birth Certificate System
            </h1>
            <p class="lead mb-5">
                Secure, efficient, and modern birth certificate management for hospitals, 
                government registrars, and families.
            </p>
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="/register" class="btn btn-light btn-lg px-4 me-sm-3">
                    <i class="fas fa-user-plus me-2"></i>Get Started
                </a>
                <a href="/verify" class="btn btn-outline-light btn-lg px-4">
                    <i class="fas fa-search me-2"></i>Verify Certificate
                </a>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold mb-3">Why Choose Our System?</h2>
                    <p class="lead text-muted">
                        Experience the future of birth certificate management with our 
                        comprehensive digital solution.
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Secure & Reliable</h5>
                            <p class="card-text">
                                Advanced encryption and blockchain technology ensure 
                                the security and authenticity of every certificate.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-bolt fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Fast & Efficient</h5>
                            <p class="card-text">
                                Streamlined processes reduce application time from 
                                weeks to minutes with instant verification.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Mobile Friendly</h5>
                            <p class="card-text">
                                Access the system from any device with our 
                                responsive design and mobile-optimized interface.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">For All Users</h2>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hospital fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1">Hospitals</h6>
                                    <small class="text-muted">Issue certificates instantly</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building fa-2x text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Government</h6>
                                    <small class="text-muted">Manage and verify records</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users fa-2x text-info me-3"></i>
                                <div>
                                    <h6 class="mb-1">Families</h6>
                                    <small class="text-muted">Easy application process</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search fa-2x text-warning me-3"></i>
                                <div>
                                    <h6 class="mb-1">Verification</h6>
                                    <small class="text-muted">Instant authenticity check</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="/images/gettyimages-78453892-612x612.jpg" 
                         alt="Birth Certificate" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">
                &copy; 2024 Digital Birth Certificate System. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 