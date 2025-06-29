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

$pageTitle = 'My Profile - Digital Birth Certificate System';
require_once __DIR__ . '/layouts/base.php';
?>

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

<!-- Profile Section -->
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            My Profile
                        </h4>
                </div>
                <div class="card-body">
                        <!-- Profile Form -->
                        <form id="profileForm" method="POST" action="/profile/update" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">
                                        <i class="fas fa-user me-1"></i>First Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="firstName" 
                                           name="first_name" 
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" 
                                           required 
                                           minlength="2" 
                                           maxlength="100">
                                    <div class="invalid-feedback">
                                        Please enter your first name.
                                    </div>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">
                                        <i class="fas fa-user me-1"></i>Last Name
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lastName" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($currentUser['last_name']); ?>"
                                           required 
                                           minlength="2" 
                                           maxlength="100">
                                    <div class="invalid-feedback">
                                        Please enter your last name.
                                    </div>
                                </div>
                            </div>

                            <!-- Email (Read-only) -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                       readonly>
                                <div class="form-text">
                                    Email address cannot be changed. Contact support if needed.
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Phone Number
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone_number" 
                                       value="<?php echo htmlspecialchars($currentUser['phone_number'] ?? ''); ?>"
                                       required 
                                       minlength="10" 
                                       maxlength="20">
                                <div class="invalid-feedback">
                                    Please enter a valid phone number.
                        </div>
                        </div>
                        
                            <!-- Role Information (Read-only) -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-user-tag me-1"></i>Account Type
                                </label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-<?php echo getRoleBadgeColor($currentUser['role']); ?>">
                                        <?php echo ucfirst($currentUser['role']); ?>
                                    </span>
                        </div>
                        </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Update Profile
                        </button>
                            </div>
                    </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Account Status -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Account Status
                        </h5>
                </div>
                <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-circle text-success me-2"></i>
                            <span>Status: <strong>Active</strong></span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar me-2"></i>
                            <span>Member since: <strong><?php echo date('M j, Y', strtotime($currentUser['created_at'])); ?></strong></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock me-2"></i>
                            <span>Last updated: <strong><?php echo date('M j, Y', strtotime($currentUser['updated_at'])); ?></strong></span>
                        </div>
                </div>
            </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                </div>
                <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($currentUser['role'] === 'parent'): ?>
                                <a href="/applications/new" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>New Application
                                </a>
                                <a href="/applications" class="btn btn-outline-info">
                                    <i class="fas fa-list me-2"></i>My Applications
                                </a>
                            <?php elseif ($currentUser['role'] === 'hospital'): ?>
                                <a href="/verifications" class="btn btn-outline-primary">
                                    <i class="fas fa-check me-2"></i>Pending Verifications
                                </a>
                                <a href="/verifications/history" class="btn btn-outline-info">
                                    <i class="fas fa-history me-2"></i>Verification History
                                </a>
                            <?php endif; ?>
                            <a href="/settings" class="btn btn-outline-secondary">
                                <i class="fas fa-cog me-2"></i>Account Settings
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Recent Activity -->
        <?php if (!empty($recentActivity)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Recent Activity
                        </h5>
                </div>
                <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($recentActivity as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon bg-<?php echo getActivityColor($activity['type']); ?>">
                                    <i class="<?php echo $activity['icon']; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo getStatusColor($activity['status']); ?>">
                                            <?php echo htmlspecialchars($activity['status']); ?>
                                        </span>
                                        <small class="text-muted"><?php echo $activity['date']; ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.profile-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-icon {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #0d6efd;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    
    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        submitBtn.disabled = true;
        
        // Re-enable after 3 seconds (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
        
        form.classList.add('was-validated');
    });
});
</script>

<?php
// Helper functions for the view
function getRoleBadgeColor($role) {
    switch ($role) {
        case 'admin': return 'danger';
        case 'registrar': return 'warning';
        case 'hospital': return 'info';
        case 'parent': return 'success';
        default: return 'secondary';
    }
}

function getActivityColor($type) {
    switch ($type) {
        case 'application': return 'primary';
        case 'verification': return 'success';
        case 'certificate': return 'info';
        default: return 'secondary';
    }
}

function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        case 'verified': return 'info';
        default: return 'secondary';
    }
}
?>