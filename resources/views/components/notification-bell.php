<?php
// Get unread notification count for current user
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = \App\Database\Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $unreadCount = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Silently fail if notifications table doesn't exist yet
        $unreadCount = 0;
    }
}
?>

<div class="dropdown notification-dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" 
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                  id="notificationBadge">
                <?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        <?php endif; ?>
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu" aria-labelledby="notificationDropdown">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <?php if ($unreadCount > 0): ?>
                <button class="btn btn-sm btn-link p-0 text-primary" onclick="markAllAsRead()">
                    Mark all read
                </button>
            <?php endif; ?>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
            <li class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0 mt-2 text-muted">Loading notifications...</p>
            </li>
        </div>
        
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-center" href="/notifications">
                <i class="fas fa-eye me-1"></i>View All Notifications
            </a>
        </li>
    </ul>
</div>

<style>
.notification-dropdown .dropdown-menu {
    width: 350px;
    max-width: 90vw;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #fff3cd;
    border-left: 3px solid #ffc107;
}

.notification-item .notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    margin-right: 12px;
}

.notification-item .notification-icon.info { background-color: #17a2b8; }
.notification-item .notification-icon.success { background-color: #28a745; }
.notification-item .notification-icon.warning { background-color: #ffc107; color: #212529; }
.notification-item .notification-icon.error { background-color: #dc3545; }
.notification-item .notification-icon.announcement { background-color: #6f42c1; }

.notification-item .notification-title {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 14px;
    line-height: 1.2;
}

.notification-item .notification-message {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
    line-height: 1.3;
}

.notification-item .notification-time {
    font-size: 11px;
    color: #9ca3af;
}

.notification-empty {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.notification-empty i {
    font-size: 32px;
    margin-bottom: 12px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .notification-dropdown .dropdown-menu {
        width: 300px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications when dropdown is opened
    document.getElementById('notificationDropdown').addEventListener('show.bs.dropdown', function() {
        loadRecentNotifications();
    });

    function loadRecentNotifications() {
        const notificationsList = document.getElementById('notificationsList');
        
        fetch('/notifications/get-recent')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.notifications.length === 0) {
                        notificationsList.innerHTML = `
                            <li class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p class="mb-0">No notifications</p>
                            </li>
                        `;
                    } else {
                        notificationsList.innerHTML = data.notifications.map(notification => `
                            <li class="notification-item ${notification.is_read ? '' : 'unread'}" 
                                onclick="markAsReadAndRedirect(${notification.id})">
                                <div class="d-flex">
                                    <div class="notification-icon ${notification.type}">
                                        <i class="fas ${getNotificationIcon(notification.type)}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="notification-title">${notification.title}</div>
                                        <div class="notification-message">${truncateText(notification.message, 80)}</div>
                                        <div class="notification-time">${notification.time_ago}</div>
                                    </div>
                                    ${!notification.is_read ? '<div class="text-primary"><i class="fas fa-circle" style="font-size: 8px;"></i></div>' : ''}
                                </div>
                            </li>
                        `).join('');
                    }
                } else {
                    notificationsList.innerHTML = `
                        <li class="text-center py-3 text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p class="mb-0 mt-2">Failed to load notifications</p>
                        </li>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationsList.innerHTML = `
                    <li class="text-center py-3 text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="mb-0 mt-2">Error loading notifications</p>
                    </li>
                `;
            });
    }

    function getNotificationIcon(type) {
        const icons = {
            info: 'fa-info-circle',
            success: 'fa-check-circle',
            warning: 'fa-exclamation-triangle',
            error: 'fa-times-circle',
            announcement: 'fa-bullhorn'
        };
        return icons[type] || 'fa-bell';
    }

    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }

    window.markAsReadAndRedirect = function(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update badge count
                updateNotificationBadge();
                // Redirect to notifications page
                window.location.href = '/notifications';
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    };

    window.markAllAsRead = function() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide badge
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.style.display = 'none';
                }
                // Reload notifications list
                loadRecentNotifications();
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    };

    function updateNotificationBadge() {
        fetch('/notifications/get-unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('notificationBadge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'inline';
                        }
                    } else {
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => console.error('Error updating badge:', error));
    }

    // Update notification count every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});
</script> 