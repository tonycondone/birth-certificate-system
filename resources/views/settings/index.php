<?php if (!isset($user)) { header('Location: /login'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Settings') ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .settings-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        .settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .nav-pills .nav-link {
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include BASE_PATH . '/resources/views/layouts/base.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card settings-card">
                    <div class="card-header settings-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Account Settings
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills p-3" id="v-pills-tab" role="tablist">
                            <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-profile" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>Profile Information
                            </button>
                            <button class="nav-link" id="v-pills-security-tab" data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>Security
                            </button>
                            <button class="nav-link" id="v-pills-applications-tab" data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-applications" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>My Applications
                            </button>
                            <button class="nav-link" id="v-pills-privacy-tab" data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-privacy" type="button" role="tab">
                                <i class="fas fa-user-shield me-2"></i>Privacy & Data
                            </button>
                            <button class="nav-link text-danger" id="v-pills-danger-tab" data-bs-toggle="pill" 
                                    data-bs-target="#v-pills-danger" type="button" role="tab">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="card settings-card mt-3">
                    <div class="card-body stat-card">
                        <h6 class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>Account Statistics
                        </h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 mb-0"><?= $stats['total_applications'] ?? 0 ?></div>
                                <small>Applications</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0"><?= $stats['unread_notifications'] ?? 0 ?></div>
                                <small>Unread Notifications</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel">
                        <div class="card settings-card">
                            <div class="card-header settings-header">
                                <h5 class="mb-0">Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="first_name" 
                                                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="last_name" 
                                                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                                        <div class="form-text">Contact admin to change email address.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?= ucfirst($user['role'] ?? '') ?>" readonly>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="v-pills-security" role="tabpanel">
                        <div class="card settings-card">
                            <div class="card-header settings-header">
                                <h5 class="mb-0">Security Settings</h5>
                            </div>
                            <div class="card-body">
                                <h6>Change Password</h6>
                                <form id="passwordForm">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                        <div class="form-text">Must be at least 8 characters long.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>

                                <hr class="my-4">

                                <h6>Account Security</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Last Login</span>
                                            <span class="text-muted">
                                                <?= $user['last_login_at'] ? date('M j, Y g:i A', strtotime($user['last_login_at'])) : 'Never' ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Account Created</span>
                                            <span class="text-muted">
                                                <?= date('M j, Y', strtotime($user['created_at'] ?? '')) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications Tab -->
                    <div class="tab-pane fade" id="v-pills-applications" role="tabpanel">
                        <div class="card settings-card">
                            <div class="card-header settings-header">
                                <h5 class="mb-0">My Applications</h5>
                            </div>
                            <div class="card-body">
                                <div id="applicationsContent">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading applications...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Tab -->
                    <div class="tab-pane fade" id="v-pills-privacy" role="tabpanel">
                        <div class="card settings-card">
                            <div class="card-header settings-header">
                                <h5 class="mb-0">Privacy & Data</h5>
                            </div>
                            <div class="card-body">
                                <h6>Data Export</h6>
                                <p class="text-muted">Download a copy of all your data stored in our system.</p>
                                <button type="button" class="btn btn-info" onclick="exportData()">
                                    <i class="fas fa-download me-2"></i>Export My Data
                                </button>

                                <hr class="my-4">

                                <h6>Privacy Settings</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">
                                        Receive email notifications
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="smsNotifications">
                                    <label class="form-check-label" for="smsNotifications">
                                        Receive SMS notifications
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone Tab -->
                    <div class="tab-pane fade" id="v-pills-danger" role="tabpanel">
                        <div class="card settings-card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                                </h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-danger">Delete Account</h6>
                                <p class="text-muted">
                                    Once you delete your account, there is no going back. Please be certain.
                                    You cannot delete your account if you have pending applications.
                                </p>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash me-2"></i>Delete Account
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Account Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. This will permanently delete your account and all associated data.
                    </div>
                    <form id="deleteAccountForm">
                        <div class="mb-3">
                            <label class="form-label">Type <strong>DELETE</strong> to confirm:</label>
                            <input type="text" class="form-control" name="confirmation" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enter your current password:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteAccount()">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile form submission
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/settings/update-profile', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Profile updated successfully!', 'success');
                } else {
                    showAlert(result.message || 'Failed to update profile', 'danger');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });

        // Password form submission
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Settings page loaded, attaching event listeners...');
            
            const passwordForm = document.getElementById('passwordForm');
            if (!passwordForm) {
                console.error('Password form not found!');
                return;
            }
            
            passwordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('Password form submitted');
                
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                console.log('Form data:', data);
                
                // Validate passwords match
                if (data.new_password !== data.confirm_password) {
                    showAlert('New passwords do not match', 'danger');
                    return;
                }
                
                // Validate password length
                if (data.new_password.length < 8) {
                    showAlert('Password must be at least 8 characters long', 'danger');
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Changing Password...';
                submitBtn.disabled = true;
                
                try {
                    console.log('Sending password change request...');
                    const response = await fetch('/settings/change-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(data)
                    });
                    
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const result = await response.json();
                    console.log('Response data:', result);
                    
                    if (result.success) {
                        showAlert('Password changed successfully!', 'success');
                        this.reset();
                    } else {
                        showAlert(result.message || 'Failed to change password', 'danger');
                    }
                } catch (error) {
                    console.error('Password change error:', error);
                    showAlert('An error occurred: ' + error.message, 'danger');
                } finally {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        });

        // Load applications
        async function loadApplications() {
            try {
                const response = await fetch('/settings/applications');
                const result = await response.json();
                
                if (result.success) {
                    displayApplications(result.applications);
                } else {
                    document.getElementById('applicationsContent').innerHTML = 
                        '<div class="alert alert-warning">No applications found.</div>';
                }
            } catch (error) {
                document.getElementById('applicationsContent').innerHTML = 
                    '<div class="alert alert-danger">Failed to load applications.</div>';
            }
        }

        // Make deleteApplication function global so it can be called from onclick
        window.deleteApplication = async function(id) {
            console.log('Delete application called with ID:', id);
            
            if (!confirm('Are you sure you want to delete this application?')) {
                console.log('User cancelled deletion');
                return;
            }
            
            try {
                console.log('Sending delete request for application ID:', id);
                const response = await fetch(`/settings/applications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Delete response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('Delete response data:', result);
                
                if (result.success) {
                    showAlert('Application deleted successfully!', 'success');
                    loadApplications(); // Reload the list
                } else {
                    showAlert(result.message || 'Failed to delete application', 'danger');
                }
            } catch (error) {
                console.error('Delete application error:', error);
                showAlert('An error occurred: ' + error.message, 'danger');
            }
        };

        function displayApplications(applications) {
            console.log('Displaying applications:', applications);
            
            if (applications.length === 0) {
                document.getElementById('applicationsContent').innerHTML = 
                    '<div class="alert alert-info">You have no applications yet.</div>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>' +
                      '<th>Application #</th><th>Child Name</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
            
            applications.forEach(app => {
                const statusClass = app.status === 'approved' ? 'success' : 
                                   app.status === 'rejected' ? 'danger' : 'warning';
                
                console.log('App can_delete:', app.can_delete, 'for app ID:', app.id);
                
                html += `<tr>
                    <td>${app.application_number || 'N/A'}</td>
                    <td>${app.child_name || 'N/A'}</td>
                    <td><span class="badge bg-${statusClass}">${app.status}</span></td>
                    <td>${new Date(app.submitted_at).toLocaleDateString()}</td>
                    <td>
                        ${app.can_delete ? 
                          `<button class="btn btn-sm btn-outline-danger" onclick="deleteApplication(${app.id})" title="Delete Application">
                             <i class="fas fa-trash"></i> Delete
                           </button>` : 
                          '<span class="text-muted">Cannot delete</span>'
                        }
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            document.getElementById('applicationsContent').innerHTML = html;
        }

        // Export data
        async function exportData() {
            try {
                const response = await fetch('/settings/export-data');
                const blob = await response.blob();
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'my-data-export.json';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                showAlert('Data exported successfully!', 'success');
            } catch (error) {
                showAlert('Failed to export data', 'danger');
            }
        }

        // Delete account
        async function deleteAccount() {
            const form = document.getElementById('deleteAccountForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            if (data.confirmation !== 'DELETE') {
                showAlert('Please type DELETE to confirm', 'danger');
                return;
            }
            
            try {
                const response = await fetch('/settings/delete-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    window.location.href = '/login?message=account_deleted';
                } else {
                    const result = await response.json();
                    showAlert(result.message || 'Failed to delete account', 'danger');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        }

        // Utility function to show alerts
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Load applications when the tab is shown
        document.getElementById('v-pills-applications-tab').addEventListener('shown.bs.tab', function() {
            loadApplications();
        });
    </script>
</body>
</html> 