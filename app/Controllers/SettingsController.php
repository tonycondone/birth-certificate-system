<?php

namespace App\Controllers;

use App\Repositories\SettingsRepository;
use App\Services\AuthService;
use Exception;

class SettingsController
{
    private SettingsRepository $settingsRepository;
    private AuthService $authService;

    public function __construct(
        SettingsRepository $settingsRepository,
        AuthService $authService
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->authService = $authService;
    }

    /**
     * Get system configuration settings
     * 
     * @return array System configuration settings
     * @throws Exception
     */
    public function getSystemSettings(): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['admin']);

        try {
            return $this->settingsRepository->getSystemSettings();
        } catch (Exception $e) {
            error_log('Get System Settings Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update system configuration settings
     * 
     * @param array $settings Settings to update
     * @return bool Success status
     * @throws Exception
     */
    public function updateSystemSettings(array $settings): bool
    {
        // Ensure user is authorized
        $currentUser = $this->authService->requireRole(['admin']);

        try {
            // Validate settings
            $this->validateSettings($settings);

            return $this->settingsRepository->updateSystemSettings(
                $settings, 
                $currentUser['id']
            );
        } catch (Exception $e) {
            error_log('Update System Settings Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Manage user roles
     * 
     * @param string $action Action to perform (add, remove, update)
     * @param array $roleData Role details
     * @return bool Success status
     * @throws Exception
     */
    public function manageUserRoles(string $action, array $roleData): bool
    {
        // Ensure user is authorized
        $currentUser = $this->authService->requireRole(['admin']);

        try {
            // Validate role data
            $this->validateRoleData($action, $roleData);

            return $this->settingsRepository->manageUserRoles(
                $action, 
                $roleData, 
                $currentUser['id']
            );
        } catch (Exception $e) {
            error_log('Manage User Roles Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate settings before update
     * 
     * @param array $settings Settings to validate
     * @throws Exception
     */
    private function validateSettings(array $settings): void
    {
        $allowedSettings = [
            'system_name',
            'registration_enabled',
            'max_daily_certificates',
            'certificate_validity_years',
            'notification_email',
            'backup_frequency'
        ];

        foreach ($settings as $key => $value) {
            // Ensure setting is allowed
            if (!in_array($key, $allowedSettings)) {
                throw new Exception("Invalid setting: $key");
            }

            // Perform specific validations
            switch ($key) {
                case 'system_name':
                    if (empty($value) || strlen($value) > 100) {
                        throw new Exception('System name must be 1-100 characters');
                    }
                    break;
                
                case 'registration_enabled':
                    if (!is_bool($value)) {
                        throw new Exception('Registration enabled must be a boolean');
                    }
                    break;
                
                case 'max_daily_certificates':
                    if (!is_int($value) || $value < 0 || $value > 1000) {
                        throw new Exception('Max daily certificates must be an integer between 0 and 1000');
                    }
                    break;
                
                case 'certificate_validity_years':
                    if (!is_int($value) || $value < 1 || $value > 10) {
                        throw new Exception('Certificate validity must be between 1 and 10 years');
                    }
                    break;
                
                case 'notification_email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid notification email address');
                    }
                    break;
                
                case 'backup_frequency':
                    $allowedFrequencies = ['daily', 'weekly', 'monthly'];
                    if (!in_array($value, $allowedFrequencies)) {
                        throw new Exception('Invalid backup frequency');
                    }
                    break;
            }
        }
    }

    /**
     * Validate role data before management
     * 
     * @param string $action Action to perform
     * @param array $roleData Role details
     * @throws Exception
     */
    private function validateRoleData(string $action, array $roleData): void
    {
        // Validate action
        $allowedActions = ['add', 'remove', 'update'];
        if (!in_array($action, $allowedActions)) {
            throw new Exception('Invalid role management action');
        }

        // Validate role name
        if (empty($roleData['role_name']) || strlen($roleData['role_name']) > 50) {
            throw new Exception('Role name must be 1-50 characters');
        }

        // Validate description for add and update actions
        if (in_array($action, ['add', 'update'])) {
            if (empty($roleData['description']) || strlen($roleData['description']) > 255) {
                throw new Exception('Role description must be 1-255 characters');
            }
        }

        // Prevent modification of system-critical roles
        $protectedRoles = ['admin', 'registrar', 'system'];
        if (in_array(strtolower($roleData['role_name']), $protectedRoles)) {
            throw new Exception('Cannot modify system-critical roles');
        }
    }
} 