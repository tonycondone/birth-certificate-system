
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user
$auth = new \App\Auth\Authentication();
$currentUser = $auth->getCurrentUser();

// Redirect if not authenticated
if (!$currentUser) {
    header('Location: /auth/login');
    exit;
}
?>

<?php require_once __DIR__ . '/layouts/base.php'; ?>

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="toastIcon" class="fas fa-info-circle me-2"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Notifications</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-2"></i>Mark All as Read
                    </button>
                    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="filterNotifications('all')">All Notifications</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterNotifications('unread')">Unread</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="filterNotifications('application')">Application Updates</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterNotifications('system')">System Messages</a></li>
                    </ul>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card shadow-sm">
                <div class="list-group list-group-flush" id="notificationsList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white border-top">
                    <nav aria-label="Notifications navigation">
                        <ul class="pagination justify-content-between align-items-center mb-0">
                            <li class="page-item">
                                <button class="btn btn-outline-primary btn-sm" onclick="previousPage()" id="prevButton" disabled>
                                    <i class="fas fa-chevron-left me-2"></i>Previous
                                </button>
                            </li>
                            <li class="text-muted small">
                                Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                            </li>
                            <li class="page-item">
                                <button class="btn btn-outline-primary btn-sm" onclick="nextPage()" id="nextButton" disabled>
                                    Next<i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent"></div>
                <div class="mt-4" id="modalActions"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentFilter = 'all';
let currentPage = 1;
let totalPages = 1;
let pageSize = 10;
let notificationModal;

document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
    
    // Auto-refresh notifications every minute
    setInterval(loadNotifications, 60000);
});

function loadNotifications() {
    const params = new URLSearchParams({
        page: currentPage,
        pageSize: pageSize,
        filter: currentFilter
    });

    fetch(`/api/notifications?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const { notifications, pagination } = data;
                totalPages = pagination.totalPages;
                updatePagination();
                renderNotifications(notifications);
            } else {
                showNotification('error', 'Error', data.message || 'Error loading notifications');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load notifications');
        });
}

function renderNotifications(notifications) {
    const container = document.getElementById('notificationsList');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">No notifications found</p>
            </div>
        `;
        return;
    }

    container.innerHTML = notifications.map(notification => `
        <div class="list-group-item list-group-item-action ${notification.read ? '' : 'bg-light'}"
             style="cursor: pointer;" onclick="showNotificationDetail(${notification.id})">
            <div class="d-flex align-items-start">
                <div class="notification-icon me-3 ${getNotificationColorClass(notification.type)}">
                    <i class="fas ${getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-1">${notification.title}</h6>
                        ${!notification.read ? `
                            <span class="badge bg-primary rounded-pill">New</span>
                        ` : ''}
                    </div>
                    <p class="mb-1 text-muted">${notification.preview}</p>
                    <small class="text-muted">
                        ${new Date(notification.timestamp).toLocaleString()}
                    </small>
                </div>
            </div>
        </div>
    `).join('');
}

function getNotificationIcon(type) {
    const icons = {
        application: 'fa-file-alt',
        system: 'fa-info-circle',
        warning: 'fa-exclamation-triangle',
        success: 'fa-check-circle',
        error: 'fa-times-circle'
    };
    return icons[type] || 'fa-bell';
}

function getNotificationColorClass(type) {
    const colors = {
        application: 'bg-primary',
        system: 'bg-info',
        warning: 'bg-warning',
        success: 'bg-success',
        error: 'bg-danger'
    };
    return colors[type] || 'bg-secondary';
}

function showNotificationDetail(id) {
    fetch(`/api/notifications/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notification = data.notification;
                document.getElementById('modalTitle').textContent = notification.title;
                document.getElementById('modalContent').innerHTML = notification.content;
                
                // Add action buttons if available
                const actionsContainer = document.getElementById('modalActions');
                actionsContainer.innerHTML = notification.actions ? notification.actions.map(action => `
                    <button onclick="handleNotificationAction('${action.type}', ${notification.id})" 
                            class="btn btn-primary me-2">
                        <i class="fas ${getActionIcon(action.type)} me-2"></i>${action.label}
                    </button>
                `).join('') : '';

                notificationModal.show();

                // Mark as read if unread
                if (!notification.read) {
                    markAsRead(id);
                }
            } else {
                showNotification('error', 'Error', data.message || 'Error loading notification details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load notification details');
        });
}

function getActionIcon(type) {
    const icons = {
        viewApplication: 'fa-eye',
        downloadCertificate: 'fa-download',
        verifyBirth: 'fa-check-circle'
    };
    return icons[type] || 'fa-arrow-right';
}

function markAsRead(id) {
    fetch(`/api/notifications/${id}/read`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                showNotification('error', 'Error', data.message || 'Error marking notification as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to mark notification as read');
        });
}

function markAllAsRead() {
    fetch('/api/notifications/mark-all-read', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Success', 'All notifications marked as read');
                loadNotifications();
            } else {
                showNotification('error', 'Error', data.message || 'Error marking notifications as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to mark all notifications as read');
        });
}

function filterNotifications(filter) {
    currentFilter = filter;
    currentPage = 1;
    loadNotifications();
}

function updatePagination() {
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    document.getElementById('prevButton').disabled = currentPage === 1;
    document.getElementById('nextButton').disabled = currentPage === totalPages;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        loadNotifications();
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadNotifications();
    }
}

function handleNotificationAction(type, notificationId) {
    switch (type) {
        case 'viewApplication':
            window.location.href = `/applications/view/${notificationId}`;
            break;
        case 'downloadCertificate':
            window.location.href = `/certificates/download/${notificationId}`;
            break;
        case 'verifyBirth':
            window.location.href = `/applications/verify/${notificationId}`;
            break;
        default:
            console.error('Unknown action type:', type);
            showNotification('error', 'Error', 'Unknown action type');
    }
    notificationModal.hide();
}

function showNotification(type, title, message) {
    const toast = document.getElementById('notificationToast');
    const toastInstance = new bootstrap.Toast(toast);
    
    // Set icon and color based on type
    const icon = document.getElementById('toastIcon');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger'} me-2`;
    
    // Set title and message
    document.getElementById('toastTitle').textContent = title;
    document.getElementById('toastMessage').textContent = message;
    
    toastInstance.show();
}
</script>

<style>
.notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.list-group-item {
    transition: transform 0.2s, background-color 0.2s;
}

.list-group-item:hover {
    transform: translateX(5px);
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.toast {
    background-color: white;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast-header {
    background-color: white;
    border-bottom: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .btn-group {
        width: 100%;
        margin-top: 1rem;
    }
    
    .list-group-item {
        padding: 1rem;
    }
}
</style>
</div>
</div>