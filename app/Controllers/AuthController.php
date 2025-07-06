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
                $_SESSION['error'] = 'Invalid email or password';
                header('Location: /login');
                exit;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = 'Login failed. Please try again.';
            header('Location: /login');
            exit;
        }
    }
}
