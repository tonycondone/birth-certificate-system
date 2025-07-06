<?php

namespace App\Controllers;

use App\Repositories\ApprovedCertificatesRepository;
use App\Repositories\PendingReviewsRepository;
use App\Services\AuthService;
use App\Services\PDFGenerationService;
use Exception;

class ApprovedCertificatesController
{
    private ApprovedCertificatesRepository $approvedCertificatesRepository;
    private PendingReviewsRepository $pendingReviewsRepository;
    private AuthService $authService;
    private PDFGenerationService $pdfGenerationService;

    public function __construct(
        ApprovedCertificatesRepository $approvedCertificatesRepository,
        PendingReviewsRepository $pendingReviewsRepository,
        AuthService $authService,
        PDFGenerationService $pdfGenerationService
    ) {
        $this->approvedCertificatesRepository = $approvedCertificatesRepository;
        $this->pendingReviewsRepository = $pendingReviewsRepository;
        $this->authService = $authService;
        $this->pdfGenerationService = $pdfGenerationService;
    }

    /**
     * Get paginated list of approved certificates
     * 
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @param array $filters Optional filters for searching
     * @return array Approved certificates with pagination
     * @throws Exception
     */
    public function getApprovedCertificates(
        int $page = 1, 
        int $perPage = 20, 
        array $filters = []
    ): array {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            return $this->approvedCertificatesRepository->getApprovedCertificates(
                $page, 
                $perPage, 
                $filters
            );
        } catch (Exception $e) {
            error_log('Get Approved Certificates Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Issue a new birth certificate
     * 
     * @param int $applicationId Application ID to issue certificate for
     * @return int Issued certificate ID
     * @throws Exception
     */
    public function issueCertificate(int $applicationId): int
    {
        // Ensure user is authorized
        $currentUser = $this->authService->requireRole(['registrar', 'admin']);

        try {
            // First, retrieve the application details
            $applicationQuery = "
                SELECT 
                    id,
                    applicant_name,
                    date_of_birth,
                    place_of_birth,
                    father_name,
                    mother_name
                FROM 
                    birth_applications
                WHERE 
                    id = :applicationId AND status = 'approved'
            ";
            
            $stmt = $this->approvedCertificatesRepository->getPdo()->prepare($applicationQuery);
            $stmt->bindParam(':applicationId', $applicationId, PDO::PARAM_INT);
            $stmt->execute();
            $application = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$application) {
                throw new Exception('Application not found or not approved');
            }

            // Generate unique certificate number
            $certificateNumber = $this->approvedCertificatesRepository->generateCertificateNumber();

            // Prepare certificate data
            $certificateData = [
                'application_id' => $applicationId,
                'certificate_number' => $certificateNumber,
                'applicant_name' => $application['applicant_name'],
                'date_of_birth' => $application['date_of_birth'],
                'place_of_birth' => $application['place_of_birth'],
                'father_name' => $application['father_name'],
                'mother_name' => $application['mother_name'],
                'issued_by' => $currentUser['id']
            ];

            // Issue the certificate
            $certificateId = $this->approvedCertificatesRepository->issueCertificate($certificateData);

            // Generate PDF certificate
            $this->pdfGenerationService->generateBirthCertificate($certificateData);

            return $certificateId;
        } catch (Exception $e) {
            error_log('Issue Certificate Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Download birth certificate PDF
     * 
     * @param string $certificateNumber Certificate number
     * @return string Path to generated PDF
     * @throws Exception
     */
    public function downloadCertificate(string $certificateNumber): string
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            // Retrieve certificate details
            $query = "
                SELECT 
                    certificate_number,
                    applicant_name,
                    date_of_birth,
                    place_of_birth,
                    father_name,
                    mother_name,
                    issued_date
                FROM 
                    birth_certificates
                WHERE 
                    certificate_number = :certificateNumber
            ";
            
            $stmt = $this->approvedCertificatesRepository->getPdo()->prepare($query);
            $stmt->bindParam(':certificateNumber', $certificateNumber);
            $stmt->execute();
            $certificateData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$certificateData) {
                throw new Exception('Certificate not found');
            }

            // Generate PDF
            return $this->pdfGenerationService->generateBirthCertificate($certificateData);
        } catch (Exception $e) {
            error_log('Download Certificate Error: ' . $e->getMessage());
            throw $e;
        }
    }
} 