<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BirthApplication;
use App\Models\Certificate;
use App\Models\User;
use App\Repositories\BirthApplicationRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Services\BlockchainService;
use PDO;
use Exception;
use DateTime;

/**
 * Modern Certificate Verification Service
 * 
 * Handles all certificate verification operations including:
 * - Application verification and approval/rejection
 * - Certificate validation and authenticity checks
 * - QR code verification
 * - Batch verification
 * - Verification analytics and logging
 */
readonly class CertificateVerificationService
{
    public function __construct(
        private PDO $db,
        private BirthApplicationRepository $birthApplicationRepository,
        private CertificateRepository $certificateRepository,
        private UserRepository $userRepository,
        private NotificationService $notificationService,
        private BlockchainService $blockchainService
    ) {}

    /**
     * Verify a birth certificate application
     */
    public function verifyApplication(
        int $applicationId, 
        int $verifierId, 
        string $notes = '',
        array $metadata = []
    ): VerificationResult {
        return $this->db->beginTransaction() ? $this->processVerification(
            $applicationId, 
            $verifierId, 
            $notes, 
            $metadata
        ) : throw new Exception("Failed to start database transaction");
    }

    /**
     * Process the verification workflow
     */
    private function processVerification(
        int $applicationId, 
        int $verifierId, 
        string $notes, 
        array $metadata
    ): VerificationResult {
        try {
            $application = $this->getApplicationForVerification($applicationId);
            $certificateNumber = $this->generateSecureCertificateNumber($application);
            $qrCodeHash = $this->generateSecureQRCodeHash($certificateNumber);
            
            // Create blockchain record for immutable verification
            $blockchainHash = $this->blockchainService->createCertificateRecord([
                'certificate_number' => $certificateNumber,
                'application_id' => $applicationId,
                'verifier_id' => $verifierId,
                'timestamp' => time(),
                'metadata' => $metadata
            ]);

            $certificate = $this->createCertificate(
                $applicationId, 
                $certificateNumber, 
                $qrCodeHash, 
                $blockchainHash,
                $verifierId, 
                $notes
            );

            $this->updateApplicationStatus($application, $verifierId);
            $this->sendVerificationNotifications($application, $certificateNumber);
            $this->logVerificationActivity($applicationId, $verifierId, 'approved', $metadata);

            $this->db->commit();

            return new VerificationResult(
                success: true,
                certificateNumber: $certificateNumber,
                qrCodeHash: $qrCodeHash,
                blockchainHash: $blockchainHash,
                verifiedAt: new DateTime(),
                verifierId: $verifierId
            );
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Verification failed: " . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Reject a birth certificate application
     */
    public function rejectApplication(
        int $applicationId, 
        int $verifierId, 
        string $reason = '',
        array $metadata = []
    ): RejectionResult {
        return $this->db->beginTransaction() ? $this->processRejection(
            $applicationId, 
            $verifierId, 
            $reason, 
            $metadata
        ) : throw new Exception("Failed to start database transaction");
    }

    /**
     * Process the rejection workflow
     */
    private function processRejection(
        int $applicationId, 
        int $verifierId, 
        string $reason, 
        array $metadata
    ): RejectionResult {
        try {
            $application = $this->getApplicationForVerification($applicationId);
            
            $application->setStatus('rejected');
            $application->setRejectionReason($reason);
            $application->setReviewedAt(new DateTime());
            $application->setReviewedBy($verifierId);
            $this->birthApplicationRepository->save($application);

            $this->notificationService->sendCertificateRejectedNotification(
                $application->getUserId(), 
                $reason
            );

            $this->logVerificationActivity($applicationId, $verifierId, 'rejected', $metadata);
            $this->db->commit();

            return new RejectionResult(
                success: true,
                rejectedAt: new DateTime(),
                verifierId: $verifierId,
                reason: $reason
            );
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Rejection failed: " . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Validate certificate authenticity with comprehensive checks
     */
    public function validateCertificate(string $certificateNumber): CertificateValidation {
        try {
            $certificate = $this->certificateRepository->findByCertificateNumber($certificateNumber);
            
            if (!$certificate) {
                $this->logVerificationAttempt($certificateNumber, false, 'not_found');
                return new CertificateValidation(
                    valid: false,
                    reason: 'Certificate not found',
                    certificateNumber: $certificateNumber
                );
            }

            // Verify blockchain integrity
            $blockchainValid = $this->blockchainService->verifyCertificateHash(
                $certificate->getBlockchainHash()
            );

            if (!$blockchainValid) {
                $this->logVerificationAttempt($certificateNumber, false, 'blockchain_invalid');
                return new CertificateValidation(
                    valid: false,
                    reason: 'Blockchain verification failed',
                    certificateNumber: $certificateNumber
                );
            }

            // Get comprehensive certificate details
            $details = $this->getCertificateDetails($certificate);
            $this->updateVerificationCount($certificate);
            $this->logVerificationAttempt($certificateNumber, true, 'verified');

            return new CertificateValidation(
                valid: true,
                certificateNumber: $certificateNumber,
                details: $details,
                verificationCount: $certificate->getVerificationCount() + 1,
                lastVerifiedAt: new DateTime()
            );

        } catch (Exception $e) {
            $this->logVerificationAttempt($certificateNumber, false, 'error');
            throw new Exception("Certificate validation failed: " . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Verify certificate via QR code
     */
    public function verifyQRCode(string $qrCodeHash): CertificateValidation {
        try {
            $certificate = $this->certificateRepository->findByQRCodeHash($qrCodeHash);
            
            if (!$certificate) {
                return new CertificateValidation(
                    valid: false,
                    reason: 'QR code not found or invalid'
                );
            }

            return $this->validateCertificate($certificate->getCertificateNumber());
        } catch (Exception $e) {
            throw new Exception("QR code verification failed: " . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Batch verify multiple certificates
     */
    public function batchVerify(array $certificateNumbers): array {
        $results = [];
        
        foreach ($certificateNumbers as $number) {
            try {
                $results[$number] = $this->validateCertificate($number);
            } catch (Exception $e) {
                $results[$number] = new CertificateValidation(
                    valid: false,
                    reason: 'Verification error: ' . $e->getMessage(),
                    certificateNumber: $number
                );
            }
        }

        return $results;
    }

    /**
     * Get verification statistics
     */
    public function getVerificationStats(DateTime $from = null, DateTime $to = null): VerificationStats {
        $from = $from ?? new DateTime('-30 days');
        $to = $to ?? new DateTime();

        $query = "
            SELECT 
                COUNT(*) as total_attempts,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_attempts,
                COUNT(DISTINCT certificate_number) as unique_certificates,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM verification_attempts 
            WHERE attempted_at BETWEEN ? AND ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return new VerificationStats(
            totalAttempts: (int)$stats['total_attempts'],
            successfulAttempts: (int)$stats['successful_attempts'],
            uniqueCertificates: (int)$stats['unique_certificates'],
            uniqueIPs: (int)$stats['unique_ips'],
            successRate: $stats['total_attempts'] > 0 ? 
                round(($stats['successful_attempts'] / $stats['total_attempts']) * 100, 2) : 0,
            period: ['from' => $from, 'to' => $to]
        );
    }

    /**
     * Check if IP is rate limited
     */
    public function isRateLimited(string $ipAddress, int $maxAttempts = 10, int $windowMinutes = 5): bool {
        $query = "
            SELECT COUNT(*) as attempts 
            FROM verification_attempts 
            WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$ipAddress, $windowMinutes]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['attempts'] >= $maxAttempts;
    }

    // Private helper methods

    private function getApplicationForVerification(int $applicationId): BirthApplication {
        $application = $this->birthApplicationRepository->findById($applicationId);

        if (!$application) {
            throw new Exception("Application not found");
        }

        if ($application->getStatus() !== 'submitted') {
            throw new Exception("Application cannot be verified. Current status: {$application->getStatus()}");
        }

        return $application;
    }

    private function generateSecureCertificateNumber(BirthApplication $application): string {
        $prefix = 'BC';
        $year = date('Y');
        $randomBytes = bin2hex(random_bytes(4));
        $checksum = substr(hash('crc32', $application->getId() . time()), 0, 4);
        
        return strtoupper("{$prefix}{$year}{$randomBytes}{$checksum}");
    }

    private function generateSecureQRCodeHash(string $certificateNumber): string {
        return hash('sha256', $certificateNumber . random_bytes(16) . time());
    }

    private function createCertificate(
        int $applicationId, 
        string $certificateNumber, 
        string $qrCodeHash, 
        string $blockchainHash,
        int $verifierId, 
        string $notes
    ): Certificate {
        $certificate = new Certificate();
        $certificate->setApplicationId($applicationId);
        $certificate->setCertificateNumber($certificateNumber);
        $certificate->setQrCodeHash($qrCodeHash);
        $certificate->setBlockchainHash($blockchainHash);
        $certificate->setIssuedBy($verifierId);
        $certificate->setVerificationNotes($notes);
        $certificate->setStatus('active');
        $certificate->setIssuedAt(new DateTime());

        $this->certificateRepository->save($certificate);
        return $certificate;
    }

    private function updateApplicationStatus(BirthApplication $application, int $verifierId): void {
        $application->setStatus('approved');
        $application->setReviewedAt(new DateTime());
        $application->setReviewedBy($verifierId);
        $this->birthApplicationRepository->save($application);
    }

    private function sendVerificationNotifications(BirthApplication $application, string $certificateNumber): void {
        $this->notificationService->sendCertificateApprovedNotification(
            $application->getUserId(), 
            $certificateNumber
        );
    }

    private function getCertificateDetails(Certificate $certificate): array {
        $application = $this->birthApplicationRepository->findById($certificate->getApplicationId());
        $user = $this->userRepository->findById($application->getUserId());

        return [
            'certificate_number' => $certificate->getCertificateNumber(),
            'qr_code_hash' => $certificate->getQrCodeHash(),
            'blockchain_hash' => $certificate->getBlockchainHash(),
            'child_name' => trim($application->getChildFirstName() . ' ' . $application->getChildLastName()),
            'parent_name' => trim($user->getFirstName() . ' ' . $user->getLastName()),
            'date_of_birth' => $application->getDateOfBirth(),
            'place_of_birth' => $application->getPlaceOfBirth(),
            'gender' => $application->getGender(),
            'issued_date' => $certificate->getIssuedAt(),
            'verified_by' => $certificate->getIssuedBy(),
            'status' => $certificate->getStatus(),
            'verification_count' => $certificate->getVerificationCount()
        ];
    }

    private function updateVerificationCount(Certificate $certificate): void {
        $certificate->setVerificationCount($certificate->getVerificationCount() + 1);
        $certificate->setLastVerifiedAt(new DateTime());
        $this->certificateRepository->save($certificate);
    }

    private function logVerificationActivity(int $applicationId, int $verifierId, string $action, array $metadata = []): void {
        $query = "
            INSERT INTO verification_log 
            (certificate_id, verified_by, verification_method, ip_address, metadata, verified_at) 
            VALUES 
            (?, ?, 'manual_entry', ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $applicationId,
            $verifierId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            json_encode($metadata)
        ]);
    }

    private function logVerificationAttempt(string $certificateNumber, bool $success, string $reason = ''): void {
        $query = "
            INSERT INTO verification_attempts 
            (certificate_number, ip_address, user_agent, success, reason, attempted_at) 
            VALUES 
            (?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $certificateNumber,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $success ? 1 : 0,
            $reason
        ]);
    }
}

// Modern PHP 8.3+ Value Objects

readonly class VerificationResult
{
    public function __construct(
        public bool $success,
        public string $certificateNumber,
        public string $qrCodeHash,
        public string $blockchainHash,
        public DateTime $verifiedAt,
        public int $verifierId
    ) {}
}

readonly class RejectionResult
{
    public function __construct(
        public bool $success,
        public DateTime $rejectedAt,
        public int $verifierId,
        public string $reason
    ) {}
}

readonly class CertificateValidation
{
    public function __construct(
        public bool $valid,
        public string $certificateNumber = '',
        public string $reason = '',
        public array $details = [],
        public int $verificationCount = 0,
        public ?DateTime $lastVerifiedAt = null
    ) {}
}

readonly class VerificationStats
{
    public function __construct(
        public int $totalAttempts,
        public int $successfulAttempts,
        public int $uniqueCertificates,
        public int $uniqueIPs,
        public float $successRate,
        public array $period
    ) {}
} 