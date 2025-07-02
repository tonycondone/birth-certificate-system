
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
$pageTitle = 'Contact Us - Digital Birth Certificate System';
require_once __DIR__ . '/layouts/base.php';
?>

<!-- Contact Form Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Contact Us
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Have questions about our birth certificate system? Need technical support? 
                            We're here to help! Fill out the form below and we'll get back to you within 24 hours.
                        </p>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['success'] ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error'] ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form id="contactForm" method="POST" action="/contact" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            
                            <!-- Name Field -->
        <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name
                                </label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" 
                                       name="name" 
                                       placeholder="Enter your full name"
                                       required 
                                       minlength="2" 
                                       maxlength="100"
                                       value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['name'] ?>
                                    </div>
                                <?php endif; ?>
        </div>

                            <!-- Email Field -->
        <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" 
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Enter your email address"
                                       required
                                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['email'] ?>
                                    </div>
                                <?php endif; ?>
        </div>

                            <!-- Subject Field -->
        <div class="mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Subject
                                </label>
                                <select class="form-select <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" id="subject" name="subject" required>
                                    <option value="">Choose a subject...</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Application Help">Application Help</option>
                                    <option value="Certificate Verification">Certificate Verification</option>
                                    <option value="Account Issues">Account Issues</option>
                                    <option value="Other">Other</option>
                                </select>
                                <?php if (isset($errors['subject'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['subject'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Message Field -->
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Message
                                </label>
                                <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" 
                                          id="message" 
                                          name="message" 
                                          rows="5" 
                                          placeholder="Please describe your inquiry in detail..."
                                          required 
                                          minlength="10" 
                                          maxlength="1000">
                                    <?= htmlspecialchars($old['message'] ?? '') ?>
                                </textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['message'] ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    <span id="charCount">0</span> / 1000 characters
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Message
                                </button>
                            </div>
                        </form>

                        <!-- Contact Information -->
                        <div class="mt-5 pt-4 border-top">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Other Ways to Reach Us
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-phone text-primary me-3 fa-lg"></i>
                                        <div>
                                            <strong>Phone Support</strong><br>
                                            <span class="text-muted">+1 (555) 123-4567</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-primary me-3 fa-lg"></i>
                                        <div>
                                            <strong>Support Hours</strong><br>
                                            <span class="text-muted">Mon-Fri: 8AM-6PM EST</span>
                                        </div>
                                    </div>
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
.contact-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    // Character counter for message
    messageTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        
        // Change color based on length
        if (length > 800) {
            charCount.style.color = '#dc3545';
        } else if (length > 600) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = '#6c757d';
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        submitBtn.disabled = true;
        
        // Re-enable after 3 seconds (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
        
        form.classList.add('was-validated');
    });
    
    // Auto-resize textarea
    messageTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
</script>

<?php include __DIR__.'/layouts/footer.php'; ?> 