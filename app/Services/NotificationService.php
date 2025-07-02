<?php

namespace App\Services;

use PDO;
use Exception;

class NotificationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Send a notification
     * 
     * @param int $userId
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function sendNotification(int $userId, string $message, string $type = 'info'): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())");
            return $stmt->execute([$userId, $message, $type]);
        } catch (Exception $e) {
            error_log("Notification Send Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notifications for a user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserNotifications(int $userId, int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Notification Fetch Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notifications as read
     * 
     * @param int $userId
     * @return bool
     */
    public function markNotificationsAsRead(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Notification Mark Read Error: " . $e->getMessage());
            return false;
        }
    }
} 