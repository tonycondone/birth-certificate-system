<?php

namespace App\Services;

use PDO;
use App\Models\User;
use Exception;

class AuthService
{
    private PDO $db;
    private ?User $currentUser = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Authenticate user by username and password
     * 
     * @param string $username
     * @param string $password
     * @return User|null
     */
    public function authenticate(string $username, string $password): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData && password_verify($password, $userData['password'])) {
                $this->currentUser = new User($userData);
                return $this->currentUser;
            }

            return null;
        } catch (Exception $e) {
            // Log error
            error_log("Authentication error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the currently authenticated user
     * 
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        // Check session if no current user is set
        if (!$this->currentUser && isset($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $this->currentUser = new User($userData);
                }
            } catch (Exception $e) {
                error_log("Error retrieving current user: " . $e->getMessage());
            }
        }

        return $this->currentUser;
    }

    /**
     * Log out the current user
     */
    public function logout(): void
    {
        $this->currentUser = null;
        unset($_SESSION['user_id']);
    }

    /**
     * Register a new user
     * 
     * @param array $userData
     * @return User|null
     */
    public function register(array $userData): ?User
    {
        try {
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);

            // Prepare insert statement
            $stmt = $this->db->prepare("
                INSERT INTO users 
                (username, email, password, role, first_name, last_name) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['role'] ?? 'user',
                $userData['first_name'] ?? '',
                $userData['last_name'] ?? ''
            ]);

            // Fetch the newly created user
            $userId = $this->db->lastInsertId();
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $newUserData = $stmt->fetch(PDO::FETCH_ASSOC);

            return $newUserData ? new User($newUserData) : null;
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            return null;
        }
    }
}