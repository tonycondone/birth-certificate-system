<?php

namespace App\Repositories;

use PDO;
use Exception;
use DateTime;

class ReportsRepository extends BaseRepository
{
    /**
     * Generate application status report
     * 
     * @param string $startDate Start date for report
     * @param string $endDate End date for report
     * @return array Application status report
     * @throws Exception
     */
    public function getApplicationStatusReport(string $startDate, string $endDate): array
    {
        try {
            $query = "
                SELECT 
                    status,
                    COUNT(*) as total_count,
                    ROUND(COUNT(*) * 100.0 / (
                        SELECT COUNT(*) 
                        FROM birth_applications 
                        WHERE created_at BETWEEN :start_date AND :end_date
                    ), 2) as percentage
                FROM 
                    birth_applications
                WHERE 
                    created_at BETWEEN :start_date AND :end_date
                GROUP BY 
                    status
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Application Status Report Error: ' . $e->getMessage());
            throw new Exception('Unable to generate application status report', 500);
        }
    }

    /**
     * Generate certificate issuance report
     * 
     * @param string $startDate Start date for report
     * @param string $endDate End date for report
     * @return array Certificate issuance report
     * @throws Exception
     */
    public function getCertificateIssuanceReport(string $startDate, string $endDate): array
    {
        try {
            $query = "
                SELECT 
                    DATE(issued_date) as issue_date,
                    COUNT(*) as certificates_issued,
                    SUM(CASE WHEN applicant_gender = 'male' THEN 1 ELSE 0 END) as male_certificates,
                    SUM(CASE WHEN applicant_gender = 'female' THEN 1 ELSE 0 END) as female_certificates
                FROM 
                    birth_certificates
                WHERE 
                    issued_date BETWEEN :start_date AND :end_date
                GROUP BY 
                    DATE(issued_date)
                ORDER BY 
                    issue_date
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Certificate Issuance Report Error: ' . $e->getMessage());
            throw new Exception('Unable to generate certificate issuance report', 500);
        }
    }

    /**
     * Generate registrar performance report
     * 
     * @param string $startDate Start date for report
     * @param string $endDate End date for report
     * @return array Registrar performance report
     * @throws Exception
     */
    public function getRegistrarPerformanceReport(string $startDate, string $endDate): array
    {
        try {
            $query = "
                SELECT 
                    u.id as registrar_id,
                    u.name as registrar_name,
                    COUNT(ba.id) as total_applications,
                    SUM(CASE WHEN ba.status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN ba.status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications,
                    ROUND(SUM(CASE WHEN ba.status = 'approved' THEN 1 ELSE 0 END) * 100.0 / COUNT(ba.id), 2) as approval_rate
                FROM 
                    users u
                LEFT JOIN 
                    birth_applications ba ON u.id = ba.processed_by 
                    AND ba.processed_at BETWEEN :start_date AND :end_date
                WHERE 
                    u.role = 'registrar'
                GROUP BY 
                    u.id, u.name
                ORDER BY 
                    total_applications DESC
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Registrar Performance Report Error: ' . $e->getMessage());
            throw new Exception('Unable to generate registrar performance report', 500);
        }
    }

    /**
     * Export report data to CSV
     * 
     * @param array $reportData Report data to export
     * @param string $filename Filename for export
     * @return string Path to generated CSV file
     * @throws Exception
     */
    public function exportReportToCSV(array $reportData, string $filename): string
    {
        try {
            $uploadDir = '/tmp/reports/';
            
            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . $filename . '_' . date('YmdHis') . '.csv';
            
            $file = fopen($filePath, 'w');
            
            // Write headers
            if (!empty($reportData)) {
                fputcsv($file, array_keys($reportData[0]));
                
                // Write data
                foreach ($reportData as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
            
            return $filePath;
        } catch (Exception $e) {
            error_log('CSV Export Error: ' . $e->getMessage());
            throw new Exception('Unable to export report to CSV', 500);
        }
    }
} 