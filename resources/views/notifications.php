<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/layouts/base.php';
?>

<style>
.notification-item {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.notification-item.unread {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.notification-item:hover {
    background-color: #f1f3f4;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
}

.notification-icon.info { background-color: #17a2b8; }
.notification-icon.success { background-color: #28a745; }
.notification-icon.warning { background-color: #ffc107; color: #212529; }
.notification-icon.error { background-color: #dc3545; }
.notification-icon.announcement { background-color: #6f42c1; }

.priority-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: bold;
    text-transform: uppercase;
}

.priority-urgent { background-color: #dc3545; color: white; }
.priority-high { background-color: #fd7e14; color: white; }
.priority-normal { background-color: #6c757d; color: white; }
.priority-low { background-color: #28a745; color: white; }

.stats-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.filter-pills .nav-link {
    border-radius: 20px;
    margin-right: 8px;
    margin-bottom: 8px;
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s ease;
}

.filter-pills .nav-link.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.notification-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

@media (max-width: 768px) {
    .notification-actions {
        opacity: 1;
    }
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bell me-2"></i>Notifications
            </h1>
            <p class="text-muted mb-0">Stay updated with your birth certificate applications</p>
        </div>
        
        <div class="d-flex gap-2">
            <button id="markAllReadBtn" class="btn btn-outline-primary" <?php echo ($stats['unread'] ?? 0) == 0 ? 'disabled' : ''; ?>>
                <i class="fas fa-check-double me-1"></i>Mark All Read
            </button>
            <button id="refreshBtn" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-envelope fa-2x text-primary me-2"></i>
                        <h3 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    </div>
                    <p class="text-muted mb-0">Total Notifications</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-envelope-open fa-2x text-warning me-2"></i>
                        <h3 class="mb-0"><?php echo $stats['unread'] ?? 0; ?></h3>
                    </div>
                    <p class="text-muted mb-0">Unread</p>
                </div>
    </div>
</div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger me-2"></i>
                        <h3 class="mb-0"><?php echo $stats['urgent'] ?? 0; ?></h3>
                    </div>
                    <p class="text-muted mb-0">Urgent</p>
                </div>
                </div>
            </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-calendar-day fa-2x text-success me-2"></i>
                        <h3 class="mb-0"><?php echo $stats['today'] ?? 0; ?></h3>
                    </div>
                    <p class="text-muted mb-0">Today</p>
                </div>
            </div>
        </div>
                        </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-2">Filter by Type:</h6>
                    <div class="nav filter-pills">
                        <a class="nav-link <?php echo ($_GET['type'] ?? 'all') === 'all' ? 'active' : ''; ?>" 
                           href="?type=all&status=<?php echo $_GET['status'] ?? 'all'; ?>">All</a>
                        <a class="nav-link <?php echo ($_GET['type'] ?? '') === 'info' ? 'active' : ''; ?>" 
                           href="?type=info&status=<?php echo $_GET['status'] ?? 'all'; ?>">Info</a>
                        <a class="nav-link <?php echo ($_GET['type'] ?? '') === 'success' ? 'active' : ''; ?>" 
                           href="?type=success&status=<?php echo $_GET['status'] ?? 'all'; ?>">Success</a>
                        <a class="nav-link <?php echo ($_GET['type'] ?? '') === 'warning' ? 'active' : ''; ?>" 
                           href="?type=warning&status=<?php echo $_GET['status'] ?? 'all'; ?>">Warning</a>
                        <a class="nav-link <?php echo ($_GET['type'] ?? '') === 'error' ? 'active' : ''; ?>" 
                           href="?type=error&status=<?php echo $_GET['status'] ?? 'all'; ?>">Error</a>
                        <a class="nav-link <?php echo ($_GET['type'] ?? '') === 'announcement' ? 'active' : ''; ?>" 
                           href="?type=announcement&status=<?php echo $_GET['status'] ?? 'all'; ?>">Announcements</a>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="mb-2">Filter by Status:</h6>
                    <div class="nav filter-pills">
                        <a class="nav-link <?php echo ($_GET['status'] ?? 'all') === 'all' ? 'active' : ''; ?>" 
                           href="?type=<?php echo $_GET['type'] ?? 'all'; ?>&status=all">All</a>
                        <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'unread' ? 'active' : ''; ?>" 
                           href="?type=<?php echo $_GET['type'] ?? 'all'; ?>&status=unread">Unread</a>
                        <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'read' ? 'active' : ''; ?>" 
                           href="?type=<?php echo $_GET['type'] ?? 'all'; ?>&status=read">Read</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h4>No Notifications Found</h4>
                    <p>You don't have any notifications matching the selected filters.</p>
                    <a href="/notifications" class="btn btn-primary">
                        <i class="fas fa-refresh me-1"></i>View All Notifications
                    </a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                             data-id="<?php echo $notification['id']; ?>">
                            <div class="d-flex align-items-start">
                                <!-- Icon -->
                                <div class="notification-icon <?php echo $notification['type']; ?> me-3">
                                    <?php
                                    $icons = [
                                        'info' => 'fas fa-info-circle',
                                        'success' => 'fas fa-check-circle',
                                        'warning' => 'fas fa-exclamation-triangle',
                                        'error' => 'fas fa-times-circle',
                                        'announcement' => 'fas fa-bullhorn'
                                    ];
                                    echo '<i class="' . ($icons[$notification['type']] ?? 'fas fa-bell') . '"></i>';
                                    ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 notification-title">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary ms-2">New</span>
                                            <?php endif; ?>
                                        </h6>
                                        
                                        <div class="d-flex align-items-center">
                                            <?php if ($notification['priority'] !== 'normal'): ?>
                                                <span class="priority-badge priority-<?php echo $notification['priority']; ?> me-2">
                                                    <?php echo ucfirst($notification['priority']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <div class="notification-actions">
                                                <?php if (!$notification['is_read']): ?>
                                                    <button class="btn btn-sm btn-outline-primary mark-read-btn me-1" 
                                                            data-id="<?php echo $notification['id']; ?>"
                                                            title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                        data-id="<?php echo $notification['id']; ?>"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
        </div>
    </div>
</div>

                                    <p class="mb-1 text-muted notification-message">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $notification['time_ago']; ?>
                                        </small>
                                        
                                        <?php if (!empty($notification['metadata'])): ?>
                                            <?php
                                            $metadata = json_decode($notification['metadata'], true);
                                            if (isset($metadata['application_id'])):
                                            ?>
                                                <a href="/applications/<?php echo $metadata['application_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>View Application
                                                </a>
                                            <?php elseif (isset($metadata['certificate_number'])): ?>
                                                <a href="/verify?certificate_number=<?php echo $metadata['certificate_number']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-certificate me-1"></i>View Certificate
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
            </div>
            </div>
                    <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Notifications pagination">
                <ul class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&type=<?php echo $_GET['type'] ?? 'all'; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $_GET['type'] ?? 'all'; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&type=<?php echo $_GET['type'] ?? 'all'; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert">
        <div class="toast-header">
            <i id="toastIcon" class="fas fa-info-circle me-2"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark individual notification as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            markAsRead(id);
        });
    });

    // Mark all notifications as read
    document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
        if (confirm('Mark all notifications as read?')) {
            markAllAsRead();
        }
    });

    // Delete notification
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            if (confirm('Delete this notification?')) {
                deleteNotification(id);
            }
        });
    });

    // Refresh notifications
    document.getElementById('refreshBtn')?.addEventListener('click', function() {
        location.reload();
    });

    // Click notification to mark as read
    document.querySelectorAll('.notification-item.unread').forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            markAsRead(id);
        });
    });

    // Functions
    function markAsRead(id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const badge = item.querySelector('.badge');
                    if (badge) badge.remove();
                    const markBtn = item.querySelector('.mark-read-btn');
                    if (markBtn) markBtn.remove();
                }
                showToast('success', 'Notification marked as read');
                updateStats();
            } else {
                showToast('error', data.message || 'Failed to mark as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'An error occurred');
        });
    }

    function markAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast('error', data.message || 'Failed to mark all as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'An error occurred');
        });
    }

    function deleteNotification(id) {
        fetch(`/notifications/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => item.remove(), 300);
                }
                showToast('success', 'Notification deleted');
                updateStats();
            } else {
                showToast('error', data.message || 'Failed to delete notification');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'An error occurred');
        });
    }

    function showToast(type, message) {
    const toast = document.getElementById('notificationToast');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');

        const icons = {
            success: 'fas fa-check-circle text-success',
            error: 'fas fa-times-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-info'
        };

        toastIcon.className = icons[type] || icons.info;
        toastTitle.textContent = type.charAt(0).toUpperCase() + type.slice(1);
        toastMessage.textContent = message;

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    function updateStats() {
        // Update unread count in stats
        setTimeout(() => {
            const unreadItems = document.querySelectorAll('.notification-item.unread').length;
            const unreadStat = document.querySelector('.stats-card h3');
            if (unreadStat) {
                unreadStat.textContent = unreadItems;
            }
        }, 100);
    }

    // Auto-refresh every 30 seconds
    setInterval(() => {
        fetch('/notifications/get-unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    // Show notification badge in navbar if exists
                    const badge = document.querySelector('#notificationBadge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.classList.remove('d-none');
                    }
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    }, 30000);
});
</script>