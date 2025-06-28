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
            <h1 class="h3 mb-4">Profile Settings</h1>

            <!-- Profile Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="card-title h5 mb-0">Personal Information</h2>
                </div>
                <div class="card-body">
                    <form id="profileForm" class="needs-validation" novalidate>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" 
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" 
                                       class="form-control" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" 
                                       value="<?php echo htmlspecialchars($currentUser['last_name']); ?>"
                                       class="form-control" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" 
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                   class="form-control" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        
                        <?php if ($currentUser['role'] === 'parent'): ?>
                        <div class="mb-3">
                            <label class="form-label">National ID</label>
                            <input type="text" name="nationalId" 
                                   value="<?php echo htmlspecialchars($currentUser['national_id'] ?? ''); ?>"
                                   class="form-control" required>
                            <div class="invalid-feedback">Please enter your National ID.</div>
                        </div>
                        <?php endif; ?>

                        <?php if ($currentUser['role'] === 'hospital'): ?>
                        <div class="mb-3">
                            <label class="form-label">Hospital Registration Number</label>
                            <input type="text" name="hospitalId" 
                                   value="<?php echo htmlspecialchars($currentUser['hospital_id'] ?? ''); ?>"
                                   class="form-control" required>
                            <div class="invalid-feedback">Please enter your Hospital Registration Number.</div>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="card-title h5 mb-0">Change Password</h2>
                </div>
                <div class="card-body">
                    <form id="passwordForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="currentPassword" class="form-control" required>
                            <div class="invalid-feedback">Please enter your current password.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="newPassword" class="form-control" 
                                   pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" required>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long and include both letters and numbers.
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar" id="passwordStrength" role="progressbar"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirmPassword" class="form-control" required>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="savePasswordBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="card-title h5 mb-0">Notification Preferences</h2>
                </div>
                <div class="card-body">
                    <form id="notificationForm">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="emailNotifications" 
                                       id="emailNotifications">
                                <label class="form-check-label" for="emailNotifications">
                                    Email Notifications
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="smsNotifications" 
                                       id="smsNotifications">
                                <label class="form-check-label" for="smsNotifications">
                                    SMS Notifications
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="applicationUpdates" 
                                       id="applicationUpdates">
                                <label class="form-check-label" for="applicationUpdates">
                                    Application Status Updates
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveNotificationsBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Save Preferences
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="card-title h5 mb-0">Recent Activity</h2>
                </div>
                <div class="card-body">
                    <div id="activityLog">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Initialize password strength meter
    const passwordInput = document.querySelector('input[name="newPassword"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', updatePasswordStrength);
    }

    // Load initial data
    loadNotificationPreferences();
    loadActivityLog();

    // Handle profile form submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            const button = document.getElementById('saveProfileBtn');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(this);
            
            fetch('/api/profile/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('success', 'Success', 'Profile updated successfully');
                } else {
                    showNotification('error', 'Error', result.message || 'Error updating profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error', 'Error updating profile');
            })
            .finally(() => {
                button.disabled = false;
                spinner.classList.add('d-none');
            });
        }
    });

    // Handle password form submission
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            const formData = new FormData(this);
            
            if (formData.get('newPassword') !== formData.get('confirmPassword')) {
                showNotification('error', 'Error', 'New passwords do not match');
                return;
            }

            const button = document.getElementById('savePasswordBtn');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = true;
            spinner.classList.remove('d-none');

            fetch('/api/profile/change-password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('success', 'Success', 'Password updated successfully');
                    this.reset();
                    this.classList.remove('was-validated');
                } else {
                    showNotification('error', 'Error', result.message || 'Error updating password');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error', 'Error updating password');
            })
            .finally(() => {
                button.disabled = false;
                spinner.classList.add('d-none');
            });
        }
    });

    // Handle notification preferences form submission
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const button = document.getElementById('saveNotificationsBtn');
        const spinner = button.querySelector('.spinner-border');
        button.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(this);
        
        fetch('/api/profile/notification-preferences', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('success', 'Success', 'Notification preferences updated successfully');
            } else {
                showNotification('error', 'Error', result.message || 'Error updating preferences');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Error updating preferences');
        })
        .finally(() => {
            button.disabled = false;
            spinner.classList.add('d-none');
        });
    });
});

function updatePasswordStrength(e) {
    const password = e.target.value;
    const progressBar = document.getElementById('passwordStrength');
    let strength = 0;

    // Length check
    if (password.length >= 8) strength += 25;
    // Contains number
    if (/\d/.test(password)) strength += 25;
    // Contains letter
    if (/[a-zA-Z]/.test(password)) strength += 25;
    // Contains special character
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;

    progressBar.style.width = strength + '%';
    
    if (strength < 50) {
        progressBar.className = 'progress-bar bg-danger';
    } else if (strength < 75) {
        progressBar.className = 'progress-bar bg-warning';
    } else {
        progressBar.className = 'progress-bar bg-success';
    }
}

function loadNotificationPreferences() {
    fetch('/api/profile/notification-preferences')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preferences = data.preferences;
                document.getElementById('emailNotifications').checked = preferences.emailNotifications;
                document.getElementById('smsNotifications').checked = preferences.smsNotifications;
                document.getElementById('applicationUpdates').checked = preferences.applicationUpdates;
            } else {
                showNotification('error', 'Error', data.message || 'Error loading preferences');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Error loading preferences');
        });
}

function loadActivityLog() {
    fetch('/api/profile/activity-log')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const logContainer = document.getElementById('activityLog');
                if (data.activities.length === 0) {
                    logContainer.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                    `;
                    return;
                }

                logContainer.innerHTML = data.activities.map(activity => `
                    <div class="d-flex align-items-start mb-3">
                        <div class="activity-icon bg-${getActivityIconColor(activity.type)} me-3">
                            <i class="fas ${getActivityIcon(activity.type)}"></i>
                        </div>
                        <div>
                            <p class="mb-1">${activity.description}</p>
                            <small class="text-muted">
                                ${new Date(activity.timestamp).toLocaleString()}
                            </small>
                        </div>
                    </div>
                `).join('');
            } else {
                showNotification('error', 'Error', data.message || 'Error loading activity log');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Error loading activity log');
        });
}

function getActivityIcon(type) {
    const icons = {
        login: 'fa-sign-in-alt',
        logout: 'fa-sign-out-alt',
        profile_update: 'fa-user-edit',
        password_change: 'fa-key',
        application_submit: 'fa-file-alt',
        notification_update: 'fa-bell'
    };
    return icons[type] || 'fa-info-circle';
}

function getActivityIconColor(type) {
    const colors = {
        login: 'success',
        logout: 'secondary',
        profile_update: 'primary',
        password_change: 'warning',
        application_submit: 'info',
        notification_update: 'primary'
    };
    return colors[type] || 'secondary';
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
.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.bg-success { background-color: #28a745; }
.bg-danger { background-color: #dc3545; }
.bg-warning { background-color: #ffc107; }
.bg-info { background-color: #17a2b8; }
.bg-primary { background-color: #007bff; }
.bg-secondary { background-color: #6c757d; }

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.progress {
    height: 4px;
    margin-top: 0.5rem;
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
    
    .card {
        margin-bottom: 1rem;
    }
}
</style>
</div>
</div>