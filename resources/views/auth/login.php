<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Digital Birth Certificate System</title>
    
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 2rem;
        }

        .login-left {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-right {
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

        .feature-list {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
        }

        .feature-list li i {
            color: #3b82f6;
            margin-right: 0.75rem;
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

        .form-floating > .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 0.75rem;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
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

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            background: white;
            color: #64748b;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            border-color: #cbd5e1;
            color: #475569;
            transform: translateY(-1px);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
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

        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
            }
            
            .login-left {
                padding: 2rem;
            }
            
            .login-right {
                padding: 2rem;
            }
            
            .social-login {
                flex-direction: column;
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
    <div class="login-container">
        <div class="row g-0">
            <!-- Left Side - Branding -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="login-left h-100">
                    <div>
                        <i class="fas fa-certificate brand-logo"></i>
                        <h2 class="brand-title">Digital Birth Certificate System</h2>
                        <p class="brand-subtitle">Secure, Fast, and Reliable Birth Certificate Management</p>
                        
                        <ul class="feature-list">
                            <li>
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Digital Certificates</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Fast Processing Times</span>
                            </li>
                            <li>
                                <i class="fas fa-mobile-alt"></i>
                                <span>Mobile-Friendly Access</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Instant Verification</span>
                            </li>
                            <li>
                                <i class="fas fa-download"></i>
                                <span>Easy Downloads</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-7">
                <div class="login-right">
                    <div class="d-lg-none text-center mb-4">
                        <i class="fas fa-certificate text-primary" style="font-size: 2rem;"></i>
                        <h4 class="mt-2">Birth Certificate System</h4>
                    </div>
                    
                    <h1 class="form-title">Welcome Back</h1>
                    <p class="form-subtitle">Please sign in to your account to continue</p>
                    
                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

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

                    <?php if (isset($_SESSION['info'])): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['info']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['info']); ?>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="/login" id="loginForm">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="/auth/forgot-password" class="forgot-password">
                                Forgot password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <!-- Social Login -->
                    <div class="divider">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <a href="#" class="social-btn" onclick="showComingSoon('Google')">
                            <i class="fab fa-google"></i>
                        </a>
                        <a href="#" class="social-btn" onclick="showComingSoon('Microsoft')">
                            <i class="fab fa-microsoft"></i>
                        </a>
                        <a href="#" class="social-btn" onclick="showComingSoon('Apple')">
                            <i class="fab fa-apple"></i>
                        </a>
                    </div>
                    
                    <!-- Register Link -->
                    <div class="register-link">
                        <p class="mb-0">Don't have an account? <a href="/register">Create one here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('loginBtn');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Basic validation
            if (!email || !password) {
                e.preventDefault();
                showAlert('Please fill in all fields', 'danger');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address', 'danger');
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });
        
        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Show alert function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const form = document.getElementById('loginForm');
            form.parentNode.insertBefore(alertDiv, form);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Social login placeholder
        function showComingSoon(provider) {
            showAlert(`${provider} login will be available soon!`, 'info');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Password visibility toggle
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Demo credentials helper
        function fillDemoCredentials(role) {
            const credentials = {
                admin: { email: 'admin@birthcert.gov', password: 'admin123' },
                registrar: { email: 'registrar@birthcert.gov', password: 'registrar123' },
                hospital: { email: 'hospital@medical.com', password: 'hospital123' },
                parent: { email: 'parent@example.com', password: 'parent123' }
            };
            
            if (credentials[role]) {
                document.getElementById('email').value = credentials[role].email;
                document.getElementById('password').value = credentials[role].password;
                showAlert(`Demo credentials filled for ${role} role`, 'info');
            }
        }
        
        // Add demo credentials buttons (for development)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const demoDiv = document.createElement('div');
            demoDiv.className = 'text-center mt-3 p-3 bg-light rounded';
            demoDiv.innerHTML = `
                <small class="text-muted d-block mb-2">Demo Credentials (Development Only)</small>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="fillDemoCredentials('admin')">Admin</button>
                    <button type="button" class="btn btn-outline-info" onclick="fillDemoCredentials('registrar')">Registrar</button>
                    <button type="button" class="btn btn-outline-warning" onclick="fillDemoCredentials('hospital')">Hospital</button>
                    <button type="button" class="btn btn-outline-success" onclick="fillDemoCredentials('parent')">Parent</button>
                </div>
            `;
            document.querySelector('.register-link').appendChild(demoDiv);
        }
    </script>
</body>
</html>
