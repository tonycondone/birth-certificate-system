<?php
namespace App\Controllers;

use App\Database\Database;
use Exception;

/**
 * AuthController
 * 
 * Handles authentication and user management
 */
class AuthController
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $pageTitle = 'Login - Digital Birth Certificate System';
        
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }
        
        // Include login view
        $viewPath = BASE_PATH . '/resources/views/auth/login.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("Error: Login view file not found at $viewPath");
        }
    }
    
    /**
     * Process login
     */
    private function processLogin()
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Please fill in all fields';
                header('Location: /login');
                exit;
            }
            
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Redirect to dashboard
                header('Location: /dashboard');
                exit;
            } else {
                header('Location: /login');
                exit;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            header('Location: /login');
            exit;
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $pageTitle = 'Register - Digital Birth Certificate System';
        
        // Handle registration form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegistration();
            return;
        }
        
        // Include registration view
        $viewPath = BASE_PATH . '/resources/views/auth/register.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("Error: Registration view file not found at $viewPath");
        }
    }
    
    /**
     * Process user registration
     */
    private function processRegistration()
    {
        try {
            // Validate input fields
            $requiredFields = [
                'first_name', 'last_name', 'email', 
                'password', 'confirm_password', 'role'
            ];
            
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['error'] = "Please fill in all required fields";
                    header('Location: /register');
                    exit;
                }
            }
            
            // Validate email format
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $_SESSION['error'] = "Invalid email address";
                header('Location: /register');
                exit;
            }
            
            // Check password match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = "Passwords do not match";
                header('Location: /register');
                exit;
            }
            
            // Validate password strength
            if (strlen($_POST['password']) < 8) {
                $_SESSION['error'] = "Password must be at least 8 characters long";
                header('Location: /register');
                exit;
            }
            
            // Validate role - map frontend roles to database roles
            $roleMapping = [
                'parent' => 'parent',
                'hospital' => 'hospital',
                'registrar' => 'registrar',
                'admin' => 'admin'
            ];
            
            $role = $_POST['role'];
            if (!isset($roleMapping[$role])) {
                $_SESSION['error'] = "Invalid user role selected";
                header('Location: /register');
                exit;
            }
            $dbRole = $roleMapping[$role];
            
            // Check if email already exists
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email address is already registered";
                header('Location: /register');
                exit;
            }
            
            // Generate username from email
            $username = explode('@', $email)[0];
            // Ensure username is unique
            $counter = 1;
            $originalUsername = $username;
            while (true) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if (!$stmt->fetch()) {
                    break;
                }
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            // Hash password with fallback for compatibility
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Prepare user data
            $stmt = $pdo->prepare("
                INSERT INTO users 
                (username, first_name, last_name, email, password, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $result = $stmt->execute([
                $username,
                $_POST['first_name'], 
                $_POST['last_name'], 
                $email, 
                $hashedPassword, 
                $dbRole
            ]);
            
            if ($result) {
                // Set success message and redirect to login
                $_SESSION['success'] = "Registration successful. Please log in.";
                header('Location: /login');
                exit;
            } else {
                // Database insertion failed
                $_SESSION['error'] = "Registration failed. Please try again.";
                header('Location: /register');
                exit;
            }
        } catch (Exception $e) {
            // Log the full error for debugging
            error_log("Registration error: " . $e->getMessage());
            
            // Show generic error to user
            $_SESSION['error'] = "An unexpected error occurred. Please try again.";
            header('Location: /register');
            exit;
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Clear all session data
        $_SESSION = array();
        session_destroy();

        // Redirect to login page
        header('Location: /login');
        exit;
    }
}
