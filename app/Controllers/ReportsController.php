<?php

namespace App\Controllers;

use App\Repositories\ReportsRepository;
use App\Services\AuthService;
use Exception;

class ReportsController
{
    private ReportsRepository $reportsRepository;
    private AuthService $authService;

    public function __construct(
        ReportsRepository $reportsRepository,
        AuthService $authService
    ) {
        $this->reportsRepository = $reportsRepository;
        $this->authService = $authService;
    }

    /**
     * Generate application status report
     * 
     * @param string $startDate Start date for report
     * @param string $endDate End date for report
     * @return array Application status report
     * @throws Exception
     */
    public function generateApplicationStatusReport(string $startDate, string $endDate): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            // Validate date range
            $this->validateDateRange($startDate, $endDate);

            $report = $this->reportsRepository->getApplicationStatusReport($startDate, $endDate);
            
            return $report;
        } catch (Exception $e) {
            error_log('Application Status Report Generation Error: ' . $e->getMessage());
            throw $e;
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
    public function generateCertificateIssuanceReport(string $startDate, string $endDate): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            // Validate date range
            $this->validateDateRange($startDate, $endDate);

            $report = $this->reportsRepository->getCertificateIssuanceReport($startDate, $endDate);
            
            return $report;
        } catch (Exception $e) {
            error_log('Certificate Issuance Report Generation Error: ' . $e->getMessage());
            throw $e;
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
    public function generateRegistrarPerformanceReport(string $startDate, string $endDate): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['admin']);

        try {
            // Validate date range
            $this->validateDateRange($startDate, $endDate);

            $report = $this->reportsRepository->getRegistrarPerformanceReport($startDate, $endDate);
            
            return $report;
        } catch (Exception $e) {
            error_log('Registrar Performance Report Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export report to CSV
     * 
     * @param string $reportType Type of report to export
     * @param string $startDate Start date for report
     * @param string $endDate End date for report
     * @return string Path to generated CSV file
     * @throws Exception
     */
    public function exportReportToCSV(string $reportType, string $startDate, string $endDate): string
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            // Validate date range
            $this->validateDateRange($startDate, $endDate);

            // Determine report data based on type
            switch ($reportType) {
                case 'application_status':
                    $reportData = $this->generateApplicationStatusReport($startDate, $endDate);
                    $filename = 'application_status_report';
                    break;
                
                case 'certificate_issuance':
                    $reportData = $this->generateCertificateIssuanceReport($startDate, $endDate);
                    $filename = 'certificate_issuance_report';
                    break;
                
                case 'registrar_performance':
                    $reportData = $this->generateRegistrarPerformanceReport($startDate, $endDate);
                    $filename = 'registrar_performance_report';
                    break;
                
                default:
                    throw new Exception('Invalid report type');
            }

            // Export to CSV
            return $this->reportsRepository->exportReportToCSV($reportData, $filename);
        } catch (Exception $e) {
            error_log('Report Export Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @throws Exception
     */
    private function validateDateRange(string $startDate, string $endDate): void
    {
        // Validate date format (assuming YYYY-MM-DD)
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        if ($startTimestamp === false || $endTimestamp === false) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD');
        }

        if ($startTimestamp > $endTimestamp) {
            throw new Exception('Start date must be before or equal to end date');
        }

        // Limit report range to prevent excessive processing
        $maxReportRange = 365 * 24 * 60 * 60; // 1 year in seconds
        if ($endTimestamp - $startTimestamp > $maxReportRange) {
            throw new Exception('Report date range cannot exceed 1 year');
        }
    }

    /**
     * Generate daily report with specified date parameter
     */
    public function daily()
    {
        // Check if user is logged in with appropriate role
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
        
        // Get report date parameter
        $date = $_GET['date'] ?? date('Y-m-d');
        
        try {
            // Validate date format
            $this->validateDateRange($date, $date);
            
            // Generate report data
            $reportData = [];
            
            if ($_SESSION['role'] === 'admin') {
                // Admin gets comprehensive report
                $reportData['applications'] = $this->generateApplicationStatusReport($date, $date);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($date, $date);
                $reportData['performance'] = $this->generateRegistrarPerformanceReport($date, $date);
            } else {
                // Registrar gets more limited report
                $reportData['applications'] = $this->generateApplicationStatusReport($date, $date);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($date, $date);
            }
            
            // Set page title and report metadata
            $pageTitle = 'Daily Report: ' . date('F j, Y', strtotime($date));
            $reportType = 'daily';
            $reportDate = $date;
            
            // Include the report view
            include BASE_PATH . '/resources/views/reports/daily.php';
            
        } catch (Exception $e) {
            error_log("Daily Report Generation Error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to generate report. Please try again or contact support.';
            header('Location: /dashboard/reports');
            exit;
        }
    }
    
    /**
     * Generate weekly report with specified date parameter
     */
    public function weekly()
    {
        // Check if user is logged in with appropriate role
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
        
        // Get report date parameter (any day within the week)
        $date = $_GET['date'] ?? date('Y-m-d');
        
        try {
            // Calculate the start and end of the week containing the given date
            $dayOfWeek = date('N', strtotime($date));
            $startDate = date('Y-m-d', strtotime("-" . ($dayOfWeek - 1) . " days", strtotime($date)));
            $endDate = date('Y-m-d', strtotime("+" . (7 - $dayOfWeek) . " days", strtotime($date)));
            
            // Validate date range
            $this->validateDateRange($startDate, $endDate);
            
            // Generate report data
            $reportData = [];
            
            if ($_SESSION['role'] === 'admin') {
                // Admin gets comprehensive report
                $reportData['applications'] = $this->generateApplicationStatusReport($startDate, $endDate);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($startDate, $endDate);
                $reportData['performance'] = $this->generateRegistrarPerformanceReport($startDate, $endDate);
            } else {
                // Registrar gets more limited report
                $reportData['applications'] = $this->generateApplicationStatusReport($startDate, $endDate);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($startDate, $endDate);
            }
            
            // Set page title and report metadata
            $pageTitle = 'Weekly Report: ' . date('F j', strtotime($startDate)) . ' - ' . date('F j, Y', strtotime($endDate));
            $reportType = 'weekly';
            $reportDate = $date;
            $weekStartDate = $startDate;
            $weekEndDate = $endDate;
            
            // Include the report view
            include BASE_PATH . '/resources/views/reports/weekly.php';
            
        } catch (Exception $e) {
            error_log("Weekly Report Generation Error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to generate report. Please try again or contact support.';
            header('Location: /dashboard/reports');
            exit;
        }
    }
    
    /**
     * Generate monthly report with specified date parameter
     */
    public function monthly()
    {
        // Check if user is logged in with appropriate role
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
        
        // Get report month and year parameters
        $month = intval($_GET['month'] ?? date('m'));
        $year = intval($_GET['year'] ?? date('Y'));
        
        try {
            // Calculate start and end dates for the month
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));
            
            // Validate date range
            $this->validateDateRange($startDate, $endDate);
            
            // Generate report data
            $reportData = [];
            
            if ($_SESSION['role'] === 'admin') {
                // Admin gets comprehensive report
                $reportData['applications'] = $this->generateApplicationStatusReport($startDate, $endDate);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($startDate, $endDate);
                $reportData['performance'] = $this->generateRegistrarPerformanceReport($startDate, $endDate);
                $reportData['trends'] = $this->generateMonthlyTrends($month, $year);
            } else {
                // Registrar gets more limited report
                $reportData['applications'] = $this->generateApplicationStatusReport($startDate, $endDate);
                $reportData['certificates'] = $this->generateCertificateIssuanceReport($startDate, $endDate);
                $reportData['trends'] = $this->generateMonthlyTrends($month, $year);
            }
            
            // Set page title and report metadata
            $pageTitle = 'Monthly Report: ' . date('F Y', strtotime($startDate));
            $reportType = 'monthly';
            $reportMonth = $month;
            $reportYear = $year;
            
            // Include the report view
            include BASE_PATH . '/resources/views/reports/monthly.php';
            
        } catch (Exception $e) {
            error_log("Monthly Report Generation Error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to generate report. Please try again or contact support.';
            header('Location: /dashboard/reports');
            exit;
        }
    }
    
    /**
     * Generate monthly trends data
     */
    private function generateMonthlyTrends($month, $year)
    {
        try {
            $reportsRepository = new \App\Repositories\ReportsRepository();
            return $reportsRepository->getMonthlyTrends($month, $year);
        } catch (Exception $e) {
            error_log("Monthly Trends Error: " . $e->getMessage());
            return [];
        }
    }
} 