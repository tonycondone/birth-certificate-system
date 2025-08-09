<?php
// Get unread notification count if user is logged in
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
    }
}
?>

<style>
.notification-bell {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.notification-bell:hover {
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

.notification-badge.hidden {
    display: none;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.notification-dropdown {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border-radius: 10px;
}

.notification-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px 10px 0 0;
    margin: -8px -8px 0 -8px;
}

.notification-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f1f1f1;
    transition: background-color 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.notification-item .notification-title {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 4px;
    color: #333;
}

.notification-item .notification-message {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-item .notification-time {
    font-size: 11px;
    color: #999;
}

.notification-icon {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.notification-icon.success { background: #28a745; }
.notification-icon.info { background: #17a2b8; }
.notification-icon.warning { background: #ffc107; }
.notification-icon.error { background: #dc3545; }

.notification-footer {
    padding: 10px 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

.notification-empty {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.notification-loading {
    text-align: center;
    padding: 20px;
}

.notification-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.mark-all-read-btn {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    transition: all 0.2s ease;
}

.mark-all-read-btn:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

@media (max-width: 768px) {
    .notification-dropdown {
        width: 300px;
    }
}
</style>

<li class="nav-item dropdown notification-bell" id="notificationBell">
    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" 
       data-bs-toggle="dropdown" aria-expanded="false" onclick="loadNotifications()">
        <i class="fas fa-bell"></i>
        <span class="notification-badge <?php echo $unreadCount === 0 ? 'hidden' : ''; ?>" id="notificationBadge">
            <?php echo $unreadCount; ?>
        </span>
    </a>
    
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
        <div class="notification-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-bell me-2"></i>Notifications
                </h6>
                <button class="btn mark-all-read-btn" onclick="markAllAsRead()" id="markAllReadBtn">
                    Mark All Read
                </button>
            </div>
        </div>
        
        <div id="notificationsList">
            <div class="notification-loading">
                <div class="notification-spinner"></div>
                <small>Loading notifications...</small>
            </div>
        </div>
        
        <div class="notification-footer">
            <a href="/notifications" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list me-1"></i>View All Notifications
            </a>
        </div>
    </div>
</li>

<script>
let notificationUpdateInterval;
let lastNotificationCheck = 0;

// Initialize notification system when document is ready
document.addEventListener('DOMContentLoaded', function() {
    startNotificationPolling();
    
    // Listen for clicks outside dropdown to stop polling
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notificationBell');
        if (!dropdown.contains(e.target)) {
            // Dropdown closed, continue background polling
        }
    });
});

function startNotificationPolling() {
    // Update immediately
    updateNotificationCount();
    
    // Then update every 30 seconds
    notificationUpdateInterval = setInterval(updateNotificationCount, 30000);
}

function updateNotificationCount() {
    fetch('/notifications/get-unread-count', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('notificationBadge');
            const count = parseInt(data.count) || 0;
            
            badge.textContent = count;
            badge.classList.toggle('hidden', count === 0);
            
            // Add animation if count increased
            if (count > lastNotificationCheck) {
                badge.style.animation = 'none';
                setTimeout(() => {
                    badge.style.animation = 'pulse 2s infinite';
                }, 10);
                
                // Play notification sound (optional)
                playNotificationSound();
            }
            
            lastNotificationCheck = count;
        }
    })
    .catch(error => {
        console.error('Error updating notification count:', error);
    });
}

function loadNotifications() {
    const container = document.getElementById('notificationsList');
    
    // Show loading state
    container.innerHTML = `
        <div class="notification-loading">
            <div class="notification-spinner"></div>
            <small>Loading notifications...</small>
        </div>
    `;
    
    fetch('/notifications/get-recent?limit=10', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications);
        } else {
            container.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                    <p>Failed to load notifications</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        container.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-times-circle text-danger mb-2"></i>
                <p>Error loading notifications</p>
            </div>
        `;
    });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationsList');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash text-muted mb-2"></i>
                <p>No notifications</p>
                <small class="text-muted">You're all caught up!</small>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const isUnread = !notification.is_read;
        const timeAgo = formatTimeAgo(notification.created_at);
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notification.id}" 
                 onclick="markAsReadAndView(${notification.id})">
                <div class="d-flex align-items-start">
                    <span class="notification-icon ${notification.type}"></span>
                    <div class="flex-grow-1">
                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="notification-time">
                            <i class="fas fa-clock me-1"></i>${timeAgo}
                        </div>
                    </div>
                    ${isUnread ? '<i class="fas fa-circle text-primary" style="font-size: 8px;"></i>' : ''}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Update mark all read button visibility
    const hasUnread = notifications.some(n => !n.is_read);
    const markAllBtn = document.getElementById('markAllReadBtn');
    markAllBtn.style.display = hasUnread ? 'block' : 'none';
}

function markAsReadAndView(notificationId) {
    // Mark as read
    fetch(`/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                const unreadIcon = item.querySelector('.fa-circle');
                if (unreadIcon) {
                    unreadIcon.remove();
                }
            }
            
            // Update count
            updateNotificationCount();
            
            // Reload notifications to refresh the list
            setTimeout(loadNotifications, 500);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    const markAllBtn = document.getElementById('markAllReadBtn');
    markAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Marking...';
    markAllBtn.disabled = true;
    
    fetch('/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const unreadIcon = item.querySelector('.fa-circle');
                if (unreadIcon) {
                    unreadIcon.remove();
                }
            });
            
            // Update count
            updateNotificationCount();
            
            // Hide mark all read button
            markAllBtn.style.display = 'none';
            
            // Show success message
            showToast('success', 'All notifications marked as read');
        } else {
            showToast('error', 'Failed to mark notifications as read');
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
        showToast('error', 'Error marking notifications as read');
    })
    .finally(() => {
        markAllBtn.innerHTML = 'Mark All Read';
        markAllBtn.disabled = false;
    });
}

function formatTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function playNotificationSound() {
    // Create a subtle notification sound
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.1;
        
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (e) {
        // Ignore if audio context is not available
    }
}

function showToast(type, message) {
    // Create and show a toast notification
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = toastId;
    toast.innerHTML = `
        <div class="toast-header">
            <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'times-circle text-danger'} me-2"></i>
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${escapeHtml(message)}</div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1060';
    document.body.appendChild(container);
    return container;
}

// Clean up interval when page unloads
window.addEventListener('beforeunload', function() {
    if (notificationUpdateInterval) {
        clearInterval(notificationUpdateInterval);
    }
});

// Enhanced real-time polling system
let lastPollTime = Math.floor(Date.now() / 1000);
let realTimePollingInterval;
let isPollingActive = false;

function startRealTimePolling() {
    if (isPollingActive) return;
    
    isPollingActive = true;
    realTimePollingInterval = setInterval(pollForNewNotifications, 15000); // Poll every 15 seconds
    
    // Also poll when user becomes active
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && isPollingActive) {
            pollForNewNotifications();
        }
    });
}

function stopRealTimePolling() {
    if (realTimePollingInterval) {
        clearInterval(realTimePollingInterval);
        realTimePollingInterval = null;
    }
    isPollingActive = false;
}

function pollForNewNotifications() {
    fetch(`/notifications/poll?since=${lastPollTime}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update last poll time
            lastPollTime = data.server_time;
            
            // Update unread count
            const badge = document.getElementById('notificationBadge');
            const count = parseInt(data.unread_count) || 0;
            
            if (badge) {
                const oldCount = parseInt(badge.textContent) || 0;
                badge.textContent = count;
                badge.classList.toggle('hidden', count === 0);
                
                // Animate badge if count increased
                if (count > oldCount) {
                    animateNotificationBadge();
                }
            }
            
            // Handle new notifications
            if (data.new_notifications && data.new_notifications.length > 0) {
                data.new_notifications.forEach(notification => {
                    showRealTimeNotification(notification);
                });
            }
        }
    })
    .catch(error => {
        console.error('Error polling for notifications:', error);
    });
}

function showRealTimeNotification(notification) {
    // Show browser notification if permission granted
    if (Notification.permission === 'granted') {
        const browserNotification = new Notification(notification.title, {
            body: notification.message,
            icon: '/images/logo.svg',
            badge: '/images/logo.svg',
            tag: 'notification-' + notification.id,
            requireInteraction: notification.priority === 'high' || notification.priority === 'urgent'
        });
        
        browserNotification.onclick = function() {
            window.focus();
            // Mark as read when clicked
            markAsReadAndView(notification.id);
            browserNotification.close();
        };
        
        // Auto close after 5 seconds for normal notifications
        if (notification.priority !== 'high' && notification.priority !== 'urgent') {
            setTimeout(() => browserNotification.close(), 5000);
        }
    }
    
    // Show in-app toast notification
    showInAppNotification(notification);
    
    // Play notification sound
    playNotificationSound();
    
    // Update the notification list if dropdown is open
    const dropdown = document.getElementById('notificationBell');
    if (dropdown && dropdown.querySelector('.dropdown-menu').classList.contains('show')) {
        setTimeout(loadNotifications, 1000); // Reload after a short delay
    }
}

function showInAppNotification(notification) {
    // Create toast notification
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastId = 'toast-' + notification.id;
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = toastId;
    
    const typeIcons = {
        success: 'fa-check-circle text-success',
        error: 'fa-times-circle text-danger',
        warning: 'fa-exclamation-triangle text-warning',
        info: 'fa-info-circle text-info'
    };
    
    const iconClass = typeIcons[notification.type] || typeIcons.info;
    
    toast.innerHTML = `
        <div class="toast-header">
            <i class="fas ${iconClass} me-2"></i>
            <strong class="me-auto">${escapeHtml(notification.title)}</strong>
            <small class="text-muted">Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${escapeHtml(notification.message)}
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary" onclick="markAsReadAndView(${notification.id})">
                    Mark as Read
                </button>
            </div>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: notification.priority === 'high' || notification.priority === 'urgent' ? false : true,
        delay: 8000
    });
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function animateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.style.animation = 'none';
        setTimeout(() => {
            badge.style.animation = 'pulse 2s infinite';
        }, 10);
    }
}

function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showToast('success', 'Browser notifications enabled! You\'ll now receive real-time updates.');
            }
        });
    }
}

// Enhanced initialization
document.addEventListener('DOMContentLoaded', function() {
    // Start real-time polling
    startRealTimePolling();
    
    // Request notification permission after a short delay
    setTimeout(requestNotificationPermission, 3000);
    
    // Stop polling when page is about to unload
    window.addEventListener('beforeunload', stopRealTimePolling);
    
    // Add notification permission button to dropdown header
    const notificationHeader = document.querySelector('.notification-header');
    if (notificationHeader && 'Notification' in window && Notification.permission !== 'granted') {
        const permissionBtn = document.createElement('button');
        permissionBtn.className = 'btn btn-sm btn-outline-light ms-2';
        permissionBtn.innerHTML = '<i class="fas fa-bell-slash me-1"></i>Enable';
        permissionBtn.title = 'Enable browser notifications';
        permissionBtn.onclick = requestNotificationPermission;
        
        notificationHeader.querySelector('.d-flex').appendChild(permissionBtn);
    }
});

// Update the existing loadNotifications function to work with real-time updates
const originalLoadNotifications = loadNotifications;
loadNotifications = function() {
    // Update last poll time when manually loading
    lastPollTime = Math.floor(Date.now() / 1000);
    originalLoadNotifications();
};

// Add visual indicator for real-time status
function addRealTimeIndicator() {
    const bell = document.querySelector('#notificationDropdown');
    if (bell && isPollingActive) {
        bell.style.position = 'relative';
        
        // Add a small green dot to indicate real-time is active
        const indicator = document.createElement('span');
        indicator.className = 'position-absolute';
        indicator.style.cssText = `
            top: 2px;
            right: 2px;
            width: 6px;
            height: 6px;
            background: #28a745;
            border-radius: 50%;
            border: 1px solid white;
        `;
        indicator.title = 'Real-time notifications active';
        
        bell.appendChild(indicator);
    }
}

// Add the real-time indicator after initialization
setTimeout(addRealTimeIndicator, 1000);
</script> 