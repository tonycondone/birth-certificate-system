<?php
namespace App\Controllers;

use App\Database\Database;
use Exception;

class NotificationController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
            $this->ensureNotificationsTableExists();
        } catch (Exception $e) {
            error_log("NotificationController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Display notifications page for current user
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['role'] ?? 'user';
            
            // Get filter parameters
            $type = $_GET['type'] ?? 'all';
            $status = $_GET['status'] ?? 'all';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Get notifications based on role
            if ($userRole === 'admin') {
                $notifications = $this->getAdminNotifications($type, $status, $offset, $perPage);
                $totalCount = $this->countAdminNotifications($type, $status);
            } else {
                $notifications = $this->getUserNotifications($userId, $type, $status, $offset, $perPage);
                $totalCount = $this->countUserNotifications($userId, $type, $status);
            }
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;
            
            // Get statistics
            $stats = $this->getNotificationStats($userId, $userRole);
            
            $pageTitle = 'Notifications';
            
            // Include appropriate view
            if ($userRole === 'admin') {
                include BASE_PATH . '/resources/views/admin/notifications.php';
            } else {
                include BASE_PATH . '/resources/views/notifications.php';
            }
            
        } catch (Exception $e) {
            error_log("Error loading notifications: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load notifications. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Create a new notification (Admin only)
     */
    public function create()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['title', 'message', 'type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                    return;
                }
            }
            
            $title = trim($data['title']);
            $message = trim($data['message']);
            $type = $data['type'];
            $targetType = $data['target_type'] ?? 'all'; // all, role, specific
            $targetValue = $data['target_value'] ?? null;
            $priority = $data['priority'] ?? 'normal'; // low, normal, high, urgent
            $scheduledFor = $data['scheduled_for'] ?? null;
            
            // Validate type
            $validTypes = ['info', 'success', 'warning', 'error', 'announcement'];
            if (!in_array($type, $validTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid notification type']);
                return;
            }
            
            // Create notifications based on target
            $notificationIds = $this->createNotificationsByTarget(
                $title, $message, $type, $targetType, $targetValue, $priority, $scheduledFor
            );
            
            echo json_encode([
                'success' => true, 
                'message' => 'Notification(s) created successfully',
                'count' => count($notificationIds),
                'ids' => $notificationIds
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Get notification details (for editing)
     */
    public function show($id)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['role'] ?? 'user';
            
            // Build query based on user role
            if ($userRole === 'admin') {
                $stmt = $this->db->prepare("
                    SELECT n.*, u.first_name, u.last_name, u.email 
                    FROM notifications n
                    LEFT JOIN users u ON n.user_id = u.id
                    WHERE n.id = ?
                ");
                $stmt->execute([$id]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT * FROM notifications 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$id, $userId]);
            }
            
            $notification = $stmt->fetch();
            
            if (!$notification) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
                return;
            }
            
            // Mark as read if it's the user's notification
            if ($notification['user_id'] == $userId && !$notification['is_read']) {
                $this->markAsRead($id);
                $notification['is_read'] = 1;
                $notification['read_at'] = date('Y-m-d H:i:s');
            }
            
            echo json_encode(['success' => true, 'notification' => $notification]);
            
        } catch (Exception $e) {
            error_log("Error fetching notification: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ? AND is_read = 0
            ");
            
            $result = $stmt->execute([$id, $userId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification already read or not found']);
            }
            
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0
            ");
            
            $result = $stmt->execute([$userId]);
            $affectedRows = $stmt->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "Marked $affectedRows notifications as read",
                'count' => $affectedRows
            ]);
            
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Delete notification (Admin only, or user's own notification)
     */
    public function delete($id)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['role'] ?? 'user';
            
            // Build delete query based on role
            if ($userRole === 'admin') {
                $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $userId]);
            }
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found or unauthorized']);
            }
            
        } catch (Exception $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Get unread notifications count for current user
     */
    public function getUnreadCount()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
            
        } catch (Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Get recent notifications for dropdown/popup
     */
    public function getRecent()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT id, title, message, type, is_read, created_at,
                       CASE 
                           WHEN created_at >= NOW() - INTERVAL 1 HOUR THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), 'm ago')
                           WHEN created_at >= NOW() - INTERVAL 1 DAY THEN CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), 'h ago')
                           ELSE DATE_FORMAT(created_at, '%M %d, %Y')
                       END as time_ago
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            
        } catch (Exception $e) {
            error_log("Error getting recent notifications: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Broadcast system notification (Admin only)
     */
    public function broadcast()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $title = trim($data['title'] ?? '');
            $message = trim($data['message'] ?? '');
            $type = $data['type'] ?? 'announcement';
            $priority = $data['priority'] ?? 'normal';
            
            if (empty($title) || empty($message)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Title and message are required']);
                return;
            }
            
            // Get all active users
            $stmt = $this->db->prepare("SELECT id FROM users WHERE status = 'active'");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            // Create notification for each user
            $notificationIds = [];
            foreach ($users as $user) {
                $id = $this->createNotification($user['id'], $title, $message, $type, $priority);
                if ($id) {
                    $notificationIds[] = $id;
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Broadcast sent successfully',
                'count' => count($notificationIds),
                'users_notified' => count($users)
            ]);
            
        } catch (Exception $e) {
            error_log("Error broadcasting notification: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    // PRIVATE HELPER METHODS
    
    /**
     * Ensure notifications table exists with all necessary columns
     */
    private function ensureNotificationsTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(200) NOT NULL,
                    message TEXT NOT NULL,
                    type ENUM('info', 'success', 'warning', 'error', 'announcement') DEFAULT 'info',
                    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
                    is_read TINYINT(1) DEFAULT 0,
                    read_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    scheduled_for TIMESTAMP NULL,
                    metadata JSON NULL,
                    INDEX idx_user_id (user_id),
                    INDEX idx_is_read (is_read),
                    INDEX idx_created_at (created_at),
                    INDEX idx_type (type),
                    INDEX idx_priority (priority),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Exception $e) {
            error_log("Failed to create notifications table: " . $e->getMessage());
        }
    }
    
    /**
     * Create notifications based on target criteria
     */
    private function createNotificationsByTarget($title, $message, $type, $targetType, $targetValue, $priority, $scheduledFor)
    {
        $notificationIds = [];
        
        switch ($targetType) {
            case 'all':
                // Send to all active users
                $stmt = $this->db->prepare("SELECT id FROM users WHERE status = 'active'");
                $stmt->execute();
                $users = $stmt->fetchAll();
                
                foreach ($users as $user) {
                    $id = $this->createNotification($user['id'], $title, $message, $type, $priority, $scheduledFor);
                    if ($id) $notificationIds[] = $id;
                }
                break;
                
            case 'role':
                // Send to users with specific role
                $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ? AND status = 'active'");
                $stmt->execute([$targetValue]);
                $users = $stmt->fetchAll();
                
                foreach ($users as $user) {
                    $id = $this->createNotification($user['id'], $title, $message, $type, $priority, $scheduledFor);
                    if ($id) $notificationIds[] = $id;
                }
                break;
                
            case 'specific':
                // Send to specific user IDs
                $userIds = is_array($targetValue) ? $targetValue : explode(',', $targetValue);
                
                foreach ($userIds as $userId) {
                    $userId = trim($userId);
                    if (is_numeric($userId)) {
                        $id = $this->createNotification($userId, $title, $message, $type, $priority, $scheduledFor);
                        if ($id) $notificationIds[] = $id;
                    }
                }
                break;
        }
        
        return $notificationIds;
    }
    
    /**
     * Create a single notification
     */
    private function createNotification($userId, $title, $message, $type = 'info', $priority = 'normal', $scheduledFor = null, $metadata = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, priority, scheduled_for, metadata, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $metadataJson = $metadata ? json_encode($metadata) : null;
            $scheduledForTimestamp = $scheduledFor ? date('Y-m-d H:i:s', strtotime($scheduledFor)) : null;
            
            $stmt->execute([$userId, $title, $message, $type, $priority, $scheduledForTimestamp, $metadataJson]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications with filtering
     */
    private function getUserNotifications($userId, $type, $status, $offset, $limit)
    {
        $whereConditions = ["user_id = ?"];
        $params = [$userId];
        
        if ($type !== 'all') {
            $whereConditions[] = "type = ?";
            $params[] = $type;
        }
        
        if ($status === 'unread') {
            $whereConditions[] = "is_read = 0";
        } elseif ($status === 'read') {
            $whereConditions[] = "is_read = 1";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("
            SELECT *, 
                   CASE 
                       WHEN created_at >= NOW() - INTERVAL 1 HOUR THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), 'm ago')
                       WHEN created_at >= NOW() - INTERVAL 1 DAY THEN CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), 'h ago')
                       ELSE DATE_FORMAT(created_at, '%M %d, %Y at %h:%i %p')
                   END as time_ago
            FROM notifications
            WHERE $whereClause
            ORDER BY 
                CASE priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'normal' THEN 3 
                    WHEN 'low' THEN 4 
                END,
                is_read ASC,
                created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get admin notifications (all notifications with user info)
     */
    private function getAdminNotifications($type, $status, $offset, $limit)
    {
        $whereConditions = ["1=1"];
        $params = [];
        
        if ($type !== 'all') {
            $whereConditions[] = "n.type = ?";
            $params[] = $type;
        }
        
        if ($status === 'unread') {
            $whereConditions[] = "n.is_read = 0";
        } elseif ($status === 'read') {
            $whereConditions[] = "n.is_read = 1";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("
            SELECT n.*, 
                   u.first_name, u.last_name, u.email, u.role,
                   CASE 
                       WHEN n.created_at >= NOW() - INTERVAL 1 HOUR THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), 'm ago')
                       WHEN n.created_at >= NOW() - INTERVAL 1 DAY THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), 'h ago')
                       ELSE DATE_FORMAT(n.created_at, '%M %d, %Y at %h:%i %p')
                   END as time_ago
            FROM notifications n
            JOIN users u ON n.user_id = u.id
            WHERE $whereClause
            ORDER BY 
                CASE n.priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'normal' THEN 3 
                    WHEN 'low' THEN 4 
                END,
                n.is_read ASC,
                n.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count user notifications
     */
    private function countUserNotifications($userId, $type, $status)
    {
        $whereConditions = ["user_id = ?"];
        $params = [$userId];
        
        if ($type !== 'all') {
            $whereConditions[] = "type = ?";
            $params[] = $type;
        }
        
        if ($status === 'unread') {
            $whereConditions[] = "is_read = 0";
        } elseif ($status === 'read') {
            $whereConditions[] = "is_read = 1";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE $whereClause");
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Count admin notifications
     */
    private function countAdminNotifications($type, $status)
    {
        $whereConditions = ["1=1"];
        $params = [];
        
        if ($type !== 'all') {
            $whereConditions[] = "type = ?";
            $params[] = $type;
        }
        
        if ($status === 'unread') {
            $whereConditions[] = "is_read = 0";
        } elseif ($status === 'read') {
            $whereConditions[] = "is_read = 1";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE $whereClause");
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get notification statistics
     */
    private function getNotificationStats($userId, $userRole)
    {
        if ($userRole === 'admin') {
            // Admin sees system-wide stats
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as `read`,
                    SUM(CASE WHEN type = 'urgent' OR priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
                    SUM(CASE WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN 1 ELSE 0 END) as today
                FROM notifications
            ");
            $stmt->execute();
        } else {
            // Regular user sees their own stats
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read,
                    SUM(CASE WHEN type = 'urgent' OR priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
                    SUM(CASE WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN 1 ELSE 0 END) as today
                FROM notifications
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetch();
    }
} 