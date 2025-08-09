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
        } catch (Exception $e) {
            error_log("NotificationController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Get search parameters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            // Get notifications
            $notifications = $this->getUserNotifications($userId, $offset, $perPage);
            
            // Get total count for pagination
            $totalCount = $this->countUserNotifications($userId);
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;
            
            // Get unread count for badge
            $unreadCount = $this->countUnreadNotifications($userId);
            
            $pageTitle = 'Notifications';
            
            // Include view
            include BASE_PATH . '/resources/views/notifications.php';
            
        } catch (Exception $e) {
            error_log("Error loading notifications: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load notifications. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }
    
    public function markAsRead($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Mark notification as read
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            
            $result = $stmt->execute([$id, $userId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
            }
            
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    public function markAllAsRead()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Mark all notifications as read
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0
            ");
            
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
            }
            
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Get user notifications with pagination
     */
    private function getUserNotifications($userId, $offset = 0, $limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count total user notifications
     */
    private function countUserNotifications($userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Count unread notifications
     */
    private function countUnreadNotifications($userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = ? AND is_read = 0
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
} 