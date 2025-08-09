<?php

namespace App\Services;

use App\Database\Database;
use Exception;

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Send notification to a specific user
     */
    public function sendNotification(int $userId, string $title, string $message, string $type = 'info', string $priority = 'normal', array $metadata = null): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, priority, metadata, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $metadataJson = $metadata ? json_encode($metadata) : null;
            
            return $stmt->execute([$userId, $title, $message, $type, $priority, $metadataJson]);
            
        } catch (Exception $e) {
            error_log("Failed to send notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulkNotification(array $userIds, string $title, string $message, string $type = 'info', string $priority = 'normal', array $metadata = null): int
    {
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            if ($this->sendNotification($userId, $title, $message, $type, $priority, $metadata)) {
                $successCount++;
            }
        }
        
        return $successCount;
    }

    /**
     * Send notification to all users with specific role
     */
    public function sendRoleNotification(string $role, string $title, string $message, string $type = 'info', string $priority = 'normal', array $metadata = null): int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ? AND status = 'active'");
            $stmt->execute([$role]);
            $users = $stmt->fetchAll();
            
            $userIds = array_column($users, 'id');
            return $this->sendBulkNotification($userIds, $title, $message, $type, $priority, $metadata);
            
        } catch (Exception $e) {
            error_log("Failed to send role notification: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send system-wide broadcast notification
     */
    public function sendBroadcast(string $title, string $message, string $type = 'announcement', string $priority = 'normal', array $metadata = null): int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE status = 'active'");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            $userIds = array_column($users, 'id');
            return $this->sendBulkNotification($userIds, $title, $message, $type, $priority, $metadata);
            
        } catch (Exception $e) {
            error_log("Failed to send broadcast: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Application status change notifications
     */
    public function sendApplicationNotification(int $userId, int $applicationId, string $status, array $details = []): bool
    {
        $templates = $this->getApplicationNotificationTemplates();
        
        if (!isset($templates[$status])) {
            return false;
        }
        
        $template = $templates[$status];
        
        // Replace placeholders
        $title = $this->replacePlaceholders($template['title'], $details);
        $message = $this->replacePlaceholders($template['message'], $details);
        
        $metadata = array_merge($details, [
            'application_id' => $applicationId,
            'notification_category' => 'application_status'
        ]);
        
        return $this->sendNotification($userId, $title, $message, $template['type'], $template['priority'], $metadata);
    }

    /**
     * Payment related notifications
     */
    public function sendPaymentNotification(int $userId, string $event, float $amount, string $applicationNumber, array $details = []): bool
    {
        $templates = $this->getPaymentNotificationTemplates();
        
        if (!isset($templates[$event])) {
            return false;
        }
        
        $template = $templates[$event];
        
        $placeholders = array_merge($details, [
            'amount' => number_format($amount, 2),
            'application_number' => $applicationNumber
        ]);
        
        $title = $this->replacePlaceholders($template['title'], $placeholders);
        $message = $this->replacePlaceholders($template['message'], $placeholders);
        
        $metadata = [
            'payment_event' => $event,
            'amount' => $amount,
            'application_number' => $applicationNumber,
            'notification_category' => 'payment'
        ];
        
        return $this->sendNotification($userId, $title, $message, $template['type'], $template['priority'], $metadata);
    }

    /**
     * Certificate related notifications
     */
    public function sendCertificateNotification(int $userId, string $event, string $certificateNumber, array $details = []): bool
    {
        $templates = $this->getCertificateNotificationTemplates();
        
        if (!isset($templates[$event])) {
            return false;
        }
        
        $template = $templates[$event];
        
        $placeholders = array_merge($details, [
            'certificate_number' => $certificateNumber
        ]);
        
        $title = $this->replacePlaceholders($template['title'], $placeholders);
        $message = $this->replacePlaceholders($template['message'], $placeholders);
        
        $metadata = [
            'certificate_event' => $event,
            'certificate_number' => $certificateNumber,
            'notification_category' => 'certificate'
        ];
        
        return $this->sendNotification($userId, $title, $message, $template['type'], $template['priority'], $metadata);
    }

    /**
     * System maintenance notifications
     */
    public function sendMaintenanceNotification(string $event, \DateTime $scheduledTime = null, int $durationMinutes = null): int
    {
        $templates = $this->getMaintenanceNotificationTemplates();
        
        if (!isset($templates[$event])) {
            return 0;
        }
        
        $template = $templates[$event];
        
        $placeholders = [
            'scheduled_time' => $scheduledTime ? $scheduledTime->format('M d, Y at h:i A') : 'TBD',
            'duration' => $durationMinutes ? $durationMinutes . ' minutes' : 'unknown duration'
        ];
        
        $title = $this->replacePlaceholders($template['title'], $placeholders);
        $message = $this->replacePlaceholders($template['message'], $placeholders);
        
        $metadata = [
            'maintenance_event' => $event,
            'scheduled_time' => $scheduledTime ? $scheduledTime->format('Y-m-d H:i:s') : null,
            'duration_minutes' => $durationMinutes,
            'notification_category' => 'maintenance'
        ];
        
        return $this->sendBroadcast($title, $message, $template['type'], $template['priority'], $metadata);
    }

    /**
     * Send reminder notifications
     */
    public function sendReminder(int $userId, string $reminderType, array $details = []): bool
    {
        $templates = $this->getReminderNotificationTemplates();
        
        if (!isset($templates[$reminderType])) {
            return false;
        }
        
        $template = $templates[$reminderType];
        
        $title = $this->replacePlaceholders($template['title'], $details);
        $message = $this->replacePlaceholders($template['message'], $details);
        
        $metadata = array_merge($details, [
            'reminder_type' => $reminderType,
            'notification_category' => 'reminder'
        ]);
        
        return $this->sendNotification($userId, $title, $message, $template['type'], $template['priority'], $metadata);
    }

    /**
     * Get notification templates for applications
     */
    private function getApplicationNotificationTemplates(): array
    {
        return [
            'submitted' => [
                'title' => '✅ Application Submitted Successfully',
                'message' => 'Your birth certificate application #{application_number} has been submitted and is now under review. You will be notified of any updates.',
                'type' => 'success',
                'priority' => 'normal'
            ],
            'under_review' => [
                'title' => '🔍 Application Under Review',
                'message' => 'Your application #{application_number} is currently being reviewed by our registrar team. We will notify you once the review is complete.',
                'type' => 'info',
                'priority' => 'normal'
            ],
            'approved' => [
                'title' => '🎉 Application Approved!',
                'message' => 'Great news! Your birth certificate application #{application_number} has been approved. Your certificate will be generated shortly.',
                'type' => 'success',
                'priority' => 'high'
            ],
            'rejected' => [
                'title' => '❌ Application Rejected',
                'message' => 'Unfortunately, your application #{application_number} has been rejected. Reason: {rejection_reason}. You can resubmit with corrections.',
                'type' => 'error',
                'priority' => 'high'
            ],
            'certificate_issued' => [
                'title' => '📜 Certificate Ready for Download',
                'message' => 'Your birth certificate is now ready! Certificate #{certificate_number} can be downloaded from your dashboard.',
                'type' => 'success',
                'priority' => 'high'
            ],
            'additional_documents_required' => [
                'title' => '📋 Additional Documents Required',
                'message' => 'We need additional documentation for your application #{application_number}. Please upload the required documents to proceed.',
                'type' => 'warning',
                'priority' => 'high'
            ]
        ];
    }

    /**
     * Get notification templates for payments
     */
    private function getPaymentNotificationTemplates(): array
    {
        return [
            'payment_required' => [
                'title' => '💳 Payment Required',
                'message' => 'Payment of ${amount} is required for your application #{application_number}. Please complete payment to proceed.',
                'type' => 'warning',
                'priority' => 'high'
            ],
            'payment_successful' => [
                'title' => '✅ Payment Successful',
                'message' => 'Your payment of ${amount} for application #{application_number} has been processed successfully.',
                'type' => 'success',
                'priority' => 'normal'
            ],
            'payment_failed' => [
                'title' => '❌ Payment Failed',
                'message' => 'Your payment of ${amount} for application #{application_number} could not be processed. Please try again.',
                'type' => 'error',
                'priority' => 'high'
            ],
            'refund_processed' => [
                'title' => '💰 Refund Processed',
                'message' => 'A refund of ${amount} has been processed for your application #{application_number}.',
                'type' => 'info',
                'priority' => 'normal'
            ]
        ];
    }

    /**
     * Get notification templates for certificates
     */
    private function getCertificateNotificationTemplates(): array
    {
        return [
            'certificate_generated' => [
                'title' => '📜 Certificate Generated',
                'message' => 'Your birth certificate #{certificate_number} has been generated and is ready for download.',
                'type' => 'success',
                'priority' => 'high'
            ],
            'certificate_verified' => [
                'title' => '✅ Certificate Verified',
                'message' => 'Certificate #{certificate_number} has been verified successfully.',
                'type' => 'success',
                'priority' => 'normal'
            ],
            'certificate_revoked' => [
                'title' => '⚠️ Certificate Revoked',
                'message' => 'Certificate #{certificate_number} has been revoked. Please contact support for more information.',
                'type' => 'error',
                'priority' => 'urgent'
            ]
        ];
    }

    /**
     * Get notification templates for maintenance
     */
    private function getMaintenanceNotificationTemplates(): array
    {
        return [
            'scheduled_maintenance' => [
                'title' => '🔧 Scheduled Maintenance Notice',
                'message' => 'The system will undergo maintenance on {scheduled_time} for approximately {duration}. Some services may be temporarily unavailable.',
                'type' => 'warning',
                'priority' => 'high'
            ],
            'emergency_maintenance' => [
                'title' => '🚨 Emergency Maintenance',
                'message' => 'The system is currently undergoing emergency maintenance. We apologize for any inconvenience and will restore service as soon as possible.',
                'type' => 'error',
                'priority' => 'urgent'
            ],
            'maintenance_complete' => [
                'title' => '✅ Maintenance Complete',
                'message' => 'System maintenance has been completed successfully. All services are now fully operational.',
                'type' => 'success',
                'priority' => 'normal'
            ]
        ];
    }

    /**
     * Get notification templates for reminders
     */
    private function getReminderNotificationTemplates(): array
    {
        return [
            'incomplete_application' => [
                'title' => '⏰ Complete Your Application',
                'message' => 'You have an incomplete birth certificate application. Please complete it to proceed with processing.',
                'type' => 'info',
                'priority' => 'normal'
            ],
            'payment_due' => [
                'title' => '💳 Payment Due Reminder',
                'message' => 'Payment is due for your application #{application_number}. Please complete payment to avoid delays.',
                'type' => 'warning',
                'priority' => 'high'
            ],
            'document_expiring' => [
                'title' => '📋 Document Expiring Soon',
                'message' => 'Some of your submitted documents will expire soon. Please update them to avoid processing delays.',
                'type' => 'warning',
                'priority' => 'normal'
            ]
        ];
    }

    /**
     * Replace placeholders in notification text
     */
    private function replacePlaceholders(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }

    /**
     * Clean up old notifications (older than 90 days)
     */
    public function cleanupOldNotifications(): int
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
                AND is_read = 1
            ");
            $stmt->execute();
            
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("Failed to cleanup old notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notification statistics
     */
    public function getSystemNotificationStats(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as notifications_today,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as notifications_week,
                    COUNT(DISTINCT user_id) as users_with_notifications
                FROM notifications
            ");
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Failed to get notification stats: " . $e->getMessage());
            return [];
        }
    }
} 