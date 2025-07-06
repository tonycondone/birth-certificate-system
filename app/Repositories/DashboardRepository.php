<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;
use Exception;

/**
 * DashboardRepository
 * 
 * Handles dashboard-related data operations
 */
class DashboardRepository
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (Exception $e) {
            error_log("DashboardRepository initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Get dashboard statistics
     * 
     * @return array Dashboard statistics
     */
    public function getDashboardStatistics(): array
    {
        if (!$this->db) {
            return $this->getDefaultStatistics();
        }

        try {
            $stats = [];
            
            // Get total users
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $stmt->fetchColumn() ?: 0;
            
            // Get total applications
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM applications");
            $stats['total_applications'] = $stmt->fetchColumn() ?: 0;
            
            // Get pending applications
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'");
            $stats['pending_applications'] = $stmt->fetchColumn() ?: 0;
            
            // Get approved applications
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM applications WHERE status = 'approved'");
            $stats['approved_applications'] = $stmt->fetchColumn() ?: 0;
            
            // Get today's applications
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM applications WHERE DATE(created_at) = CURDATE()");
            $stats['today_applications'] = $stmt->fetchColumn() ?: 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting dashboard statistics: " . $e->getMessage());
            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get recent activities
     * 
     * @param int $limit Number of activities to retrieve
     * @return array Recent activities
     */
    public function getRecentActivities(int $limit = 10): array
    {
        if (!$this->db) {
            return $this->getDefaultActivities();
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activities 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return $this->getDefaultActivities();
        }
    }

    /**
     * Get default statistics when database is unavailable
     * 
     * @return array Default statistics
     */
    private function getDefaultStatistics(): array
    {
        return [
            'total_users' => 0,
            'total_applications' => 0,
            'pending_applications' => 0,
            'approved_applications' => 0,
            'today_applications' => 0
        ];
    }

    /**
     * Get default activities when database is unavailable
     * 
     * @return array Default activities
     */
    private function getDefaultActivities(): array
    {
        return [
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'system_start',
                'description' => 'System initialized',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Get user statistics by role
     * 
     * @return array User statistics
     */
    public function getUserStatistics(): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->query("
                SELECT role, COUNT(*) as count 
                FROM users 
                GROUP BY role
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get application trends
     * 
     * @param int $days Number of days to look back
     * @return array Application trends
     */
    public function getApplicationTrends(int $days = 30): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM applications
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting application trends: " . $e->getMessage());
            return [];
        }
    }
}
