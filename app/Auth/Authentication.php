<?php

namespace App\Auth;

use PDO;
use Exception;

class Authentication
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(string $email, string $password, bool $rememberMe = false): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Invalid email or password');
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

        // Validate password
        if (strlen($data['password']) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        if (!preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/\d/', $data['password'])) {
            throw new Exception('Password must contain both letters and numbers');
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Prepare SQL
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = sprintf(
            'INSERT INTO users (%s) VALUES (%s)',
            implode(', ', $fields),
            $placeholders
        );

        // Execute insert
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
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
}