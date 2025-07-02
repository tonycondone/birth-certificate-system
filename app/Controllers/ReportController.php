<?php
namespace App\Controllers;

use App\Database\Database;

/**
 * Class ReportController
 *
 * Handles generation and display of reports such as
 * application summaries, certificates issued, processing times,
 * and user activity. Supports CSV export and quick statistics.
 */
class ReportController {
    /**
     * Display the report dashboard with report filters,
     * process report requests, and handle CSV exports.
     *
     * Access restricted to admin and registrar roles.
     *
     * @return void
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        if (!in_array($_SESSION['role'], ['admin', 'registrar'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $pdo = Database::getConnection();
        $reports = [];
        $quickStats = [];
        
        // Get date range from request
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'] ?? '';
            $format = $_POST['format'] ?? 'html';
            
            switch ($reportType) {
                case 'applications_summary':
                    $reports['applications_summary'] = $this->generateApplicationsSummary($pdo, $startDate, $endDate);
                    break;
                case 'certificates_issued':
                    $reports['certificates_issued'] = $this->generateCertificatesIssued($pdo, $startDate, $endDate);
                    break;
                case 'processing_times':
                    $reports['processing_times'] = $this->generateProcessingTimes($pdo, $startDate, $endDate);
                    break;
                case 'user_activity':
                    $reports['user_activity'] = $this->generateUserActivity($pdo, $startDate, $endDate);
                    break;
            }
            
            if ($format === 'csv') {
                $this->generateCSVReport($reports, $reportType);
                return;
            }
        }
        
        // Get quick statistics
        $quickStats = $this->getQuickStats($pdo);
        
        include BASE_PATH . '/resources/views/reports/index.php';
    }
    
    private function generateApplicationsSummary($pdo, $startDate, $endDate) {
        try {
            $stmt = $pdo->prepare('
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected
                FROM birth_applications 
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ');
            $stmt->execute([$startDate, $endDate . ' 23:59:59']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Applications summary error: " . $e->getMessage());
            return [];
        }
    }
    
    private function generateCertificatesIssued($pdo, $startDate, $endDate) {
        try {
            $stmt = $pdo->prepare('
                SELECT 
                    c.certificate_number,
                    ba.child_name,
                    ba.date_of_birth,
                    ba.gender,
                    ba.place_of_birth,
                    c.issued_at
                FROM certificates c 
                JOIN birth_applications ba ON c.application_id = ba.id 
                WHERE c.issued_at BETWEEN ? AND ?
                ORDER BY c.issued_at DESC
            ');
            $stmt->execute([$startDate, $endDate . ' 23:59:59']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Certificates issued error: " . $e->getMessage());
            return [];
        }
    }
    
    private function generateProcessingTimes($pdo, $startDate, $endDate) {
        try {
            $stmt = $pdo->prepare('
                SELECT 
                    ba.id,
                    ba.child_name,
                    ba.created_at as application_date,
                    c.issued_at as approval_date,
                    DATEDIFF(c.issued_at, ba.created_at) as processing_days
                FROM birth_applications ba 
                JOIN certificates c ON ba.id = c.application_id 
                WHERE ba.created_at BETWEEN ? AND ?
                ORDER BY processing_days DESC
            ');
            $stmt->execute([$startDate, $endDate . ' 23:59:59']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Processing times error: " . $e->getMessage());
            return [];
        }
    }
    
    private function generateUserActivity($pdo, $startDate, $endDate) {
        try {
            $stmt = $pdo->prepare('
                SELECT 
                    u.email,
                    u.role,
                    COUNT(ba.id) as applications_submitted
                FROM users u 
                LEFT JOIN birth_applications ba ON u.id = ba.user_id 
                    AND ba.created_at BETWEEN ? AND ?
                GROUP BY u.id, u.email, u.role
                ORDER BY applications_submitted DESC
            ');
            $stmt->execute([$startDate, $endDate . ' 23:59:59']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("User activity error: " . $e->getMessage());
            return [];
        }
    }
    
    private function getQuickStats($pdo) {
        $stats = [];
        
        try {
            // Total applications today
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM birth_applications WHERE DATE(created_at) = CURDATE()');
            $stmt->execute();
            $stats['applications_today'] = $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            $stats['applications_today'] = 0;
        }
        
        try {
            // Total certificates issued today
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM certificates WHERE DATE(issued_at) = CURDATE()');
            $stmt->execute();
            $stats['certificates_today'] = $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            $stats['certificates_today'] = 0;
        }
        
        try {
            // Pending applications
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM birth_applications WHERE status = "pending"');
            $stmt->execute();
            $stats['pending_applications'] = $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            $stats['pending_applications'] = 0;
        }
        
        try {
            // Average processing time
            $stmt = $pdo->prepare('
                SELECT AVG(DATEDIFF(c.issued_at, ba.created_at)) as avg_days
                FROM birth_applications ba 
                JOIN certificates c ON ba.id = c.application_id 
                WHERE ba.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ');
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['avg_processing_days'] = round($result['avg_days'] ?? 0, 1);
        } catch (Exception $e) {
            $stats['avg_processing_days'] = 0;
        }
        
        try {
            // Monthly trend
            $stmt = $pdo->prepare('
                SELECT 
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as applications
                FROM birth_applications 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, "%Y-%m")
                ORDER BY month DESC
            ');
            $stmt->execute();
            $stats['monthly_trend'] = $stmt->fetchAll();
        } catch (Exception $e) {
            $stats['monthly_trend'] = [];
        }
        
        return $stats;
    }
    
    private function generateCSVReport($reports, $reportType) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . $reportType . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($reports[$reportType])) {
            // Write headers
            fputcsv($output, array_keys($reports[$reportType][0]));
            
            // Write data
            foreach ($reports[$reportType] as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
    
    public function exportData() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized';
            return;
        }
        
        $pdo = Database::getConnection();
        $dataType = $_GET['type'] ?? '';
        
        switch ($dataType) {
            case 'applications':
                $this->exportApplications($pdo);
                break;
            case 'certificates':
                $this->exportCertificates($pdo);
                break;
            case 'users':
                $this->exportUsers($pdo);
                break;
            default:
                http_response_code(400);
                echo 'Invalid data type';
        }
    }
    
    private function exportApplications($pdo) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="applications_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        try {
            $stmt = $pdo->prepare('
                SELECT ba.*, u.email as applicant_email
                FROM birth_applications ba 
                JOIN users u ON ba.user_id = u.id 
                ORDER BY ba.created_at DESC
            ');
            $stmt->execute();
            
            $applications = $stmt->fetchAll();
            
            if (!empty($applications)) {
                fputcsv($output, array_keys($applications[0]));
                foreach ($applications as $row) {
                    fputcsv($output, $row);
                }
            }
        } catch (Exception $e) {
            error_log("Export applications error: " . $e->getMessage());
        }
        
        fclose($output);
    }
    
    private function exportCertificates($pdo) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="certificates_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        try {
            $stmt = $pdo->prepare('
                SELECT c.*, ba.child_name, ba.date_of_birth, ba.gender, ba.place_of_birth,
                       ba.mother_name, ba.father_name
                FROM certificates c 
                JOIN birth_applications ba ON c.application_id = ba.id 
                ORDER BY c.issued_at DESC
            ');
            $stmt->execute();
            
            $certificates = $stmt->fetchAll();
            
            if (!empty($certificates)) {
                fputcsv($output, array_keys($certificates[0]));
                foreach ($certificates as $row) {
                    fputcsv($output, $row);
                }
            }
        } catch (Exception $e) {
            error_log("Export certificates error: " . $e->getMessage());
        }
        
        fclose($output);
    }
    
    private function exportUsers($pdo) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        try {
            $stmt = $pdo->prepare('SELECT id, email, role, first_name, last_name, created_at FROM users ORDER BY created_at DESC');
            $stmt->execute();
            
            $users = $stmt->fetchAll();
            
            if (!empty($users)) {
                fputcsv($output, array_keys($users[0]));
                foreach ($users as $row) {
                    fputcsv($output, $row);
                }
            }
        } catch (Exception $e) {
            error_log("Export users error: " . $e->getMessage());
        }
        
        fclose($output);
    }
} 