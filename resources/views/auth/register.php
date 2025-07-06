<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Digital Birth Certificate System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .register-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        .register-left {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .register-right {
            padding: 3rem;
        }

        .brand-logo {
            font-size: 3rem;
            color: #3b82f6;
            margin-bottom: 1rem;
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        .benefits-list li {
            padding: 0.75rem 0;
            display: flex;
            align-items: flex-start;
        }

        .benefits-list li i {
            color: #3b82f6;
            margin-right: 0.75rem;
            margin-top: 0.25rem;
            width: 20px;
        }

        .form-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating > .form-control,
        .form-floating > .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 0.75rem;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .form-floating > label {
            color: #64748b;
            padding: 1rem 0.75rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
            border-radius: 0.75rem;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background-color: #dc2626; width: 25%; }
        .strength-fair { background-color: #d97706; width: 50%; }
        .strength-good { background-color: #059669; width: 75%; }
        .strength-strong { background-color: #047857; width: 100%; }

        .strength-text {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .role-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-card {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .role-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .role-card.selected {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
        }

        .role-card i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            display: block;
        }

        .role-card .role-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .role-card .role-description {
            font-size: 0.875rem;
            color: #64748b;
        }

        .terms-checkbox {
            margin-bottom: 1.5rem;
        }

        .terms-checkbox .form-check-input {
            border-radius: 0.375rem;
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 0.5rem;
            position: relative;
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }

        .step.completed {
            background: var(--success-color);
            color: white;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 40px;
            height: 2px;
            background: #e2e8f0;
            transform: translateY(-50%);
        }

        .step:last-child::after {
            display: none;
        }

        .step.completed::after {
            background: var(--success-color);
        }

        @media (max-width: 768px) {
            .register-container {
                margin: 1rem;
            }
            
            .register-left {
                padding: 2rem;
            }
            
            .register-right {
                padding: 2rem;
            }
            
            .role-selection {
                grid-template-columns: 1fr;
            }
        }

        /* Loading animation */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="row g-0">
                <!-- Left Side - Branding -->
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="register-left h-100">
                        <div>
                            <i class="fas fa-certificate brand-logo"></i>
                            <h2 class="brand-title">Join Our Platform</h2>
                            <p class="brand-subtitle">Create your account and start managing birth certificates digitally</p>
                            
                            <ul class="benefits-list">
                                <li>
                                    <i class="fas fa-rocket"></i>
                                    <div>
                                        <strong>Fast Registration</strong><br>
                                        <small>Get started in just a few minutes</small>
                                    </div>
                                </li>
                                <li>
                                    <i class="fas fa-shield-alt"></i>
                                    <div>
                                        <strong>Secure Platform</strong><br>
                                        <small>Your data is protected with enterprise-grade security</small>
                                    </div>
                                </li>
                                <li>
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <strong>Mobile Access</strong><br>
                                        <small>Access your certificates anywhere, anytime</small>
                                    </div>
                                </li>
                                <li>
                                    <i class="fas fa-headset"></i>
                                    <div>
                                        <strong>24/7 Support</strong><br>
                                        <small>Get help whenever you need it</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Registration Form -->
                <div class="col-lg-7">
                    <div class="register-right">
                        <div class="d-lg-none text-center mb-4">
                            <i class="fas fa-certificate text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">Birth Certificate System</h4>
                        </div>
                        
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step active" id="step1">1</div>
                            <div class="step" id="step2">2</div>
                            <div class="step" id="step3">3</div>
                        </div>
                        
                        <h1 class="form-title">Create Account</h1>
                        <p class="form-subtitle">Fill in your information to get started</p>
                        
                        <!-- Alert Messages -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['warning'])): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($_SESSION['warning']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['warning']); ?>
                        <?php endif; ?>
                        
                        <!-- Registration Form -->
                        <form method="POST" action="/register" id="registerForm">
                            <!-- Step 1: Role Selection -->
                            <div class="form-step" id="formStep1">
                                <h5 class="mb-3">Select Your Role</h5>
                                <div class="role-selection">
                                    <div class="role-card" data-role="parent">
                                        <i class="fas fa-user-friends text-primary"></i>
                                        <div class="role-title">Parent/Guardian</div>
                                        <div class="role-description">Apply for birth certificates for your children</div>
                                    </div>
                                    <div class="role-card" data-role="hospital">
                                        <i class="fas fa-hospital text-info"></i>
                                        <div class="role-title">Hospital Staff</div>
                                        <div class="role-description">Register births and manage hospital records</div>
                                    </div>
                                </div>
                                <input type="hidden" name="role" id="selectedRole" value="parent">
                                
                                <button type="button" class="btn btn-primary w-100 mt-3" onclick="nextStep()">
                                    Continue <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                            
                            <!-- Step 2: Personal Information -->
                            <div class="form-step d-none" id="formStep2">
                                <h5 class="mb-3">Personal Information</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                                            <label for="first_name">
                                                <i class="fas fa-user me-2"></i>First Name
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                                            <label for="last_name">
                                                <i class="fas fa-user me-2"></i>Last Name
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number">
                                    <label for="phone">
                                        <i class="fas fa-phone me-2"></i>Phone Number (Optional)
                                    </label>
                                </div>
                                
                                <!-- Hospital-specific fields -->
                                <div id="hospitalFields" class="d-none">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="hospital_id" name="hospital_id" placeholder="Hospital ID">
                                        <label for="hospital_id">
                                            <i class="fas fa-hospital me-2"></i>Hospital ID
                                        </label>
                                    </div>
                                    
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="license_number" name="license_number" placeholder="Medical License Number">
                                        <label for="license_number">
                                            <i class="fas fa-id-card me-2"></i>Medical License Number
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="button" class="btn btn-primary flex-fill" onclick="nextStep()">
                                        Continue <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Security -->
                            <div class="form-step d-none" id="formStep3">
                                <h5 class="mb-3">Security Settings</h5>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    <label for="password">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                </div>
                                
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <div class="strength-text" id="strengthText">Enter a password</div>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                    <label for="confirm_password">
                                        <i class="fas fa-lock me-2"></i>Confirm Password
                                    </label>
                                </div>
                                
                                <div class="terms-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Send me updates and news about the platform
                                    </label>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="submit" class="btn btn-primary flex-fill" id="registerBtn">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="login-link">
                            <p class="mb-0">Already have an account? <a href="/login">Sign in here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        let currentStep = 1;
        const totalSteps = 3;
        
        // Role selection
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Update hidden input
                const role = this.dataset.role;
                document.getElementById('selectedRole').value = role;
                
                // Show/hide hospital fields
                const hospitalFields = document.getElementById('hospitalFields');
                if (role === 'hospital') {
                    hospitalFields.classList.remove('d-none');
                    hospitalFields.querySelectorAll('input').forEach(input => input.required = true);
                } else {
                    hospitalFields.classList.add('d-none');
                    hospitalFields.querySelectorAll('input').forEach(input => input.required = false);
                }
            });
        });
        
        // Step navigation
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    // Hide current step
                    document.getElementById(`formStep${currentStep}`).classList.add('d-none');
                    
                    // Update step indicator
                    document.getElementById(`step${currentStep}`).classList.remove('active');
                    document.getElementById(`step${currentStep}`).classList.add('completed');
                    
                    // Show next step
                    currentStep++;
                    document.getElementById(`formStep${currentStep}`).classList.remove('d-none');
                    document.getElementById(`step${currentStep}`).classList.add('active');
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                // Hide current step
                document.getElementById(`formStep${currentStep}`).classList.add('d-none');
                document.getElementById(`step${currentStep}`).classList.remove('active');
                
                // Show previous step
                currentStep--;
                document.getElementById(`formStep${currentStep}`).classList.remove('d-none');
                document.getElementById(`step${currentStep}`).classList.remove('completed');
                document.getElementById(`step${currentStep}`).classList.add('active');
            }
        }
        
        // Validation
        function validateCurrentStep() {
            switch (currentStep) {
                case 1:
                    return document.getElementById('selectedRole').value !== '';
                case 2:
                    const requiredFields = ['first_name', 'last_name', 'email'];
                    const role = document.getElementById('selectedRole').value;
                    
                    if (role === 'hospital') {
                        requiredFields.push('hospital_id', 'license_number');
                    }
                    
                    for (let field of requiredFields) {
                        const input = document.getElementById(field);
                        if (!input.value.trim()) {
                            showAlert(`Please fill in the ${field.replace('_', ' ')} field`, 'danger');
                            input.focus();
                            return false;
                        }
                    }
                    
                    // Email validation
                    const email = document.getElementById('email').value;
                    if (!isValidEmail(email)) {
                        showAlert('Please enter a valid email address', 'danger');
                        document.getElementById('email').focus();
                        return false;
                    }
                    
                    return true;
                case 3:
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    const terms = document.getElementById('terms').checked;
                    
                    if (password.length < 6) {
                        showAlert('Password must be at least 6 characters long', 'danger');
                        return false;
                    }
                    
                    if (password !== confirmPassword) {
                        showAlert('Passwords do not match', 'danger');
                        return false;
                    }
                    
                    if (!terms) {
                        showAlert('Please accept the terms and conditions', 'danger');
                        return false;
                    }
                    
                    return true;
                default:
                    return true;
            }
        }
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            let className = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    text = 'Weak password';
                    className = 'strength-weak';
                    break;
                case 2:
                    text = 'Fair password';
                    className = 'strength-fair';
                    break;
                case 3:
                case 4:
                    text = 'Good password';
                    className = 'strength-good';
                    break;
                case 5:
                    text = 'Strong password';
                    className = 'strength-strong';
                    break;
            }
            
            strengthFill.className = `strength-fill ${className}`;
            strengthText.textContent = text;
            strengthText.className = `strength-text text-${className.split('-')[1]}`;
        });
        
        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                return;
            }
            
            const submitBtn = document.getElementById('registerBtn');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });
        
        // Utility functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const form = document.getElementById('registerForm');
            form.parentNode.insertBefore(alertDiv, form);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Initialize first role selection
        document.querySelector('.role-card[data-role="parent"]').click();
    </script>
</body>
</html>
