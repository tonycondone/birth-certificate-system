<?php
// GOD TIER Payment Notification System - Dynamic Banner
// This file should be included in the homepage or relevant pages

use App\Services\PaymentNotificationService;

$notificationService = new \App\Services\PaymentNotificationService();
$users = $notificationService->detectUsersRequiringPaymentNotification();
$systemStatus = $notificationService->getPaymentSystemStatus();

// Get current user context
$currentUser = $_SESSION['user'] ?? null;
$userNotification = null;

if ($currentUser) {
    foreach ($users as $user) {
        if ($user['id'] == $currentUser['id']) {
            $userNotification = $notificationService->generateSmartMessage($user);
            break;
        }
    }
}

// Default notification for new users
if (!$userNotification && $currentUser) {
    $userNotification = [
        'title' => '🎯 Get Started',
        'message' => 'Begin your Digital Birth Certificate application',
        'cta' => 'Start Application',
        'color' => 'info',
        'icon' => 'target'
    ];
}
?>

<?php if ($userNotification): ?>
    <div class="payment-notification-banner alert alert-<?= $userNotification['color'] ?> alert-dismissible fade show" role="alert">
        <div class="container">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-<?= $userNotification['icon'] ?> fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="alert-heading mb-2"><?= $userNotification['title'] ?></h4>
                    <p class="mb-2"><?= $userNotification['message'] ?></p>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-check-circle me-1"></i>
                            <?= $systemStatus['status'] ?>
                        </span>
                        <span class="text-muted me-3">
                            <i class="fas fa-clock me-1"></i>
                            <?= $systemStatus['response_time'] ?>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="handlePaymentAction()">
                            <?= $userNotification['cta'] ?>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment notification system
    const notificationSystem = {
        currentUser: <?= json_encode($currentUser) ?>,
        systemStatus: <?= json_encode($systemStatus) ?>,
        
        init: function() {
            this.bindEvents();
            this.trackPerformance();
        },
        
        bindEvents: function() {
            // Track notification interactions
            document.querySelectorAll('.payment-notification-banner').forEach(banner => {
                banner.addEventListener('click', this.handleClick.bind(this));
                banner.addEventListener('close.bs.alert', this.handleDismiss.bind(this));
            });
        },
        
        handleClick: function(event) {
            const banner = event.target.closest('.payment-notification-banner');
            const action = banner.dataset.action || 'click';
            
            fetch('/api/track-notification', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: action,
                    user_id: this.currentUser?.id,
                    timestamp: new Date().toISOString()
                })
            });
        },
        
        handleDismiss: function(event) {
            const banner = event.target.closest('.payment-notification-banner');
            const action = 'dismiss';
            
            fetch('/api/track-notification', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: action,
                    user_id: this.currentUser?.id,
                    timestamp: new Date().toISOString()
                })
            });
        },
        
        trackPerformance: function() {
            // Track page load performance
            const loadTime = performance.now();
            fetch('/api/track-performance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    load_time: loadTime,
                    user_id: this.currentUser?.id
                })
            });
        }
    };
    
    notificationSystem.init();
});
</script>
