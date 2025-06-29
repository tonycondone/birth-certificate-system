<?php

namespace App\Controllers;

use App\Auth\Authentication;
use App\Database\Database;
use Exception;
use PDOException;

class AuthController
{
    private Authentication $auth;

    public function __construct()
    {
        try {
            $this->auth = new Authentication(Database::getConnection());
        } catch (PDOException $e) {
            // Log error and redirect to error page
            error_log($e->getMessage());
            $_SESSION['error'] = 'System error. Please try again later.';
            header('Location: /error');
            exit;
        }
    }

    public function showLogin()
    {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $pageTitle = 'Login - Digital Birth Certificate System';
        require_once __DIR__ . '/../../resources/views/auth/login.php';
    }

    public function showRegister()
    {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $pageTitle = 'Register - Digital Birth Certificate System';
        require_once __DIR__ . '/../../resources/views/auth/register.php';
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
                    header('Location: /dashboard/registrar');
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

            // Validate phone number format
            if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', $userData['phone_number'])) {
                throw new Exception('Please enter a valid phone number');
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

            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/', $userData['password'])) {
                throw new Exception('Password must contain at least one letter and one number');
            }

            // Check if email already exists
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('An account with this email address already exists');
            }

            // Register the user
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

    public function logout()
    {
        if (isset($_SESSION['user']['id'])) {
            $this->logUserActivity($_SESSION['user']['id'], 'logout', 'User logged out');
        }
        
        $this->auth->logout();
        $_SESSION['success'] = 'You have been logged out successfully.';
        header('Location: /login');
        exit;
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
}