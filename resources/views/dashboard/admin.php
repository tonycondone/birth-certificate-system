<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user and verify admin role
$auth = new \App\Auth\Authentication();
$currentUser = $auth->getCurrentUser();

// Redirect if not admin
if (!$currentUser || $currentUser['role'] !== 'admin') {
    header('Location: /auth/login');
    exit;
}
?>

<?php require_once __DIR__ . '/../layouts/base.php'; ?>

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

<!-- Admin Dashboard -->
<div class="container-fluid px-4 py-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        <div class="btn-toolbar">
            <button type="button" class="btn btn-primary me-2" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-success" onclick="openAddUserModal()">
                <i class="fas fa-user-plus"></i> Add User
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-primary border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 fw-bold text-gray-800" id="totalUsers">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Applications -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-success border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Applications</div>
                            <div class="h5 mb-0 fw-bold text-gray-800" id="totalApplications">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-warning border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Verifications</div>
                            <div class="h5 mb-0 fw-bold text-gray-800" id="pendingVerifications">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificates Issued -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-info border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Certificates Issued</div>
                            <div class="h5 mb-0 fw-bold text-gray-800" id="certificatesIssued">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- User Management -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">User Management</h6>
                    <div class="input-group w-50">
                        <input type="text" class="form-control" placeholder="Search users..." id="userSearch">
                        <button class="btn btn-outline-secondary" type="button" onclick="searchUsers()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div id="activityLog">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">System Settings</h6>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        <div class="mb-3">
                            <label class="form-label">System Name</label>
                            <input type="text" name="systemName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Settings</label>
                            <input type="text" name="smtpHost" placeholder="SMTP Host" class="form-control mb-2">
                            <input type="text" name="smtpUser" placeholder="SMTP Username" class="form-control mb-2">
                            <input type="password" name="smtpPass" placeholder="SMTP Password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SMS Gateway</label>
                            <input type="text" name="smsApiKey" placeholder="API Key" class="form-control mb-2">
                            <input type="text" name="smsSecret" placeholder="API Secret" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="parent">Parent</option>
                            <option value="hospital">Hospital</option>
                            <option value="registrar">Registrar</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddUserForm()">Add User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="userId">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="parent">Parent</option>
                            <option value="hospital">Hospital</option>
                            <option value="registrar">Registrar</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitEditUserForm()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadUsers();
    loadActivityLog();
    loadSettings();

    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all modals
    var modalTriggerList = [].slice.call(document.querySelectorAll('.modal'));
    modalTriggerList.map(function (modalTriggerEl) {
        return new bootstrap.Modal(modalTriggerEl);
    });
});

// Load dashboard statistics
function loadDashboardStats() {
    fetch('/api/admin/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalUsers').textContent = data.data.totalUsers;
                document.getElementById('totalApplications').textContent = data.data.totalApplications;
                document.getElementById('pendingVerifications').textContent = data.data.pendingVerifications;
                document.getElementById('certificatesIssued').textContent = data.data.certificatesIssued;
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load dashboard statistics');
        });
}

// Load users table
function loadUsers(search = '') {
    const tbody = document.querySelector('#usersTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `;

    fetch(`/api/admin/users${search ? `?search=${encodeURIComponent(search)}` : ''}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = data.users.map(user => `
                    <tr>
                        <td>
                            <div class="fw-bold">${user.first_name} ${user.last_name}</div>
                            <div class="small text-muted">${user.email}</div>
                        </td>
                        <td>
                            <span class="badge bg-${getRoleBadgeColor(user.role)}">
                                ${user.role}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${user.status === 'active' ? 'success' : 'danger'}">
                                ${user.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})" 
                                    data-bs-toggle="tooltip" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})"
                                    data-bs-toggle="tooltip" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load users');
        });
}

// Load activity log
function loadActivityLog() {
    const container = document.getElementById('activityLog');
    
    fetch('/api/admin/activity-log?limit=10')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.logs.map(log => `
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="activity-icon bg-${getActivityIconColor(log.action)}">
                                <i class="fas ${getActivityIcon(log.action)}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-dark">${log.details}</div>
                            <div class="text-muted small">
                                ${new Date(log.timestamp).toLocaleString()}
                                ${log.first_name ? ` by ${log.first_name} ${log.last_name}` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load activity log');
        });
}

// Load settings
function loadSettings() {
    fetch('/api/admin/settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('settingsForm');
                Object.keys(data.settings).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = data.settings[key];
                    }
                });
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load settings');
        });
}

// Helper functions
function getRoleBadgeColor(role) {
    const colors = {
        admin: 'danger',
        registrar: 'primary',
        hospital: 'success',
        parent: 'info'
    };
    return colors[role] || 'secondary';
}

function getActivityIcon(action) {
    const icons = {
        user_created: 'fa-user-plus',
        user_updated: 'fa-user-edit',
        user_deleted: 'fa-user-minus',
        login: 'fa-sign-in-alt',
        logout: 'fa-sign-out-alt',
        application_submitted: 'fa-file-alt',
        application_approved: 'fa-check-circle',
        application_rejected: 'fa-times-circle',
        certificate_generated: 'fa-certificate'
    };
    return icons[action] || 'fa-info-circle';
}

function getActivityIconColor(action) {
    const colors = {
        user_created: 'success',
        user_updated: 'info',
        user_deleted: 'danger',
        login: 'primary',
        logout: 'secondary',
        application_submitted: 'info',
        application_approved: 'success',
        application_rejected: 'danger',
        certificate_generated: 'primary'
    };
    return colors[action] || 'secondary';
}

// Form handling
function submitAddUserForm() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);

    fetch('/api/admin/users', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Success', 'User added successfully');
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            form.reset();
            loadUsers();
        } else {
            showNotification('error', 'Error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Failed to add user');
    });
}

function submitEditUserForm() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    const userId = formData.get('userId');

    fetch(`/api/admin/users/${userId}`, {
        method: 'PUT',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Success', 'User updated successfully');
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            loadUsers();
        } else {
            showNotification('error', 'Error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Failed to update user');
    });
}

// User actions
function editUser(userId) {
    fetch(`/api/admin/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('editUserForm');
                form.querySelector('[name="userId"]').value = data.user.id;
                form.querySelector('[name="firstName"]').value = data.user.first_name;
                form.querySelector('[name="lastName"]').value = data.user.last_name;
                form.querySelector('[name="email"]').value = data.user.email;
                form.querySelector('[name="role"]').value = data.user.role;
                form.querySelector('[name="status"]').value = data.user.status;
                
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load user data');
        });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/api/admin/users/${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Success', 'User deleted successfully');
                loadUsers();
            } else {
                showNotification('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to delete user');
        });
    }
}

// Search functionality
function searchUsers() {
    const search = document.getElementById('userSearch').value;
    loadUsers(search);
}

// Notifications
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

// Refresh dashboard
function refreshDashboard() {
    loadDashboardStats();
    loadUsers();
    loadActivityLog();
}

// Auto-refresh every 5 minutes
setInterval(refreshDashboard, 300000);
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

.border-start.border-4 {
    border-left-width: 4px !important;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-toolbar .btn {
    display: inline-flex;
    align-items: center;
}

.btn-toolbar .btn i {
    margin-right: 0.5rem;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
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
    .btn-toolbar {
        margin-top: 1rem;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        margin-bottom: 1rem;
    }
}
</style>
</div>
</div>