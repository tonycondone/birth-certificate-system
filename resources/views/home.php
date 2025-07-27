<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('/images/gettyimages-82842381-612x612.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            position: relative;
            min-height: 600px;
            display: flex;
            align-items: center;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            background-color: var(--accent-color);
            color: white;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            display: inline-block;
            margin-bottom: 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card .card-body {
            padding: 2rem;
        }
        
        .feature-icon {
            height: 80px;
            width: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .stats-section {
            background-color: var(--light-color);
            padding: 80px 0;
        }
        
        .stat-card {
            padding: 2rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .step-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
        }
        
        .step-circle::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -50%;
            height: 2px;
            width: 100%;
            background-color: #e0e0e0;
            z-index: -1;
        }
        
        .step-item:last-child .step-circle::after {
            display: none;
        }
        
        .testimonial-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 80px 0;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-outline-primary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .text-primary {
            color: var(--secondary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--secondary-color) !important;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 3rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-certificate me-2"></i>
                Birth Certificate System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                <a class="nav-link" href="/verify">
                    <i class="fas fa-search me-1"></i>Verify Certificate
                </a>
                    </li>
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                        <li class="nav-item">
                    <a class="nav-link" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                        </li>
                        <li class="nav-item">
                    <a class="nav-link" href="/profile">
                        <i class="fas fa-user me-1"></i>Profile
                    </a>
                        </li>
                        <li class="nav-item">
                    <a class="nav-link" href="/auth/logout">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                        </li>
                <?php else: ?>
                        <li class="nav-item">
                    <a class="nav-link" href="/login">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                        </li>
                        <li class="nav-item">
                    <a class="nav-link" href="/register">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                        </li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- GOD TIER Payment Notification Banner -->
    <?php include __DIR__ . '/partials/payment-notification-banner.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 hero-content">
                    <div class="hero-badge">Official Government Service</div>
                    <h1 class="display-3 fw-bold mb-4">Digital Birth Certificate System</h1>
                    <p class="lead mb-4 fs-4">
                        <?= htmlspecialchars($welcomeMessage ?? 'Secure, efficient, and accessible birth registration for all citizens. Your gateway to official identity documentation.') ?>
                    </p>
                    <p class="mb-5">
                        Our modern digital platform simplifies the birth registration process, ensuring every child's right to an identity is protected and accessible to all families.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                            <a href="/dashboard" class="btn btn-primary btn-lg px-4 py-3">
                                <i class="fas fa-tachometer-alt me-2"></i>Access Dashboard
                    </a>
                <?php else: ?>
                            <a href="/register" class="btn btn-primary btn-lg px-4 py-3">
                                <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                <?php endif; ?>
                        <a href="/verify" class="btn btn-outline-light btn-lg px-4 py-3">
                    <i class="fas fa-search me-2"></i>Verify Certificate
                </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 my-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title display-5 fw-bold">Why Choose Our System?</h2>
                <p class="lead text-muted">A modern approach to birth registration and certificate management</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Secure & Reliable</h4>
                            <p class="text-muted">
                                Advanced encryption and blockchain technology ensure your data is protected. 
                                Our system meets the highest international security standards for government documentation.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-success">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Fast Processing</h4>
                            <p class="text-muted">
                                Streamlined application process reduces processing time from weeks to days.
                                Real-time tracking and automated notifications keep you informed at every step.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-info">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Accessible Anywhere</h4>
                            <p class="text-muted">
                                Access your certificates anytime, anywhere with our responsive 
                                platform that works on all devices, bringing government services to your fingertips.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mt-3">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-warning">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Tamper-Proof</h4>
                            <p class="text-muted">
                                Each certificate includes unique security features and digital signatures
                                that can be verified online, preventing fraud and ensuring authenticity.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-danger">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Internationally Recognized</h4>
                            <p class="text-muted">
                                Our digital certificates comply with international standards and are
                                recognized by foreign governments, embassies, and global institutions.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon" style="background-color: #8e44ad;">
                                <i class="fas fa-universal-access"></i>
                            </div>
                            <h4 class="mt-4 mb-3">Inclusive Design</h4>
                            <p class="text-muted">
                                Our system is designed to be accessible to all citizens, including those
                                with disabilities, ensuring everyone can access vital registration services.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title display-5 fw-bold">Our Impact</h2>
                <p class="lead text-muted">Making a difference in citizens' lives through digital transformation</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card stat-card h-100 bg-white">
                        <div class="card-body text-center">
                            <i class="fas fa-certificate fa-3x text-primary mb-3"></i>
                            <h2 class="display-5 fw-bold text-primary"><?= number_format(is_numeric($statistics['approved_certificates'] ?? null) ? $statistics['approved_certificates'] : 10000) ?></h2>
                            <p class="text-muted text-uppercase fw-bold">Certificates Issued</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card h-100 bg-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h2 class="display-5 fw-bold text-success"><?= number_format(is_numeric($statistics['total_users'] ?? null) ? $statistics['total_users'] : 5000) ?></h2>
                            <p class="text-muted text-uppercase fw-bold">Registered Users</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card h-100 bg-white">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x text-warning mb-3"></i>
                            <h2 class="display-5 fw-bold text-warning"><?= number_format(is_numeric($statistics['total_applications'] ?? null) ? $statistics['total_applications'] : 15000) ?></h2>
                            <p class="text-muted text-uppercase fw-bold">Total Applications</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card h-100 bg-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-3x text-info mb-3"></i>
                            <h2 class="display-5 fw-bold text-info"><?= number_format(is_numeric($statistics['pending_applications'] ?? null) ? $statistics['pending_applications'] : 0) ?></h2>
                            <p class="text-muted text-uppercase fw-bold">Pending Applications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 my-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title display-5 fw-bold">How It Works</h2>
                <p class="lead text-muted">Four simple steps to obtain your official birth certificate</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3 text-center step-item">
                    <div class="step-circle bg-primary text-white">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                    <h4 class="mt-4">1. Register</h4>
                    <p class="text-muted">Create your secure account with basic information and identity verification</p>
                </div>
                
                <div class="col-md-3 text-center step-item">
                    <div class="step-circle bg-success text-white">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h4 class="mt-4">2. Apply</h4>
                    <p class="text-muted">Complete the digital application form and upload supporting documents</p>
                </div>
                
                <div class="col-md-3 text-center step-item">
                    <div class="step-circle bg-warning text-white">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="mt-4">3. Verification</h4>
                    <p class="text-muted">Our officials verify your information with hospital and government records</p>
                </div>
                
                <div class="col-md-3 text-center step-item">
                    <div class="step-circle bg-info text-white">
                        <i class="fas fa-download fa-2x"></i>
                    </div>
                    <h4 class="mt-4">4. Receive</h4>
                    <p class="text-muted">Download your digital certificate or request physical copies by mail</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title display-5 fw-bold">What People Are Saying</h2>
                <p class="lead text-muted">Feedback from citizens who have used our digital services</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-3">"I was amazed at how quick and easy the process was. What used to take weeks now took just days. The digital certificate was accepted everywhere!"</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">SM</span>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Sarah M.</h6>
                                    <small class="text-muted">New Parent</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-3">"As a hospital administrator, this system has revolutionized our workflow. Registration is now seamless and we can focus more on patient care."</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">JT</span>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Dr. James T.</h6>
                                    <small class="text-muted">Hospital Director</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            </div>
                            <p class="mb-3">"Living abroad, I needed my child's birth certificate urgently. This system allowed me to apply online and receive a verified digital copy within days!"</p>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">RK</span>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Robert K.</h6>
                                    <small class="text-muted">Expatriate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4 fs-5">Join thousands of citizens who have simplified their birth certificate process.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/register" class="btn btn-light btn-lg px-5 py-3">
                <i class="fas fa-rocket me-2"></i>Start Your Application
            </a>
                <a href="/verify" class="btn btn-outline-light btn-lg px-5 py-3">
                    <i class="fas fa-search me-2"></i>Verify a Certificate
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-certificate me-2"></i>Birth Certificate System</h5>
                    <p class="text-muted mt-3">Secure, efficient, and modern birth certificate management for all citizens. An official government service.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-light"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/" class="text-muted">Home</a></li>
                        <li class="mb-2"><a href="/about" class="text-muted">About Us</a></li>
                        <li class="mb-2"><a href="/faq" class="text-muted">FAQs</a></li>
                        <li class="mb-2"><a href="/contact" class="text-muted">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="mb-3">Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/register" class="text-muted">Registration</a></li>
                        <li class="mb-2"><a href="/verify" class="text-muted">Certificate Verification</a></li>
                        <li class="mb-2"><a href="/track" class="text-muted">Application Tracking</a></li>
                        <li class="mb-2"><a href="/support" class="text-muted">Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Contact Information</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Government Plaza, Capital City</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@birthcertificate.gov</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Mon-Fri: 8:00 AM - 5:00 PM</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2023 Digital Birth Certificate System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/privacy" class="text-muted me-3">Privacy Policy</a>
                    <a href="/terms" class="text-muted me-3">Terms of Service</a>
                    <a href="/accessibility" class="text-muted">Accessibility</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
