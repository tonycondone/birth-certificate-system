<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database\Database;
use App\Models\Certificate;
use App\Models\BirthApplication;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CertificateVerificationService;
use App\Services\QRCodeService;
use App\Services\RateLimitService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use PDO;
use Exception;
use DateTime;

/**
 * Modern Certificate Controller
 *
 * Handles all certificate-related operations including:
 * - Certificate verification and validation
 * - QR code verification
 * - Batch verification
 * - Verification analytics
 * - PDF generation and downloads
 */
readonly class CertificateController
{
    public function __construct(
        private PDO $db,
        private AuthService $authService,
        private CertificateVerificationService $verificationService,
        private QRCodeService $qrCodeService,
        private RateLimitService $rateLimitService
    ) {}

    /**
     * Display the certificate verification dashboard for registrars/admins
     */
    public function showVerificationDashboard(): void
    {
        $this->ensureUserHasRole(['registrar', 'admin']);
        
        $user = $this->authService->getCurrentUser();
        $pendingApplications = $this->getPendingApplications();
        $verificationStats = $this->verificationService->getVerificationStats();
        
        $this->renderView('certificates/verify-dashboard', [
            'user' => $user,
            'pendingApplications' => $pendingApplications,
            'stats' => $verificationStats,
            'pageTitle' => 'Certificate Verification Dashboard'
        ]);
    }

    /**
     * Display public certificate verification page
     */
    public function showPublicVerify(): void
    {
        $certificateNumber = trim($_GET['certificate_number'] ?? '');
        $certificate = null;
        $error = null;

        // Rate limiting check
        if ($this->rateLimitService->isRateLimited($_SERVER['REMOTE_ADDR'], 'verify')) {
            $error = "Too many verification attempts. Please wait before trying again.";
        } elseif (!empty($certificateNumber)) {
            try {
                $validation = $this->verificationService->validateCertificate($certificateNumber);
                $certificate = $this->formatCertificateForView($validation);
            } catch (Exception $e) {
                error_log("Certificate verification error: " . $e->getMessage());
                $error = "An error occurred during verification. Please try again.";
            }
        }

        $this->renderView('verify', [
            'certificate' => $certificate,
            'error' => $error,
            'certificateNumber' => $certificateNumber,
            'pageTitle' => 'Verify Birth Certificate'
        ]);
    }

    /**
     * API endpoint for certificate verification
     */
    public function apiVerify(): void
    {
        $this->setJsonHeaders();
        
        try {
            $input = $this->getJsonInput();
            $certificateNumber = trim($input['certificate_number'] ?? '');

            if (empty($certificateNumber)) {
                $this->sendJsonError('Certificate number is required', 400);
                return;
            }

            if (!$this->isValidCertificateFormat($certificateNumber)) {
                $this->sendJsonError('Invalid certificate number format', 400);
                return;
            }

            // Rate limiting
            if ($this->rateLimitService->isRateLimited($_SERVER['REMOTE_ADDR'], 'api_verify')) {
                $this->sendJsonError('Rate limit exceeded', 429);
                return;
            }

            $validation = $this->verificationService->validateCertificate($certificateNumber);
            
            $this->sendJsonResponse([
                'success' => true,
                'valid' => $validation->valid,
                'certificate_number' => $validation->certificateNumber,
                'details' => $validation->details,
                'verification_count' => $validation->verificationCount,
                'verified_at' => $validation->lastVerifiedAt?->format('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("API verification error: " . $e->getMessage());
            $this->sendJsonError('Verification failed', 500);
        }
    }

    /**
     * QR Code verification endpoint
     */
    public function verifyQRCode(): void
    {
        $this->setJsonHeaders();
        
        try {
            $input = $this->getJsonInput();
            $qrCodeHash = trim($input['qr_hash'] ?? '');

            if (empty($qrCodeHash)) {
                $this->sendJsonError('QR code hash is required', 400);
                return;
            }

            $validation = $this->verificationService->verifyQRCode($qrCodeHash);
            
            $this->sendJsonResponse([
                'success' => true,
                'valid' => $validation->valid,
                'details' => $validation->details,
                'reason' => $validation->reason
            ]);

        } catch (Exception $e) {
            error_log("QR verification error: " . $e->getMessage());
            $this->sendJsonError('QR verification failed', 500);
        }
    }

    /**
     * Batch verification endpoint
     */
    public function batchVerify(): void
    {
        $this->setJsonHeaders();
        $this->ensureUserHasRole(['registrar', 'admin']);
        
        try {
            $input = $this->getJsonInput();
            $certificateNumbers = $input['certificate_numbers'] ?? [];

            if (empty($certificateNumbers) || !is_array($certificateNumbers)) {
                $this->sendJsonError('Certificate numbers array is required', 400);
                return;
            }

            if (count($certificateNumbers) > 100) {
                $this->sendJsonError('Maximum 100 certificates per batch', 400);
                return;
            }

            $results = $this->verificationService->batchVerify($certificateNumbers);
            
            $this->sendJsonResponse([
                'success' => true,
                'results' => $results,
                'total_processed' => count($results),
                'valid_count' => array_sum(array_map(fn($r) => $r->valid ? 1 : 0, $results))
            ]);

        } catch (Exception $e) {
            error_log("Batch verification error: " . $e->getMessage());
            $this->sendJsonError('Batch verification failed', 500);
        }
    }

    /**
     * Verify/approve a certificate application
     */
    public function verifyCertificateApplication(): void
    {
        $this->setJsonHeaders();
        $this->ensureUserHasRole(['registrar', 'admin']);
        
        try {
            $input = $this->getJsonInput();
            $applicationId = (int)($input['application_id'] ?? 0);
            $notes = trim($input['notes'] ?? '');
            $metadata = $input['metadata'] ?? [];

            if (!$applicationId) {
                $this->sendJsonError('Invalid application ID', 400);
                return;
            }

            $user = $this->authService->getCurrentUser();
            $result = $this->verificationService->verifyApplication(
                $applicationId,
                $user->getId(),
                $notes,
                $metadata
            );

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Application verified successfully',
                'certificate_number' => $result->certificateNumber,
                'qr_code_hash' => $result->qrCodeHash,
                'blockchain_hash' => $result->blockchainHash,
                'verified_at' => $result->verifiedAt->format('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Application verification error: " . $e->getMessage());
            $this->sendJsonError($e->getMessage(), 500);
        }
    }

    /**
     * Reject a certificate application
     */
    public function rejectCertificateApplication(): void
    {
        $this->setJsonHeaders();
        $this->ensureUserHasRole(['registrar', 'admin']);
        
        try {
            $input = $this->getJsonInput();
            $applicationId = (int)($input['application_id'] ?? 0);
            $reason = trim($input['reason'] ?? '');
            $metadata = $input['metadata'] ?? [];

            if (!$applicationId) {
                $this->sendJsonError('Invalid application ID', 400);
                return;
            }

            if (empty($reason)) {
                $this->sendJsonError('Rejection reason is required', 400);
                return;
            }

            $user = $this->authService->getCurrentUser();
            $result = $this->verificationService->rejectApplication(
                $applicationId,
                $user->getId(),
                $reason,
                $metadata
            );

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Application rejected successfully',
                'rejected_at' => $result->rejectedAt->format('Y-m-d H:i:s'),
                'reason' => $result->reason
            ]);

        } catch (Exception $e) {
            error_log("Application rejection error: " . $e->getMessage());
            $this->sendJsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get verification analytics
     */
    public function getVerificationAnalytics(): void
    {
        $this->setJsonHeaders();
        $this->ensureUserHasRole(['registrar', 'admin']);
        
        try {
            $from = isset($_GET['from']) ? new DateTime($_GET['from']) : new DateTime('-30 days');
            $to = isset($_GET['to']) ? new DateTime($_GET['to']) : new DateTime();
            
            $stats = $this->verificationService->getVerificationStats($from, $to);
            
            $this->sendJsonResponse([
                'success' => true,
                'stats' => [
                    'total_attempts' => $stats->totalAttempts,
                    'successful_attempts' => $stats->successfulAttempts,
                    'unique_certificates' => $stats->uniqueCertificates,
                    'unique_ips' => $stats->uniqueIPs,
                    'success_rate' => $stats->successRate,
                    'period' => [
                        'from' => $stats->period['from']->format('Y-m-d'),
                        'to' => $stats->period['to']->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (Exception $e) {
            error_log("Analytics error: " . $e->getMessage());
            $this->sendJsonError('Failed to retrieve analytics', 500);
        }
    }

    /**
     * Generate QR code for certificate
     */
    public function generateQRCode(string $certificateNumber): void
    {
        try {
            if (!$this->isValidCertificateFormat($certificateNumber)) {
                http_response_code(400);
                echo "Invalid certificate number format";
                return;
            }

            $validation = $this->verificationService->validateCertificate($certificateNumber);
            
            if (!$validation->valid) {
                http_response_code(404);
                echo "Certificate not found";
                return;
            }

            $qrCodeData = [
                'certificate_number' => $certificateNumber,
                'verification_url' => "https://{$_SERVER['HTTP_HOST']}/verify?certificate_number={$certificateNumber}",
                'issued_date' => $validation->details['issued_date'] ?? null
            ];

            $qrCode = $this->qrCodeService->generateQRCode(json_encode($qrCodeData));
            
            header('Content-Type: image/png');
            echo $qrCode;

        } catch (Exception $e) {
            error_log("QR code generation error: " . $e->getMessage());
            http_response_code(500);
            echo "QR code generation failed";
        }
    }

    // Private helper methods

    private function getPendingApplications(): array
    {
        $query = "
            SELECT 
                ba.id, 
                ba.user_id, 
                ba.child_first_name, 
                ba.child_last_name, 
                ba.child_middle_name,
                ba.date_of_birth,
                ba.place_of_birth,
                ba.gender,
                ba.status, 
                ba.created_at,
                ba.updated_at,
                u.first_name AS parent_first_name, 
                u.last_name AS parent_last_name,
                u.email AS parent_email,
                h.name AS hospital_name
            FROM 
                birth_applications ba
            JOIN 
                users u ON ba.user_id = u.id
            LEFT JOIN
                hospitals h ON ba.hospital_id = h.id
            WHERE 
                ba.status IN ('submitted', 'pending')
            ORDER BY 
                ba.created_at ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function formatCertificateForView(CertificateValidation $validation): ?array
    {
        if (!$validation->valid) {
            return [
                'is_valid' => false,
                'certificate_number' => $validation->certificateNumber,
                'reason' => $validation->reason,
                'message' => $validation->reason
            ];
        }

        $details = $validation->details;
        
        return [
            'is_valid' => true,
            'certificate_number' => $validation->certificateNumber,
            'child_name' => $details['child_name'] ?? '',
            'date_of_birth' => $details['date_of_birth'] ?? '',
            'place_of_birth' => $details['place_of_birth'] ?? '',
            'gender' => $details['gender'] ?? '',
            'mother_name' => $details['mother_name'] ?? '',
            'father_name' => $details['father_name'] ?? '',
            'issued_at' => $details['issued_date'] ?? '',
            'verification_count' => $validation->verificationCount,
            'last_verified_at' => $validation->lastVerifiedAt?->format('Y-m-d H:i:s'),
            'status' => $details['status'] ?? 'active',
            'message' => 'Certificate is valid and verified'
        ];
    }

    private function isValidCertificateFormat(string $certificateNumber): bool
    {
        return preg_match('/^BC[A-Z0-9]{12}$/', $certificateNumber) === 1;
    }

    private function ensureUserHasRole(array $allowedRoles): void
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user || !in_array($user->getRole(), $allowedRoles)) {
            if ($this->isJsonRequest()) {
                $this->sendJsonError('Insufficient permissions', 403);
            } else {
                http_response_code(403);
                $this->renderView('errors/403');
            }
            exit;
        }
    }

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
    }

    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    private function isJsonRequest(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') ||
               str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    private function sendJsonResponse(array $data): void
    {
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function sendJsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code
        ], JSON_THROW_ON_ERROR);
    }

    private function renderView(string $view, array $data = []): void
    {
        extract($data);
        
        if (file_exists(BASE_PATH . "/resources/views/{$view}.php")) {
            include BASE_PATH . "/resources/views/{$view}.php";
        } else {
            throw new Exception("View not found: {$view}");
        }
    }

    // Legacy methods for backward compatibility (to be phased out)

    /**
     * @deprecated Use showPublicVerify() instead
     */
    public function verify($certificateId = null): void
    {
        $this->showPublicVerify();
    }

    /**
     * @deprecated Use showVerificationDashboard() instead
     */
    public function showVerify(): void
    {
        $this->showVerificationDashboard();
    }

    /**
     * @deprecated Use verifyCertificateApplication() instead
     */
    public function verifyCertificate(int $applicationId): void
    {
        $_POST = json_decode(file_get_contents('php://input'), true) ?: [];
        $_POST['application_id'] = $applicationId;
        $this->verifyCertificateApplication();
    }

    /**
     * @deprecated Use rejectCertificateApplication() instead
     */
    public function rejectCertificate(int $applicationId, string $reason = ''): void
    {
        $_POST = json_decode(file_get_contents('php://input'), true) ?: [];
        $_POST['application_id'] = $applicationId;
        $_POST['reason'] = $reason;
        $this->rejectCertificateApplication();
    }
} 