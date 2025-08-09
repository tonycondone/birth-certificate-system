<?php
namespace App\Services;

use App\Database\Database;
use Exception;

class RealTimeNotificationService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Send immediate notification with real-time update
     */
    public function sendRealTimeNotification($userId, $title, $message, $type = 'info', $priority = 'normal', $metadata = null)
    {
        try {
            // Store in database first
            $notificationId = $this->storeNotification($userId, $title, $message, $type, $priority, $metadata);
            
            // Trigger real-time update (could be expanded with WebSockets later)
            $this->triggerRealTimeUpdate($userId, [
                'id' => $notificationId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => $priority,
                'created_at' => date('Y-m-d H:i:s'),
                'is_read' => false
            ]);
            
            return $notificationId;
            
        } catch (Exception $e) {
            error_log("Error sending real-time notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send application status change notification
     */
    public function notifyApplicationStatusChange($userId, $applicationId, $status, $applicationNumber, $childName)
    {
        $statusMessages = [
            'submitted' => [
                'title' => '📝 Application Submitted',
                'message' => "Application {$applicationNumber} for {$childName} has been submitted successfully.",
                'type' => 'success'
            ],
            'under_review' => [
                'title' => '👀 Under Review',
                'message' => "Application {$applicationNumber} for {$childName} is now being reviewed.",
                'type' => 'info'
            ],
            'approved' => [
                'title' => '✅ Application Approved',
                'message' => "Great news! Application {$applicationNumber} for {$childName} has been approved.",
                'type' => 'success'
            ],
            'rejected' => [
                'title' => '❌ Application Rejected',
                'message' => "Application {$applicationNumber} for {$childName} has been rejected. Please check the details.",
                'type' => 'error'
            ],
            'certificate_issued' => [
                'title' => '🎉 Certificate Ready',
                'message' => "Birth certificate for {$childName} is ready for download!",
                'type' => 'success'
            ]
        ];
        
        $notification = $statusMessages[$status] ?? [
            'title' => '📋 Status Update',
            'message' => "Application {$applicationNumber} status has been updated to {$status}.",
            'type' => 'info'
        ];
        
        return $this->sendRealTimeNotification(
            $userId,
            $notification['title'],
            $notification['message'],
            $notification['type'],
            'high',
            [
                'application_id' => $applicationId,
                'application_number' => $applicationNumber,
                'status' => $status
            ]
        );
    }
    
    /**
     * Send certificate ready notification
     */
    public function notifyCertificateReady($userId, $certificateId, $certificateNumber, $childName)
    {
        return $this->sendRealTimeNotification(
            $userId,
            '🏆 Certificate Ready for Download',
            "Birth certificate #{$certificateNumber} for {$childName} is now available for download.",
            'success',
            'high',
            [
                'certificate_id' => $certificateId,
                'certificate_number' => $certificateNumber,
                'action_url' => "/certificates/{$certificateId}/download"
            ]
        );
    }
    
    /**
     * Send payment notification
     */
    public function notifyPaymentStatus($userId, $paymentId, $amount, $status, $applicationNumber)
    {
        $statusMessages = [
            'pending' => [
                'title' => '⏳ Payment Pending',
                'message' => "Payment of ${$amount} for application {$applicationNumber} is being processed.",
                'type' => 'info'
            ],
            'completed' => [
                'title' => '💳 Payment Successful',
                'message' => "Payment of ${$amount} for application {$applicationNumber} has been completed successfully.",
                'type' => 'success'
            ],
            'failed' => [
                'title' => '❌ Payment Failed',
                'message' => "Payment of ${$amount} for application {$applicationNumber} has failed. Please try again.",
                'type' => 'error'
            ]
        ];
        
        $notification = $statusMessages[$status] ?? [
            'title' => '💰 Payment Update',
            'message' => "Payment status for application {$applicationNumber} has been updated.",
            'type' => 'info'
        ];
        
        return $this->sendRealTimeNotification(
            $userId,
            $notification['title'],
            $notification['message'],
            $notification['type'],
            'high',
            [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => $status,
                'application_number' => $applicationNumber
            ]
        );
    }
    
    /**
     * Send system maintenance notification
     */
    public function notifySystemMaintenance($title, $message, $scheduledTime = null, $duration = null)
    {
        // Get all active users
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE status = 'active' 
            AND last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $notificationIds = [];
        foreach ($users as $user) {
            $notificationIds[] = $this->sendRealTimeNotification(
                $user['id'],
                $title,
                $message,
                'warning',
                'high',
                [
                    'scheduled_time' => $scheduledTime,
                    'duration' => $duration,
                    'type' => 'system_maintenance'
                ]
            );
        }
        
        return $notificationIds;
    }
    
    /**
     * Send reminder notification
     */
    public function sendReminder($userId, $type, $context)
    {
        $reminders = [
            'incomplete_application' => [
                'title' => '📋 Complete Your Application',
                'message' => 'You have an incomplete birth certificate application. Complete it now to avoid delays.',
                'type' => 'warning'
            ],
            'document_upload' => [
                'title' => '📎 Upload Required Documents',
                'message' => 'Please upload the required documents to process your application.',
                'type' => 'warning'
            ],
            'payment_due' => [
                'title' => '💳 Payment Required',
                'message' => 'Your application is ready for processing. Complete payment to continue.',
                'type' => 'warning'
            ]
        ];
        
        $reminder = $reminders[$type] ?? [
            'title' => '🔔 Reminder',
            'message' => $context,
            'type' => 'info'
        ];
        
        return $this->sendRealTimeNotification(
            $userId,
            $reminder['title'],
            $reminder['message'],
            $reminder['type'],
            'normal',
            ['reminder_type' => $type, 'context' => $context]
        );
    }
    
    /**
     * Store notification in database
     */
    private function storeNotification($userId, $title, $message, $type, $priority, $metadata)
    {
        // Ensure notifications table exists
        $this->ensureNotificationsTableExists();
        
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, priority, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        $stmt->execute([$userId, $title, $message, $type, $priority, $metadataJson]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Trigger real-time update (placeholder for WebSocket implementation)
     */
    private function triggerRealTimeUpdate($userId, $notificationData)
    {
        // For now, we'll use a simple file-based approach for demo
        // In production, this would be replaced with WebSocket server or SSE
        
        try {
            $updateFile = sys_get_temp_dir() . "/notification_update_{$userId}.json";
            file_put_contents($updateFile, json_encode([
                'timestamp' => time(),
                'user_id' => $userId,
                'notification' => $notificationData
            ]));
            
            // Clean up old update files
            $this->cleanupOldUpdates();
            
        } catch (Exception $e) {
            error_log("Error triggering real-time update: " . $e->getMessage());
        }
    }
    
    /**
     * Get recent real-time updates for user
     */
    public function getRecentUpdates($userId, $since = null)
    {
        $updateFile = sys_get_temp_dir() . "/notification_update_{$userId}.json";
        
        if (!file_exists($updateFile)) {
            return [];
        }
        
        try {
            $data = json_decode(file_get_contents($updateFile), true);
            $since = $since ?? (time() - 300); // Default to last 5 minutes
            
            if ($data && $data['timestamp'] > $since) {
                // Remove the file after reading to prevent duplicate notifications
                unlink($updateFile);
                return [$data['notification']];
            }
            
        } catch (Exception $e) {
            error_log("Error getting recent updates: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Clean up old update files
     */
    private function cleanupOldUpdates()
    {
        $tempDir = sys_get_temp_dir();
        $pattern = $tempDir . '/notification_update_*.json';
        $files = glob($pattern);
        
        $cutoff = time() - 3600; // Remove files older than 1 hour
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    /**
     * Ensure notifications table exists
     */
    private function ensureNotificationsTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    type ENUM('info', 'success', 'warning', 'error', 'announcement') DEFAULT 'info',
                    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
                    is_read BOOLEAN DEFAULT FALSE,
                    read_at TIMESTAMP NULL,
                    metadata JSON,
                    scheduled_for TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_is_read (is_read),
                    INDEX idx_created_at (created_at),
                    INDEX idx_type (type),
                    INDEX idx_priority (priority)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Exception $e) {
            error_log("Error creating notifications table: " . $e->getMessage());
        }
    }
    
    /**
     * Get notification statistics for user
     */
    public function getNotificationStats($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN priority = 'high' OR priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
                FROM notifications 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch() ?: [];
            
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }
} 