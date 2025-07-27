<?php
// Dynamic payment notification banner system
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

    <script>
    function handlePaymentAction() {
        <?php if ($userNotification['user_type'] === 'pending_application'): ?>
            window.location.href = '/applications/<?= $user['application_id'] ?? '' ?>/pay';
        <?php elseif ($userNotification['user_type'] === 'new_user'): ?>
            window.location.href = '/applications/new';
        <?php else: ?>
            window.location.href = '/applications';
        <?php endif; ?>
    }
    </script>
<?php endif; ?>

<style>
.payment-notification-banner {
    position: sticky;
    top: 0;
    z-index: 1030;
    margin-bottom: 0;
    border-radius: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 576px) {
    .payment-notification-banner .container {
        padding: 0.5rem;
    }
    
    .payment-notification-banner .alert-heading {
        font-size: 1.1rem;
    }
    
    .payment-notification-banner p {
        font-size: 0.9rem;
    }
}
</style>
