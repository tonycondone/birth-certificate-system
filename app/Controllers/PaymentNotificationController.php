<?php

namespace App\Controllers;

use App\Database\Database;
use App\Services\PaymentNotificationService;
use App\Services\LoggingService;
use Exception;

class PaymentNotificationController extends BaseController
{
    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new PaymentNotificationService();
    }

    /**
     * Get payment notifications for current user
     */
    public function getNotifications(): array
    {
        try {
            $users = $this->notificationService->detectUsersRequiringPaymentNotification();
            return $users;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get notification variants for A/B testing
     */
    public function getNotificationVariants(string $userType): array
    {
        return $this->notificationService->getNotificationVariants($userType);
    }

    /**
     * Track notification performance
     */
    public function trackNotificationPerformance(string $userId, string $notificationType, string $action): void
    {
        $this->notificationService->trackNotificationPerformance($userId, $notificationType, $action);
    }

    /**
     * Get payment system status
     */
    public function getPaymentSystemStatus(): array
    {
        return $this->notificationService->getPaymentSystemStatus();
    }
}
