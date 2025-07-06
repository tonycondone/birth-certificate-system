<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\EmailService;
use Firebase\JWT\JWT;
use DateTime;
use DateInterval;

class PasswordResetService {
    private $userRepository;
    private $emailService;
    private $tokenLifetime = 3600; // 1 hour

    public function __construct(UserRepository $userRepository, EmailService $emailService) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function initiatePasswordReset(string $email): bool {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return false;
        }

        $resetToken = $this->generateResetToken($user['id']);
        $resetLink = $this->generateResetLink($resetToken);

        // Send password reset email
        $this->emailService->sendPasswordResetEmail($email, $resetLink);

        return true;
    }

    private function generateResetToken(int $userId): string {
        $payload = [
            'user_id' => $userId,
            'exp' => (new DateTime())->add(new DateInterval("PT{$this->tokenLifetime}S"))->getTimestamp()
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    private function generateResetLink(string $token): string {
        return "{$_ENV['APP_URL']}/reset-password?token={$token}";
    }

    public function validateResetToken(string $token): ?int {
        try {
            $decoded = JWT::decode($token, $_ENV['JWT_SECRET'], ['HS256']);
            
            // Check if token is still valid
            if ($decoded->exp < time()) {
                return null;
            }

            return $decoded->user_id;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function resetPassword(int $userId, string $newPassword): bool {
        // Validate password strength
        if (!$this->isPasswordStrong($newPassword)) {
            return false;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);

        // Update user's password
        return $this->userRepository->updatePassword($userId, $hashedPassword);
    }

    private function isPasswordStrong(string $password): bool {
        // Strong password requirements
        return 
            strlen($password) >= 12 && 
            preg_match('/[A-Z]/', $password) && 
            preg_match('/[a-z]/', $password) && 
            preg_match('/\d/', $password) && 
            preg_match('/[^a-zA-Z\d]/', $password);
    }
} 