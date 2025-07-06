<?php

namespace App\Repositories;

use PDO;
use Exception;

class ApprovedCertificatesRepository extends BaseRepository
{
    /**
     * Get approved birth certificates with pagination
     * 
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @param array $filters Optional filters for searching
     * @return array Approved certificates with pagination
     * @throws Exception
     */
    public function getApprovedCertificates(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Base query with optional filters
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['name'])) {
                $whereConditions[] = "applicant_name LIKE :name";
                $params[':name'] = "%{$filters['name']}%";
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "issued_date >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "issued_date <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            // Total count query
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM birth_certificates 
                $whereClause
            ";
            $countStmt = $this->pdo->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Certificates query
            $query = "
                SELECT 
                    id,
                    certificate_number,
                    applicant_name,
                    date_of_birth,
                    place_of_birth,
                    issued_date,
                    issued_by
                FROM 
                    birth_certificates
                $whereClause
                ORDER BY 
                    issued_date DESC
                LIMIT :perPage OFFSET :offset
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return [
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'certificates' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            error_log('Approved Certificates Error: ' . $e->getMessage());
            throw new Exception('Unable to retrieve approved certificates', 500);
        }
    }

    /**
     * Generate a unique certificate number
     * 
     * @return string Unique certificate number
     * @throws Exception
     */
    public function generateCertificateNumber(): string
    {
        try {
            $prefix = date('Y'); // Year-based prefix
            $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            $checkQuery = "SELECT COUNT(*) as count FROM birth_certificates WHERE certificate_number = :number";
            $checkStmt = $this->pdo->prepare($checkQuery);
            
            do {
                $certificateNumber = $prefix . $randomPart;
                $checkStmt->bindParam(':number', $certificateNumber);
                $checkStmt->execute();
                $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Regenerate if number exists
                $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            } while ($count > 0);
            
            return $certificateNumber;
        } catch (Exception $e) {
            error_log('Certificate Number Generation Error: ' . $e->getMessage());
            throw new Exception('Unable to generate certificate number', 500);
        }
    }

    /**
     * Issue a new birth certificate
     * 
     * @param array $certificateData Certificate details
     * @return int Inserted certificate ID
     * @throws Exception
     */
    public function issueCertificate(array $certificateData): int
    {
        try {
            $query = "
                INSERT INTO birth_certificates (
                    application_id,
                    certificate_number,
                    applicant_name,
                    date_of_birth,
                    place_of_birth,
                    father_name,
                    mother_name,
                    issued_date,
                    issued_by
                ) VALUES (
                    :application_id,
                    :certificate_number,
                    :applicant_name,
                    :date_of_birth,
                    :place_of_birth,
                    :father_name,
                    :mother_name,
                    NOW(),
                    :issued_by
                )
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':application_id', $certificateData['application_id'], PDO::PARAM_INT);
            $stmt->bindParam(':certificate_number', $certificateData['certificate_number']);
            $stmt->bindParam(':applicant_name', $certificateData['applicant_name']);
            $stmt->bindParam(':date_of_birth', $certificateData['date_of_birth']);
            $stmt->bindParam(':place_of_birth', $certificateData['place_of_birth']);
            $stmt->bindParam(':father_name', $certificateData['father_name']);
            $stmt->bindParam(':mother_name', $certificateData['mother_name']);
            $stmt->bindParam(':issued_by', $certificateData['issued_by'], PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log('Certificate Issuance Error: ' . $e->getMessage());
            throw new Exception('Unable to issue birth certificate', 500);
        }
    }
} 