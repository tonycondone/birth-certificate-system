<?php

namespace App\Repositories;

use PDO;
use Exception;

class SettingsRepository extends BaseRepository
{
    /**
     * Get system configuration settings
     * 
     * @return array System configuration settings
     * @throws Exception
     */
    public function getSystemSettings(): array
    {
        try {
            $query = "
                SELECT 
                    setting_key,
                    setting_value,
                    description
                FROM 
                    system_settings
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform to key-value array
            $settingsMap = [];
            foreach ($settings as $setting) {
                $settingsMap[$setting['setting_key']] = [
                    'value' => $setting['setting_value'],
                    'description' => $setting['description']
                ];
            }
            
            return $settingsMap;
        } catch (Exception $e) {
            error_log('System Settings Retrieval Error: ' . $e->getMessage());
            throw new Exception('Unable to retrieve system settings', 500);
        }
    }

    /**
     * Update system configuration settings
     * 
     * @param array $settings Settings to update
     * @param int $userId User updating the settings
     * @return bool Success status
     * @throws Exception
     */
    public function updateSystemSettings(array $settings, int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $updateQuery = "
                UPDATE system_settings 
                SET 
                    setting_value = :value, 
                    updated_by = :userId, 
                    updated_at = NOW()
                WHERE 
                    setting_key = :key
            ";
            
            $stmt = $this->pdo->prepare($updateQuery);
            
            foreach ($settings as $key => $value) {
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('System Settings Update Error: ' . $e->getMessage());
            throw new Exception('Unable to update system settings', 500);
        }
    }

    /**
     * Manage user roles and permissions
     * 
     * @param string $action Action to perform (add, remove, update)
     * @param array $roleData Role details
     * @param int $userId User performing the action
     * @return bool Success status
     * @throws Exception
     */
    public function manageUserRoles(string $action, array $roleData, int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            switch ($action) {
                case 'add':
                    $query = "
                        INSERT INTO user_roles 
                        (role_name, description, created_by, created_at) 
                        VALUES 
                        (:role_name, :description, :userId, NOW())
                    ";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindParam(':role_name', $roleData['role_name']);
                    $stmt->bindParam(':description', $roleData['description']);
                    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    break;
                
                case 'update':
                    $query = "
                        UPDATE user_roles 
                        SET 
                            description = :description, 
                            updated_by = :userId, 
                            updated_at = NOW()
                        WHERE 
                            role_name = :role_name
                    ";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindParam(':role_name', $roleData['role_name']);
                    $stmt->bindParam(':description', $roleData['description']);
                    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    break;
                
                case 'remove':
                    $query = "
                        DELETE FROM user_roles 
                        WHERE 
                            role_name = :role_name
                    ";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindParam(':role_name', $roleData['role_name']);
                    break;
                
                default:
                    throw new Exception('Invalid role management action');
            }
            
            $result = $stmt->execute();
            $this->pdo->commit();
            
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('User Roles Management Error: ' . $e->getMessage());
            throw new Exception('Unable to manage user roles', 500);
        }
    }

    /**
     * Audit system configuration changes
     * 
     * @param int $userId User making the changes
     * @param string $action Action performed
     * @param array $details Change details
     * @return bool Success status
     * @throws Exception
     */
    public function auditConfigurationChange(int $userId, string $action, array $details): bool
    {
        try {
            $query = "
                INSERT INTO system_configuration_audit 
                (user_id, action, details, created_at) 
                VALUES 
                (:userId, :action, :details, NOW())
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':details', json_encode($details));
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Configuration Change Audit Error: ' . $e->getMessage());
            throw new Exception('Unable to log configuration change', 500);
        }
    }
} 