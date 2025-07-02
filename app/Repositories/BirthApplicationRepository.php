<?php

namespace App\Repositories;

use PDO;
use Exception;

class BirthApplicationRepository extends BaseRepository
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->table = 'birth_applications';
    }

    /**
     * Find birth applications by status
     * 
     * @param string $status
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = ? LIMIT ? OFFSET ?");
            $stmt->execute([$status, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding birth applications by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update application status
     * 
     * @param int $applicationId
     * @param string $newStatus
     * @return bool
     */
    public function updateStatus(int $applicationId, string $newStatus): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
            return $stmt->execute([$newStatus, $applicationId]);
        } catch (Exception $e) {
            error_log("Error updating birth application status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find applications by parent ID
     * 
     * @param int $parentId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findByParentId(int $parentId, int $limit = 100, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE parent_id = ? LIMIT ? OFFSET ?");
            $stmt->execute([$parentId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding birth applications by parent ID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if an application is unique based on certain criteria
     * 
     * @param array $criteria
     * @return bool
     */
    public function isUniqueApplication(array $criteria): bool
    {
        try {
            $whereClause = [];
            $values = [];
            foreach ($criteria as $key => $value) {
                $whereClause[] = "$key = ?";
                $values[] = $value;
            }

            $query = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);
            
            return $stmt->fetchColumn() === 0;
        } catch (Exception $e) {
            error_log("Error checking unique application: " . $e->getMessage());
            return false;
        }
    }
} 