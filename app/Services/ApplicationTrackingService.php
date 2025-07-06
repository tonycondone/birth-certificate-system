<?php
namespace App\Services;

use App\Repositories\BirthApplicationRepository;
use App\Services\NotificationService;
use App\Services\LoggingService;

class ApplicationTrackingService {
    private $applicationRepository;
    private $notificationService;
    private $loggingService;

    // Application status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_REQUIRES_ADDITIONAL_INFO = 'additional_info_required';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ISSUED = 'certificate_issued';

    public function __construct(
        BirthApplicationRepository $applicationRepository,
        NotificationService $notificationService,
        LoggingService $loggingService
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->notificationService = $notificationService;
        $this->loggingService = $loggingService;
    }

    public function createApplication(array $applicationData): int {
        $applicationData['status'] = self::STATUS_DRAFT;
        $applicationId = $this->applicationRepository->create($applicationData);

        $this->loggingService->log(
            'application_created', 
            ['application_id' => $applicationId, 'user_id' => $applicationData['user_id']]
        );

        return $applicationId;
    }

    public function updateApplicationStatus(int $applicationId, string $newStatus, ?string $notes = null): bool {
        $currentStatus = $this->applicationRepository->getStatus($applicationId);

        // Validate status transition
        if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
            throw new \InvalidArgumentException("Invalid status transition from $currentStatus to $newStatus");
        }

        $updated = $this->applicationRepository->updateStatus($applicationId, $newStatus, $notes);

        if ($updated) {
            $this->handleStatusChangeNotification($applicationId, $currentStatus, $newStatus);
            
            $this->loggingService->log(
                'application_status_changed', 
                [
                    'application_id' => $applicationId, 
                    'old_status' => $currentStatus, 
                    'new_status' => $newStatus
                ]
            );
        }

        return $updated;
    }

    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool {
        $validTransitions = [
            self::STATUS_DRAFT => [self::STATUS_SUBMITTED],
            self::STATUS_SUBMITTED => [self::STATUS_UNDER_REVIEW, self::STATUS_REJECTED],
            self::STATUS_UNDER_REVIEW => [
                self::STATUS_REQUIRES_ADDITIONAL_INFO, 
                self::STATUS_APPROVED, 
                self::STATUS_REJECTED
            ],
            self::STATUS_REQUIRES_ADDITIONAL_INFO => [self::STATUS_UNDER_REVIEW],
            self::STATUS_APPROVED => [self::STATUS_ISSUED],
        ];

        return isset($validTransitions[$currentStatus]) && 
               in_array($newStatus, $validTransitions[$currentStatus]);
    }

    private function handleStatusChangeNotification(int $applicationId, string $oldStatus, string $newStatus) {
        $application = $this->applicationRepository->findById($applicationId);
        $userId = $application['user_id'];

        $notificationTemplates = [
            self::STATUS_SUBMITTED => 'application_submitted',
            self::STATUS_UNDER_REVIEW => 'application_under_review',
            self::STATUS_REQUIRES_ADDITIONAL_INFO => 'additional_info_required',
            self::STATUS_APPROVED => 'application_approved',
            self::STATUS_REJECTED => 'application_rejected',
            self::STATUS_ISSUED => 'certificate_issued'
        ];

        if (isset($notificationTemplates[$newStatus])) {
            $this->notificationService->sendNotification(
                $userId, 
                $notificationTemplates[$newStatus], 
                ['application_id' => $applicationId]
            );
        }
    }

    public function getApplicationTimeline(int $applicationId): array {
        return $this->applicationRepository->getStatusHistory($applicationId);
    }

    public function searchApplications(array $filters): array {
        return $this->applicationRepository->search($filters);
    }
} 