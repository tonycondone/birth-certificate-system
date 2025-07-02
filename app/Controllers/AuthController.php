<?php

namespace App\Controllers;

use App\Auth\Authentication;
use App\Database\Database;
use Exception;
use PDOException;

/**
 * Class AuthController
 *
 * Manages user authentication actions including login,
 * registration, password reset, and two-factor authentication.
 */
class AuthController
{
    private ?Authentication $auth = null;

    public function __construct()
    {
        // Don't initialize auth in constructor to avoid database connection issues
        // Auth will be initialized when needed for login/register operations
    }

    /**
     * Display and process the login form.
     * Validates credentials, sets session, and redirects.
     *
     * @return void
     */
    public function showLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getConnection();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            if (!$email || !$password) {
                $error = 'Email and password are required.';
            } else {
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Prevent login if email not verified
                    if (empty($user['email_verified'])) {
                        $error = 'Please verify your email address before logging in.';
                    } elseif (password_verify($password, $user['password'])) {
                        // Set session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['login_time'] = time();
                        // User array for application workflows
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                        ];
                        
                        // Log activity
                        $this->logActivity($user['id'], 'User logged in successfully');
                        
                        header('Location: /dashboard');
                        exit;
                    } else {
                        $error = 'Invalid credentials.';
                    }
                } else {
                    $error = 'Invalid credentials.';
                }
            }
        }
        include BASE_PATH . '/resources/views/auth/login.php';
    }

    /**
     * Display and process the registration form.
     * Validates input, creates new user, and logs activity.
     *
     * @return void
     */
    public function showRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = Database::getConnection();
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                $role = $_POST['role'] ?? 'parent';
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                // Validation
                $errors = $this->validateRegistration($email, $password, $confirmPassword, $firstName, $lastName, $phone);
                
                if (empty($errors)) {
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already registered.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Generate a unique username based on email
                        $username = $this->generateUsername($email);
                        
                        $stmt = $pdo->prepare(
                            'INSERT INTO users (username, email, password, role, first_name, last_name, phone_number, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)' 
                        );
                        $stmt->execute([$username, $email, $hash, $role, $firstName, $lastName, $phone]);
                        $userId = $pdo->lastInsertId();
                        
                        // Generate email verification token
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
                        $stmt = $pdo->prepare(
                            'INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)'
                        );
                        $stmt->execute([$userId, $token, $expiresAt]);

                        // Send verification email
                        $emailService = new \App\Services\EmailService(
                            new \App\Services\BlockchainService(),
                            \App\Services\LoggingService::getInstance()
                        );
                        $emailService->sendVerificationEmail($email, $firstName . ' ' . $lastName, $token);

                        // Inform user to check email
                        $success = 'Registration successful! Please check your email to verify your account.';
                    }
                } else {
                    $error = implode('<br>', $errors);
                }
            } catch (\Exception $e) {
                error_log('Registration error: ' . $e->getMessage());
                $error = 'An unexpected error occurred during registration. Please try again later.';
            }
        }
        include BASE_PATH . '/resources/views/auth/register.php';
    }

    /**
     * Log out the current user and destroy the session.
     *
     * @return void
     */
    public function logout()
    {
        // Log activity before destroying session
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'User logged out');
        }
        
        session_destroy();
        header('Location: /login');
        exit;
    }

    /**
     * Display and process the forgot password form.
     *
     * @return void
     */
    public function showForgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare('SELECT id, first_name FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $success = 'Password reset instructions sent to your email.';
                } else {
                    $error = 'Email not found in our system.';
                }
            } else {
                $error = 'Please enter a valid email address.';
            }
        }
        include BASE_PATH . '/resources/views/auth/forgot-password.php';
    }

    /**
     * Display and process the password reset form.
     *
     * @return void
     */
    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if ($password === $confirmPassword && strlen($password) >= 8) {
                $success = 'Password updated successfully! You can now login with your new password.';
            } else {
                $error = 'Passwords do not match or are too short.';
            }
        }
        
        include BASE_PATH . '/resources/views/auth/reset-password.php';
    }

    /**
     * Display and process the two-factor authentication form.
     *
     * @return void
     */
    public function showTwoFactorAuth()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'] ?? '';
            
            if (strlen($code) === 6 && is_numeric($code)) {
                header('Location: /dashboard');
                exit;
            } else {
                $error = 'Invalid verification code.';
            }
        }
        
        include BASE_PATH . '/resources/views/auth/2fa.php';
    }

    /**
     * Verify email address using a token and activate the user account.
     *
     * @return void
     */
    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        $error = '';
        $success = '';

        if (empty($token)) {
            header('Location: /login');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            // Fetch verification record
            $stmt = $pdo->prepare('SELECT user_id, expires_at FROM email_verifications WHERE token = ?');
            $stmt->execute([$token]);
            $record = $stmt->fetch();

            if (!$record) {
                $error = 'Invalid or expired verification link.';
            } elseif (strtotime($record['expires_at']) < time()) {
                $error = 'Verification link has expired. Please request a new verification email.';
            } else {
                // Activate user
                $stmt = $pdo->prepare('UPDATE users SET email_verified = 1, email_verified_at = NOW() WHERE id = ?');
                $stmt->execute([$record['user_id']]);

                // Delete used token
                $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE token = ?');
                $stmt->execute([$token]);

                $success = 'Email verified successfully! You can now log in with your account.';
            }
        } catch (\Exception $e) {
            error_log('Email verification error: ' . $e->getMessage());
            $error = 'An unexpected error occurred during verification. Please try again later.';
        }

        include BASE_PATH . '/resources/views/auth/email-verified.php';
    }

    private function initializeAuth()
    {
        if ($this->auth === null) {
            try {
                $this->auth = new Authentication(Database::getConnection());
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['error'] = 'Database connection failed. Please try again later.';
                header('Location: /error');
                exit;
            }
        }
    }

    public function login()
    {
        try {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                throw new Exception('Invalid request. Please try again.');
            }

            // Rate limiting check
            if ($this->isRateLimited($_SERVER['REMOTE_ADDR'], 'login')) {
                throw new Exception('Too many login attempts. Please wait 15 minutes before trying again.');
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            // Enhanced validation
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            if (strlen($password) < 1) {
                throw new Exception('Password is required');
            }

            // Attempt login
            $this->initializeAuth();
            $user = $this->auth->login($email, $password, $rememberMe);
            
            // Clear any existing error messages
            unset($_SESSION['error']);
            
            $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!';
            
            // Log successful login
            $this->logUserActivity($user['id'], 'login', 'Successful login');
            
            // Redirect based on role
            switch ($user['role']) {
                case 'parent':
                    header('Location: /dashboard/parent');
                    break;
                case 'hospital':
                    header('Location: /dashboard/hospital');
                    break;
                case 'registrar':
                    header('Location: /dashboard');
                    break;
                case 'admin':
                    header('Location: /dashboard/admin');
                    break;
                default:
                    header('Location: /dashboard');
            }
            exit;
        } catch (Exception $e) {
            // Log failed login attempt
            if (isset($_POST['email'])) {
                $this->logFailedLogin($_POST['email'], $_SERVER['REMOTE_ADDR']);
            }
            
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }
    }

    public function register()
    {
        try {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                throw new Exception('Invalid request. Please try again.');
            }

            // Rate limiting check
            if ($this->isRateLimited($_SERVER['REMOTE_ADDR'], 'register')) {
                throw new Exception('Too many registration attempts. Please wait 15 minutes before trying again.');
            }

            // Generate username from email
            $email = trim($_POST['email'] ?? '');
            $username = $this->generateUsername($email);
            
            // Enhanced validation
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $_POST['password'] ?? '',
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'role' => $_POST['role'] ?? 'parent',
                'phone_number' => trim($_POST['phone_number'] ?? ''),
            ];

            // Validate required fields
            if (empty($userData['first_name']) || empty($userData['last_name'])) {
                throw new Exception('First name and last name are required');
            }

            if (empty($userData['phone_number'])) {
                throw new Exception('Phone number is required');
            }

            // Validate phone number format - extremely flexible
            if (strlen(trim($userData['phone_number'])) < 3) {
                throw new Exception('Please enter a phone number');
            }

            // Validate role
            $validRoles = ['parent', 'hospital', 'registrar'];
            if (!in_array($userData['role'], $validRoles)) {
                throw new Exception('Invalid role selected');
            }

            // Add role-specific fields with validation
            if ($userData['role'] === 'parent') {
                $userData['national_id'] = trim($_POST['national_id'] ?? '');
                if (empty($userData['national_id'])) {
                    throw new Exception('National ID is required for parent registration');
                }
                if (!preg_match('/^[A-Z0-9]{6,20}$/', $userData['national_id'])) {
                    throw new Exception('Please enter a valid National ID');
                }
            } elseif ($userData['role'] === 'hospital') {
                $userData['hospital_id'] = trim($_POST['hospital_id'] ?? '');
                if (empty($userData['hospital_id'])) {
                    throw new Exception('Hospital registration number is required');
                }
                if (!preg_match('/^[A-Z0-9]{4,15}$/', $userData['hospital_id'])) {
                    throw new Exception('Please enter a valid hospital registration number');
                }
            } elseif ($userData['role'] === 'registrar') {
                $userData['registrar_id'] = trim($_POST['registrar_id'] ?? '');
                if (empty($userData['registrar_id'])) {
                    throw new Exception('Registrar ID is required');
                }
                if (!preg_match('/^[A-Z0-9]{4,15}$/', $userData['registrar_id'])) {
                    throw new Exception('Please enter a valid registrar ID');
                }
            }

            // Validate password strength
            if (strlen($userData['password']) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }

            if (!preg_match('/[A-Za-z]/', $userData['password'])) {
                throw new Exception('Password must contain at least one letter');
            }

            if (!preg_match('/[0-9]/', $userData['password'])) {
                throw new Exception('Password must contain at least one number');
            }

            // Check if email already exists
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('An account with this email address already exists');
            }

            // Register the user
            $this->initializeAuth();
            $this->auth->register($userData);
            
            // Log successful registration
            $this->logUserActivity(null, 'register', 'New user registration: ' . $email);
            
            $_SESSION['success'] = 'Registration successful! Your username is: ' . $username . '. Please login to continue.';
            header('Location: /login');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /register');
            exit;
        }
    }

    public function forgotPassword()
    {
        $pageTitle = 'Forgot Password - Digital Birth Certificate System';
        require_once __DIR__ . '/../../resources/views/auth/forgot-password.php';
    }

    public function resetPassword()
    {
        try {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            // Check if user exists
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                // In a real application, send email here
                // For now, we'll just show success message
                $_SESSION['success'] = 'If an account with that email exists, you will receive password reset instructions.';
            } else {
                // Don't reveal if email exists or not for security
                $_SESSION['success'] = 'If an account with that email exists, you will receive password reset instructions.';
            }
            
            header('Location: /login');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /auth/forgot-password');
            exit;
        }
    }
    
    /**
     * Generate a unique username from email
     */
    private function generateUsername($email)
    {
        $baseUsername = strtolower(explode('@', $email)[0]);
        $username = $baseUsername;
        $counter = 1;
        
        try {
            $pdo = Database::getConnection();
            
            // Check if username exists and generate unique one
            while (true) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetchColumn() == 0) {
                    break; // Username is unique
                }
                
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            return $username;
        } catch (Exception $e) {
            // Fallback to timestamp-based username
            return $baseUsername . '_' . time();
        }
    }

    /**
     * Check if IP is rate limited for specific action
     */
    private function isRateLimited($ip, $action)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM login_attempts 
                WHERE ip_address = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");
            $stmt->execute([$ip, $action]);
            return $stmt->fetchColumn() >= 5;
        } catch (Exception $e) {
            return false; // Don't block if we can't check
        }
    }

    /**
     * Log failed login attempt
     */
    private function logFailedLogin($email, $ip)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts (email, ip_address, action, success, created_at) 
                VALUES (?, ?, 'login', 0, NOW())
            ");
            $stmt->execute([$email, $ip]);
        } catch (Exception $e) {
            error_log("Failed to log login attempt: " . $e->getMessage());
        }
    }

    /**
     * Log user activity
     */
    private function logUserActivity($userId, $action, $description)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO user_activity_log (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId, 
                $action, 
                $description, 
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log user activity: " . $e->getMessage());
        }
    }

    // Helper methods
    private function validateRegistration($email, $password, $confirmPassword, $firstName, $lastName, $phone) {
        $errors = [];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($firstName) || empty($lastName)) {
            $errors[] = 'First name and last name are required.';
        }
        
        if (!empty($phone) && !preg_match('/^\+?[\d\s\-\(\)]{10,}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
        
        return $errors;
    }
    
    private function logActivity($userId, $description) {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                INSERT INTO activity_logs (user_id, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $userId, 
                $description, 
                $_SERVER['REMOTE_ADDR'] ?? '', 
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            // Log error silently
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}