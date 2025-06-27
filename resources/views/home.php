<?php
$pageTitle = 'Digital Birth Certificate System - Secure Birth Registration';
require_once __DIR__ . '/layouts/base.php';
?>

<!-- Hero Section -->
<section class="hero bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Secure Digital Birth Registration</h1>
                <p class="lead mb-4">Register births, obtain certificates, and verify documents - all in one secure platform.</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="/register" class="btn btn-light btn-lg px-4 me-md-2">Get Started</a>
                    <a href="/verify" class="btn btn-outline-light btn-lg px-4">Verify Certificate</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="/images/hero-image.svg" alt="Digital Birth Certificate" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Digital Registration?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h3 class="h5">Secure & Reliable</h3>
                        <p class="card-text">Advanced encryption and blockchain technology ensure document authenticity.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h3 class="h5">Fast Processing</h3>
                        <p class="card-text">Quick turnaround time with automated verification and processing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                        <h3 class="h5">24/7 Access</h3>
                        <p class="card-text">Access your certificates and track applications anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Verification Widget -->
<section class="verify-section bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Verify Certificate</h2>
                        <form id="verifyForm" action="/verify" method="GET" class="needs-validation" novalidate>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-lg" 
                                       placeholder="Enter Certificate Number" 
                                       name="certificate_number" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-2"></i>Verify
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="display-4 fw-bold text-primary mb-2" data-count="50000">50,000+</div>
                <p class="lead">Certificates Issued</p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="display-4 fw-bold text-primary mb-2" data-count="1000">1,000+</div>
                <p class="lead">Registered Hospitals</p>
            </div>
            <div class="col-md-4">
                <div class="display-4 fw-bold text-primary mb-2" data-count="99">99%</div>
                <p class="lead">Satisfaction Rate</p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="process-step">
                    <div class="circle bg-primary text-white mb-3">1</div>
                    <h3 class="h5">Register</h3>
                    <p>Create your account as a parent or hospital</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="process-step">
                    <div class="circle bg-primary text-white mb-3">2</div>
                    <h3 class="h5">Submit Details</h3>
                    <p>Provide birth information and required documents</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="process-step">
                    <div class="circle bg-primary text-white mb-3">3</div>
                    <h3 class="h5">Verification</h3>
                    <p>Hospital verifies birth details</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="process-step">
                    <div class="circle bg-primary text-white mb-3">4</div>
                    <h3 class="h5">Certificate</h3>
                    <p>Receive your digital birth certificate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card bg-primary text-white border-0">
                    <div class="card-body p-5 text-center">
                        <h2 class="mb-4">Ready to Get Started?</h2>
                        <p class="lead mb-4">Join thousands of parents who have already registered their children's births digitally.</p>
                        <a href="/register" class="btn btn-light btn-lg">Register Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto;
}

.process-step {
    position: relative;
}

.process-step::after {
    content: '';
    position: absolute;
    top: 30px;
    right: -50%;
    width: 100%;
    border-top: 2px dashed #dee2e6;
    z-index: 0;
}

.process-step:last-child::after {
    display: none;
}

@media (max-width: 768px) {
    .process-step::after {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate statistics when in view
    const stats = document.querySelectorAll('[data-count]');
    let animated = false;

    function animateStats() {
        if (animated) return;
        
        const windowHeight = window.innerHeight;
        const statsSection = document.querySelector('.stats');
        const statsSectionTop = statsSection.getBoundingClientRect().top;

        if (statsSectionTop < windowHeight * 0.75) {
            stats.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-count'));
                let current = 0;
                const increment = target / 50;
                const duration = 2000;
                const interval = duration / 50;

                const counter = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        stat.textContent = target.toLocaleString() + (target === 99 ? '%' : '+');
                        clearInterval(counter);
                    } else {
                        stat.textContent = Math.floor(current).toLocaleString() + (target === 99 ? '%' : '+');
                    }
                }, interval);
            });
            animated = true;
        }
    }

    window.addEventListener('scroll', animateStats);
    animateStats(); // Check on load
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>