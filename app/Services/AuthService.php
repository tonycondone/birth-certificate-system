<?php

namespace App\Services;

use App\Database\Database;
use PDO;
use Exception;

/**
 * AuthService
 * 
 * Handles authentication and authorization operations
 */
class AuthService
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (Exception $e) {
            error_log("AuthService initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool True if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current authenticated user
     * 
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated() || !$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if current user has required role
     * 
     * @param array|string $roles Required roles
     * @return bool True if user has required role
     */
    public function hasRole($roles): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? null;
        if (!$userRole) {
            return false;
        }

        if (is_string($roles)) {
            return $userRole === $roles;
        }

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return false;
    }

    /**
     * Require user to have specific role(s) or redirect
     * 
     * @param array|string $roles Required roles
     * @param string $redirectUrl URL to redirect to if unauthorized
     * @return bool True if authorized
     */
    public function requireRole($roles, string $redirectUrl = '/login'): bool
    {
        if (!$this->isAuthenticated()) {
            header("Location: $redirectUrl");
            exit;
        }

        if (!$this->hasRole($roles)) {
            header("Location: /dashboard");
            exit;
        }

        return true;
    }

    /**
     * Authenticate user with email and password
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    public function authenticate(string $email, string $password): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database connection error'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Update last login
            $this->updateLastLogin($user['id']);

            return [
                'success' => true,
                'message' => 'Authentication successful',
                'user' => $user
            ];

        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication error'];
        }
    }

    /**
     * Log out current user
     * 
     * @return bool True if logout successful
     */
    public function logout(): bool
    {
        try {
            // Clear session data
            $_SESSION = [];
            
            // Destroy session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy session
            session_destroy();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has permission for specific action
     * 
     * @param string $action Action to check
     * @param array $context Additional context
     * @return bool True if user has permission
     */
    public function hasPermission(string $action, array $context = []): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? null;

        // Define role-based permissions
        $permissions = [
            'admin' => ['*'], // Admin has all permissions
            'registrar' => [
                'view_applications',
                'approve_applications',
                'reject_applications',
                'view_certificates',
                'generate_reports'
            ],
            'hospital' => [
                'view_own_applications',
                'verify_records',
                'view_own_certificates'
            ],
            'parent' => [
                'create_applications',
                'view_own_applications',
                'view_own_certificates',
                'track_applications'
            ]
        ];

        if (!isset($permissions[$userRole])) {
            return false;
        }

        $userPermissions = $permissions[$userRole];

        // Admin has all permissions
        if (in_array('*', $userPermissions)) {
            return true;
        }

        return in_array($action, $userPermissions);
    }

    /**
     * Update user's last login timestamp
     * 
     * @param int $userId User ID
     * @return bool True if update successful
     */
    private function updateLastLogin(int $userId): bool
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            return $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate password hash
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Check if current session is valid
     * 
     * @return bool True if session is valid
     */
    public function isValidSession(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Check session timeout (optional)
        $sessionTimeout = 3600; // 1 hour
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
                $this->logout();
                return false;
            }
        }

        $_SESSION['last_activity'] = time();
        return true;
    }
}
