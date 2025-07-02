<?php
namespace App\Services;

use App\Models\BirthApplication;
use App\Models\Certificate;
use App\Models\User;
use App\Repositories\BirthApplicationRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use PDO;

class CertificateVerificationService {
    private $db;
    private $birthApplicationRepository;
    private $certificateRepository;
    private $userRepository;
    private $notificationService;

    public function __construct(
        PDO $db, 
        BirthApplicationRepository $birthApplicationRepository,
        CertificateRepository $certificateRepository,
        UserRepository $userRepository,
        NotificationService $notificationService
    ) {
        $this->db = $db;
        $this->birthApplicationRepository = $birthApplicationRepository;
        $this->certificateRepository = $certificateRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Verify a birth certificate application
     * 
     * @param int $applicationId
     * @param int $verifierId
     * @param string $notes Optional verification notes
     * @return array
     * @throws \Exception
     */
    public function verifyApplication(int $applicationId, int $verifierId, string $notes = ''): array {
        // Start a database transaction
        $this->db->beginTransaction();

        try {
            // Fetch the application
            $application = $this->birthApplicationRepository->findById($applicationId);

            if (!$application) {
                throw new \Exception("Application not found");
            }

            // Check if application is already verified or rejected
            if ($application->getStatus() !== 'submitted') {
                throw new \Exception("Application cannot be verified. Current status: {$application->getStatus()}");
            }

            // Generate unique certificate number
            $certificateNumber = $this->generateCertificateNumber($application);

            // Generate QR code hash
            $qrCodeHash = $this->generateQRCodeHash($certificateNumber);

            // Create new certificate
            $certificate = new Certificate();
            $certificate->setApplicationId($applicationId);
            $certificate->setCertificateNumber($certificateNumber);
            $certificate->setQrCodeHash($qrCodeHash);
            $certificate->setIssuedBy($verifierId);
            $certificate->setVerificationNotes($notes);
            $certificate->setStatus('active');

            // Save certificate
            $this->certificateRepository->save($certificate);

            // Update application status
            $application->setStatus('approved');
            $application->setReviewedAt(new \DateTime());
            $application->setReviewedBy($verifierId);
            $this->birthApplicationRepository->save($application);

            // Send notification to applicant
            $this->notificationService->sendCertificateApprovedNotification(
                $application->getUserId(), 
                $certificateNumber
            );

            // Log verification activity
            $this->logVerificationActivity($applicationId, $verifierId, 'approved');

            // Commit transaction
            $this->db->commit();

            return [
                'certificate_number' => $certificateNumber,
                'qr_code_hash' => $qrCodeHash,
                'verified_at' => date('Y-m-d H:i:s'),
                'verifier_id' => $verifierId
            ];
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reject a birth certificate application
     * 
     * @param int $applicationId
     * @param int $verifierId
     * @param string $reason Reason for rejection
     * @return array
     * @throws \Exception
     */
    public function rejectApplication(int $applicationId, int $verifierId, string $reason = ''): array {
        // Start a database transaction
        $this->db->beginTransaction();

        try {
            // Fetch the application
            $application = $this->birthApplicationRepository->findById($applicationId);

            if (!$application) {
                throw new \Exception("Application not found");
            }

            // Check if application is already verified or rejected
            if ($application->getStatus() !== 'submitted') {
                throw new \Exception("Application cannot be rejected. Current status: {$application->getStatus()}");
            }

            // Update application status
            $application->setStatus('rejected');
            $application->setRejectionReason($reason);
            $application->setReviewedAt(new \DateTime());
            $application->setReviewedBy($verifierId);
            $this->birthApplicationRepository->save($application);

            // Send notification to applicant
            $this->notificationService->sendCertificateRejectedNotification(
                $application->getUserId(), 
                $reason
            );

            // Log verification activity
            $this->logVerificationActivity($applicationId, $verifierId, 'rejected');

            // Commit transaction
            $this->db->commit();

            return [
                'rejected_at' => date('Y-m-d H:i:s'),
                'verifier_id' => $verifierId,
                'reason' => $reason
            ];
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Validate a certificate's authenticity
     * 
     * @param string $certificateNumber
     * @return array
     * @throws \Exception
     */
    public function validateCertificate(string $certificateNumber): array {
        try {
            // Find certificate by number
            $certificate = $this->certificateRepository->findByCertificateNumber($certificateNumber);

            if (!$certificate) {
                return [
                    'valid' => false,
                    'details' => null
                ];
            }

            // Fetch associated application and user details
            $application = $this->birthApplicationRepository->findById($certificate->getApplicationId());
            $user = $this->userRepository->findById($application->getUserId());

            return [
                'valid' => true,
                'details' => [
                    'certificate_number' => $certificateNumber,
                    'qr_code_hash' => $certificate->getQrCodeHash(),
                    'child_name' => $application->getChildFirstName() . ' ' . $application->getChildLastName(),
                    'parent_name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'issued_date' => $certificate->getIssuedAt(),
                    'verified_by' => $certificate->getIssuedBy()
                ]
            ];
        } catch (\Exception $e) {
            throw new \Exception("Certificate validation failed: " . $e->getMessage());
        }
    }

    /**
     * Generate a unique certificate number
     * 
     * @param BirthApplication $application
     * @return string
     */
    private function generateCertificateNumber(BirthApplication $application): string {
        // Use a combination of prefix, year, and unique identifier
        $prefix = 'BC'; // Birth Certificate
        $year = date('Y');
        $uniqueId = str_pad($application->getId(), 6, '0', STR_PAD_LEFT);

        return "{$prefix}{$year}{$uniqueId}";
    }

    /**
     * Generate a secure QR code hash
     * 
     * @param string $certificateNumber
     * @return string
     */
    private function generateQRCodeHash(string $certificateNumber): string {
        return hash('sha256', $certificateNumber . time());
    }

    /**
     * Log verification activity
     * 
     * @param int $applicationId
     * @param int $verifierId
     * @param string $action
     */
    private function logVerificationActivity(int $applicationId, int $verifierId, string $action) {
        $query = "
            INSERT INTO activity_log 
            (user_id, action_type, resource_type, resource_id, created_at) 
            VALUES 
            (:user_id, :action_type, 'birth_application', :resource_id, NOW())
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id' => $verifierId,
            ':action_type' => "certificate_{$action}",
            ':resource_id' => $applicationId
        ]);
    }
} 