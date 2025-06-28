<?php

namespace App\Auth;

use PDO;
use Exception;
use DateTime;

class Authentication
{
    private PDO $db;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes in seconds
    private const PASSWORD_HISTORY_LIMIT = 5;
    private const PASSWORD_EXPIRY_DAYS = 90;
    
    public function __construct(PDO $db = null)
    {
        if ($db === null) {
            // Get database connection from Database class if not provided
            require_once __DIR__ . '/../Database/Database.php';
            $database = new \App\Database\Database();
            $this->db = $database->getConnection();
        } else {
            $this->db = $db;
        }
    }

    /**
     * Get the current authenticated user
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser(): ?array
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        // Try to authenticate via remember me token
        return $this->checkRememberMe();
    }

    public function login(string $email, string $password, bool $rememberMe = false, ?string $twoFactorCode = null): array
    {
        // Check for account lockout
        if ($this->isAccountLocked($email)) {
            throw new Exception('Account is temporarily locked. Please try again later.');
        }

        // Get user and verify credentials
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Record login attempt
        $this->recordLoginAttempt($email, $_SERVER['REMOTE_ADDR'], (bool)$user && password_verify($password, $user['password']));

        if (!$user || !password_verify($password, $user['password'])) {
            // Check if we should lock the account
            if ($this->shouldLockAccount($email)) {
                $this->lockAccount($email);
                throw new Exception('Too many failed attempts. Account has been temporarily locked.');
            }
            throw new Exception('Invalid email or password');
        }

        // Check if password is expired
        if ($this->isPasswordExpired($user)) {
            throw new Exception('Password has expired. Please reset your password.');
        }

        // Verify 2FA if enabled
        if ($this->isTwoFactorEnabled($user['id'])) {
            if (!$twoFactorCode) {
                throw new Exception('Two-factor authentication code required');
            }
            if (!$this->verifyTwoFactorCode($user['id'], $twoFactorCode)) {
                throw new Exception('Invalid two-factor authentication code');
            }
        }

        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
            $stmt->execute([$token, $user['id']]);
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
            setcookie('remember_email', $email, time() + (86400 * 30), '/', '', true, true);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ];

        return $user;
    }

    public function register(array $data): void
    {
        // Validate required fields
        $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception(ucfirst($field) . ' is required');
            }
        }

        // Validate role-specific fields
        if ($data['role'] === 'parent' && empty($data['national_id'])) {
            throw new Exception('National ID is required for parents');
        }
        if ($data['role'] === 'hospital' && empty($data['hospital_id'])) {
            throw new Exception('Hospital registration number is required');
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check if email exists
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Email already exists');
        }

        // Enhanced password validation
        $this->validatePassword($data['password']);

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Set password expiration
        $expiryDate = new DateTime();
        $expiryDate->modify('+' . self::PASSWORD_EXPIRY_DAYS . ' days');
        $data['password_expires_at'] = $expiryDate->format('Y-m-d H:i:s');

        // Prepare SQL
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = sprintf(
            'INSERT INTO users (%s) VALUES (%s)',
            implode(', ', $fields),
            $placeholders
        );

        // Begin transaction
        $this->db->beginTransaction();
        try {
            // Execute insert
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            
            // Get the new user ID
            $userId = $this->db->lastInsertId();
            
            // Store password in history
            $this->addPasswordToHistory($userId, $data['password']);
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    /**
     * Validate password against security requirements
     */
    private function validatePassword(string $password): void
    {
        if (strlen($password) < 12) {
            throw new Exception('Password must be at least 12 characters long');
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter');
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception('Password must contain at least one lowercase letter');
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception('Password must contain at least one number');
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new Exception('Password must contain at least one special character');
        }
    }

    /**
     * Check if account should be locked due to failed attempts
     */
    private function shouldLockAccount(string $email): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM login_attempts 
            WHERE email = ? AND success = 0 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ');
        $stmt->execute([$email]);
        return $stmt->fetchColumn() >= self::MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Lock an account
     */
    private function lockAccount(string $email): void
    {
        $lockExpiry = new DateTime();
        $lockExpiry->modify('+' . self::LOCKOUT_DURATION . ' seconds');
        
        $stmt = $this->db->prepare('
            UPDATE users 
            SET account_locked = 1, lock_expires_at = ? 
            WHERE email = ?
        ');
        $stmt->execute([$lockExpiry->format('Y-m-d H:i:s'), $email]);
    }

    /**
     * Check if account is locked
     */
    private function isAccountLocked(string $email): bool
    {
        $stmt = $this->db->prepare('
            SELECT account_locked, lock_expires_at 
            FROM users 
            WHERE email = ?
        ');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || !$result['account_locked']) {
            return false;
        }

        $lockExpiry = new DateTime($result['lock_expires_at']);
        $now = new DateTime();
        
        if ($now > $lockExpiry) {
            // Lock has expired, unlock the account
            $stmt = $this->db->prepare('
                UPDATE users 
                SET account_locked = 0, lock_expires_at = NULL 
                WHERE email = ?
            ');
            $stmt->execute([$email]);
            return false;
        }
        
        return true;
    }

    /**
     * Record login attempt
     */
    private function recordLoginAttempt(string $email, string $ipAddress, bool $success): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO login_attempts (email, ip_address, success) 
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$email, $ipAddress, $success]);
    }

    /**
     * Check if password is expired
     */
    private function isPasswordExpired(array $user): bool
    {
        if (!isset($user['password_expires_at'])) {
            return false;
        }

        $expiryDate = new DateTime($user['password_expires_at']);
        $now = new DateTime();
        return $now > $expiryDate;
    }

    /**
     * Add password to history
     */
    private function addPasswordToHistory(int $userId, string $passwordHash): void
    {
        // Add new password to history
        $stmt = $this->db->prepare('
            INSERT INTO password_history (user_id, password_hash) 
            VALUES (?, ?)
        ');
        $stmt->execute([$userId, $passwordHash]);

        // Remove old entries beyond the limit
        $stmt = $this->db->prepare('
            DELETE FROM password_history 
            WHERE user_id = ? 
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM password_history 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?
                ) tmp
            )
        ');
        $stmt->execute([$userId, $userId, self::PASSWORD_HISTORY_LIMIT]);
    }

    /**
     * Check if two-factor authentication is enabled
     */
    private function isTwoFactorEnabled(int $userId): bool
    {
        $stmt = $this->db->prepare('
            SELECT enabled 
            FROM two_factor_auth 
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verify two-factor authentication code
     */
    private function verifyTwoFactorCode(int $userId, string $code): bool
    {
        // Implementation would use a proper 2FA library like Google Authenticator
        // This is a placeholder for the actual implementation
        $stmt = $this->db->prepare('
            SELECT secret_key 
            FROM two_factor_auth 
            WHERE user_id = ? AND enabled = 1
        ');
        $stmt->execute([$userId]);
        $secret = $stmt->fetchColumn();

        if (!$secret) {
            return false;
        }

        // In real implementation, use proper 2FA verification
        // return Google2FA::verify($secret, $code);
        return true; // Placeholder return
    }
    public function isAuthenticated(): bool
    {
        // Check if user is logged in
        if (isset($_SESSION['user'])) {
            return true;
        }

        // Check remember me token
        return $this->checkRememberMe() !== null;
    }

    public function refreshSession(): void
    {
        // Update last activity timestamp
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration']) || 
            (time() - $_SESSION['last_regeneration']) > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    public function logout(): void
    {
        // Clear session
        session_destroy();
        
        // Clear remember me cookies
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_email', '', time() - 3600, '/');
        }
    }

    public function checkRememberMe(): ?array
    {
        if (isset($_COOKIE['remember_token'], $_COOKIE['remember_email'])) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND remember_token = ?');
            $stmt->execute([$_COOKIE['remember_email'], $_COOKIE['remember_token']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ];
                return $user;
            }
        }
        return null;
    }

    /**
     * Update user password with history check
     * @throws Exception if password validation fails
     */
    public function updatePassword(string $email, string $currentPassword, string $newPassword): void
    {
        // Verify current password
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new Exception('Current password is incorrect');
        }

        // Validate new password
        if (strlen($newPassword) < 12) {
            throw new Exception('Password must be at least 12 characters long');
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new Exception('Password must contain at least one uppercase letter');
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            throw new Exception('Password must contain at least one lowercase letter');
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one number');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one special character');
        }

        // Check password history
        $stmt = $this->db->prepare('
            SELECT password_hash 
            FROM password_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$user['id'], self::PASSWORD_HISTORY_LIMIT]);
        
        while ($history = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($newPassword, $history['password_hash'])) {
                throw new Exception('Password has been used recently');
            }
        }

        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $this->db->beginTransaction();
        try {
            // Update user's password
            $stmt = $this->db->prepare('
                UPDATE users 
                SET password = ?, 
                    password_expires_at = DATE_ADD(NOW(), INTERVAL ? DAY)
                WHERE id = ?
            ');
            $stmt->execute([$newHash, self::PASSWORD_EXPIRY_DAYS, $user['id']]);

            // Add to password history
            $stmt = $this->db->prepare('
                INSERT INTO password_history (user_id, password_hash) 
                VALUES (?, ?)
            ');
            $stmt->execute([$user['id'], $newHash]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}