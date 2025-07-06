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
} 