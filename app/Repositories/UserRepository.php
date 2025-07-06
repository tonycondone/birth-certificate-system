<?php

namespace App\Repositories;

use PDO;
use Exception;
use App\Services\AuthService;

class UserRepository extends BaseRepository
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->table = 'users';
    }

    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate user
     * 
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $email, string $password): ?array
    {
        try {
            $user = $this->findByEmail($email);
            if (!$user) {
                return null;
            }

            // Use AuthService's password verification
            if (AuthService::verifyPassword($password, $user['password'])) {
                // Remove sensitive information
                unset($user['password']);
                return $user;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new user
     * 
     * @param array $userData
     * @return int|false
     */
    public function createUser(array $userData)
    {
        try {
            // Hash the password before saving
            if (isset($userData['password'])) {
                $userData['password'] = AuthService::hashPassword($userData['password']);
            }

            return $this->save($userData);
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find users by role
     * 
     * @param string $role
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findByRole(string $role, int $limit = 100, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? LIMIT ? OFFSET ?");
            $stmt->execute([$role, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding users by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user status
     * 
     * @param int $userId
     * @param string $newStatus
     * @return bool
     */
    public function updateStatus(int $userId, string $newStatus): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
            return $stmt->execute([$newStatus, $userId]);
        } catch (Exception $e) {
            error_log("Error updating user status: " . $e->getMessage());
            return false;
        }
    }
} 