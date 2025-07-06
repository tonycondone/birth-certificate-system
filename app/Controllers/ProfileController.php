<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\UserRepository;

class ProfileController {
    private $authService;
    private $userRepository;

    public function __construct(AuthService $authService, UserRepository $userRepository) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    public function updateProfile() {
        // Check if user is logged in
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Get POST data
        $profileData = [
            'full_name' => $_POST['full_name'] ?? null,
            'phone_number' => $_POST['phone_number'] ?? null,
            'address' => $_POST['address'] ?? null
        ];

        // Perform basic validation
        $errors = $this->validateProfileData($profileData);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            exit;
        }

        // Update profile
        $result = $this->userRepository->updateProfile($user['id'], $profileData);

        if ($result) {
            echo json_encode(['message' => 'Profile updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
    }

    private function validateProfileData(array $data): array {
        $errors = [];

        // Full Name validation
        if (isset($data['full_name'])) {
            if (empty(trim($data['full_name']))) {
                $errors['full_name'] = 'Full name cannot be empty';
            } elseif (strlen($data['full_name']) > 100) {
                $errors['full_name'] = 'Full name is too long';
            }
        }

        // Phone Number validation (optional, but if provided must be valid)
        if (isset($data['phone_number']) && !empty($data['phone_number'])) {
            $phoneRegex = '/^[0-9\-\(\)\s]{10,15}$/';
            if (!preg_match($phoneRegex, $data['phone_number'])) {
                $errors['phone_number'] = 'Invalid phone number format';
            }
        }

        // Address validation (optional)
        if (isset($data['address']) && !empty($data['address'])) {
            if (strlen($data['address']) > 255) {
                $errors['address'] = 'Address is too long';
            }
        }

        return $errors;
    }
} 