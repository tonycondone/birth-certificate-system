<?php

namespace App\Repositories;

use PDO;
use Exception;

class CertificateRepository extends BaseRepository
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->table = 'certificates';
    }

    /**
     * Find certificates by birth application ID
     * 
     * @param int $birthApplicationId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findByBirthApplicationId(int $birthApplicationId, int $limit = 100, int $offset = 0): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE birth_application_id = ? LIMIT ? OFFSET ?");
            $stmt->execute([$birthApplicationId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding certificates by birth application ID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find certificates by status
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
            error_log("Error finding certificates by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update certificate status
     * 
     * @param int $certificateId
     * @param string $newStatus
     * @return bool
     */
    public function updateStatus(int $certificateId, string $newStatus): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
            return $stmt->execute([$newStatus, $certificateId]);
        } catch (Exception $e) {
            error_log("Error updating certificate status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify certificate authenticity
     * 
     * @param string $certificateNumber
     * @return bool
     */
    public function verifyCertificate(string $certificateNumber): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE certificate_number = ? AND status = 'VERIFIED'");
            $stmt->execute([$certificateNumber]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error verifying certificate: " . $e->getMessage());
            return false;
        }
    }
} 