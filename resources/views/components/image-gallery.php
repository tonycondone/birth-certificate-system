
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
        <?php
/**
 * System Features & Security Component
 * Displays a comprehensive overview of system capabilities and security measures
 */
?>

<section class="system-features py-5 bg-gradient-light">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary mb-3">System Features & Security</h2>
            <p class="lead text-muted">Advanced digital infrastructure ensuring secure, efficient, and trustworthy birth certificate management</p>
        </div>

        <!-- Features Grid -->
        <div class="row g-4">
            <!-- Digital Security & Encryption -->
            <div class="col-lg-6 col-xl-3">
                <div class="feature-card h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Digital Security</h4>
                    <p class="text-muted mb-3">Enterprise-grade encryption protocols protect all sensitive data with military-standard security measures.</p>
                    <ul class="feature-list">
                        <li>256-bit AES encryption</li>
                        <li>Secure SSL/TLS connections</li>
                        <li>Multi-factor authentication</li>
                        <li>Real-time threat monitoring</li>
                    </ul>
                </div>
            </div>

            <!-- Verification Process -->
            <div class="col-lg-6 col-xl-3">
                <div class="feature-card h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Multi-Step Verification</h4>
                    <p class="text-muted mb-3">Comprehensive verification process involving multiple stakeholders to ensure document authenticity.</p>
                    <ul class="feature-list">
                        <li>Hospital record verification</li>
                        <li>Government registrar approval</li>
                        <li>Digital signature validation</li>
                        <li>Blockchain-based verification</li>
                    </ul>
                </div>
            </div>

            <!-- Official Documents -->
            <div class="col-lg-6 col-xl-3">
                <div class="feature-card h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-certificate fa-2x text-warning"></i>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Official Documents</h4>
                    <p class="text-muted mb-3">Government-approved digital certificates with advanced security features and legal recognition.</p>
                    <ul class="feature-list">
                        <li>Legally binding certificates</li>
                        <li>QR code verification</li>
                        <li>Digital watermarking</li>
                        <li>Tamper-evident design</li>
                    </ul>
                </div>
            </div>

            <!-- Family Registration -->
            <div class="col-lg-6 col-xl-3">
                <div class="feature-card h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                    <h4 class="h5 fw-bold mb-3">Family Registration</h4>
                    <p class="text-muted mb-3">Streamlined family birth registration process with user-friendly interface and comprehensive support.</p>
                    <ul class="feature-list">
                        <li>Simple online registration</li>
                        <li>Document upload support</li>
                        <li>Progress tracking</li>
                        <li>24/7 customer support</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Security Assurance Section -->
        <div class="security-assurance mt-5 pt-5 border-top">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="h4 fw-bold mb-4">Security Assurance</h3>
                    <div class="security-features">
                        <div class="security-item d-flex align-items-center mb-3">
                            <i class="fas fa-lock text-success me-3"></i>
                            <span>End-to-end data encryption</span>
                        </div>
                        <div class="security-item d-flex align-items-center mb-3">
                            <i class="fas fa-user-shield text-primary me-3"></i>
                            <span>Role-based access control</span>
                        </div>
                        <div class="security-item d-flex align-items-center mb-3">
                            <i class="fas fa-history text-info me-3"></i>
                            <span>Complete audit trail</span>
                        </div>
                        <div class="security-item d-flex align-items-center mb-3">
                            <i class="fas fa-server text-warning me-3"></i>
                            <span>Secure cloud infrastructure</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="trust-badges text-center">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="badge-card">
                                    <i class="fas fa-award fa-2x text-primary mb-2"></i>
                                    <h6 class="fw-bold">ISO 27001</h6>
                                    <small class="text-muted">Certified</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="badge-card">
                                    <i class="fas fa-shield-check fa-2x text-success mb-2"></i>
                                    <h6 class="fw-bold">GDPR</h6>
                                    <small class="text-muted">Compliant</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.system-features {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.feature-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-success));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.feature-card:hover::before {
    opacity: 1;
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(25, 135, 84, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    padding: 0.25rem 0;
    position: relative;
    padding-left: 1.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.feature-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--bs-success);
    font-weight: bold;
}

.security-item {
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    border-left: 4px solid var(--bs-primary);
}

.badge-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
    .feature-card {
        padding: 1.5rem;
    }
    
    .security-assurance {
        text-align: center;
    }
}
</style> 