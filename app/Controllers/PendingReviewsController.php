<?php

namespace App\Controllers;

use App\Repositories\PendingReviewsRepository;
use App\Services\AuthService;
use Exception;

class PendingReviewsController
{
    private PendingReviewsRepository $pendingReviewsRepository;
    private AuthService $authService;

    public function __construct(
        PendingReviewsRepository $pendingReviewsRepository, 
        AuthService $authService
    ) {
        $this->pendingReviewsRepository = $pendingReviewsRepository;
        $this->authService = $authService;
    }

    /**
     * Get paginated list of pending applications
     * 
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @return array Pending applications with pagination
     * @throws Exception
     */
    public function getPendingApplications(int $page = 1, int $perPage = 20): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            return $this->pendingReviewsRepository->getPendingApplications($page, $perPage);
        } catch (Exception $e) {
            error_log('Get Pending Applications Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update application status
     * 
     * @param int $applicationId Application ID
     * @param string $status New status (approved/rejected)
     * @return bool Success status
     * @throws Exception
     */
    public function updateApplicationStatus(int $applicationId, string $status): bool
    {
        // Ensure user is authorized
        $currentUser = $this->authService->requireRole(['registrar', 'admin']);

        // Validate status
        $allowedStatuses = ['approved', 'rejected'];
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception('Invalid application status');
        }

        try {
            return $this->pendingReviewsRepository->updateApplicationStatus(
                $applicationId, 
                $status, 
                $currentUser['id']
            );
        } catch (Exception $e) {
            error_log('Update Application Status Error: ' . $e->getMessage());
            throw $e;
        }
    }
} 