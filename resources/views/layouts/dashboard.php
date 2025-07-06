<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard - Digital Birth Certificate System' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Dashboard CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --info-color: #0891b2;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            color: #3b82f6;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            position: relative;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            color: white;
            border-right: 3px solid #3b82f6;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .nav-badge {
            background-color: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        /* Top Navigation */
        .top-navbar {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: #f1f5f9;
            color: #334155;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .notification-bell {
            position: relative;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .notification-bell:hover {
            background-color: #f1f5f9;
            color: #334155;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 1rem;
            transform: translate(25%, -25%);
        }

        .user-dropdown {
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .page-subtitle {
            color: #64748b;
            margin-top: 0.5rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-icon.primary { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stats-icon.success { background: linear-gradient(135deg, #059669, #047857); }
        .stats-icon.warning { background: linear-gradient(135deg, #d97706, #b45309); }
        .stats-icon.danger { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .stats-icon.info { background: linear-gradient(135deg, #0891b2, #0e7490); }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }

        .stats-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .stats-change.positive {
            color: #059669;
        }

        .stats-change.negative {
            color: #dc2626;
        }

        /* Tables */
        .table {
            margin: 0;
        }

        .table th {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
        }

        .badge.status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge.status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge.status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge.status-under-review {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 1rem;
            }
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="sidebar-brand">
                <i class="fas fa-certificate"></i>
                <span class="brand-text">Birth Cert System</span>
            </a>
        </div>
        
        <div class="sidebar-nav">
            <?php
            $currentPath = $_SERVER['REQUEST_URI'];
            $userRole = $_SESSION['role'] ?? 'parent';
            
            // Define navigation items based on role
            $navItems = [];
            
            switch ($userRole) {
                case 'admin':
                    $navItems = [
                        ['url' => '/admin/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                        ['url' => '/admin/users', 'icon' => 'fas fa-users', 'label' => 'User Management'],
                        ['url' => '/admin/applications', 'icon' => 'fas fa-file-alt', 'label' => 'Applications'],
                        ['url' => '/admin/certificates', 'icon' => 'fas fa-certificate', 'label' => 'Certificates'],
                        ['url' => '/admin/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
                        ['url' => '/admin/settings', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
                        ['url' => '/admin/monitoring', 'icon' => 'fas fa-heartbeat', 'label' => 'System Health'],
                    ];
                    break;
                    
                case 'registrar':
                    $pendingCount = $pendingCount ?? 0;
                    $navItems = [
                        ['url' => '/registrar/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                        ['url' => '/dashboard/pending', 'icon' => 'fas fa-clock', 'label' => 'Pending Reviews', 'badge' => $pendingCount],
                        ['url' => '/dashboard/approved', 'icon' => 'fas fa-check-circle', 'label' => 'Approved'],
                        ['url' => '/registrar/batch-process', 'icon' => 'fas fa-layer-group', 'label' => 'Batch Process'],
                        ['url' => '/dashboard/reports', 'icon' => 'fas fa-chart-line', 'label' => 'Reports'],
                        ['url' => '/dashboard/settings', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
                    ];
                    break;
                    
                case 'hospital':
                    $navItems = [
                        ['url' => '/hospital/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                        ['url' => '/hospital/records', 'icon' => 'fas fa-file-medical', 'label' => 'Birth Records'],
                        ['url' => '/hospital/verifications', 'icon' => 'fas fa-shield-alt', 'label' => 'Verifications'],
                        ['url' => '/hospital/submissions', 'icon' => 'fas fa-upload', 'label' => 'Submissions'],
                        ['url' => '/hospital/settings', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
                    ];
                    break;
                    
                case 'parent':
                default:
                    $navItems = [
                        ['url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                        ['url' => '/applications/new', 'icon' => 'fas fa-plus-circle', 'label' => 'New Application'],
                        ['url' => '/applications', 'icon' => 'fas fa-file-alt', 'label' => 'My Applications'],
                        ['url' => '/certificates', 'icon' => 'fas fa-certificate', 'label' => 'My Certificates'],
                        ['url' => '/track', 'icon' => 'fas fa-search', 'label' => 'Track Application'],
                        ['url' => '/profile', 'icon' => 'fas fa-user', 'label' => 'Profile'],
                        ['url' => '/notifications', 'icon' => 'fas fa-bell', 'label' => 'Notifications'],
                    ];
                    break;
            }
            
            foreach ($navItems as $item):
                $isActive = strpos($currentPath, $item['url']) === 0;
            ?>
                <div class="nav-item">
                    <a href="<?= $item['url'] ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span class="nav-text"><?= $item['label'] ?></span>
                        <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
                            <span class="nav-badge"><?= $item['badge'] ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
            
            <!-- Logout -->
            <div class="nav-item mt-4">
                <a href="/auth/logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <div class="top-navbar">
            <div class="navbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-breadcrumb">
                    <span class="text-muted"><?= ucfirst($userRole) ?> Portal</span>
                </div>
            </div>
            
            <div class="navbar-right">
                <!-- Notifications -->
                <button class="notification-bell" onclick="showNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                
                <!-- User Dropdown -->
                <div class="user-dropdown">
                    <div class="user-avatar" onclick="toggleUserMenu()">
                        <?= strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $_SESSION['warning'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= $_SESSION['info'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['info']); ?>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dashboard JavaScript -->
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Mobile Sidebar Toggle
        if (window.innerWidth <= 768) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
            });
        }

        // Notifications
        function showNotifications() {
            Swal.fire({
                title: 'Notifications',
                html: `
                    <div class="text-start">
                        <div class="notification-item p-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <div>
                                    <div class="fw-bold">Application Approved</div>
                                    <small class="text-muted">Your birth certificate application has been approved</small>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item p-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-warning me-3"></i>
                                <div>
                                    <div class="fw-bold">Payment Pending</div>
                                    <small class="text-muted">Please complete payment for application #BC2024001</small>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item p-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-info me-3"></i>
                                <div>
                                    <div class="fw-bold">System Update</div>
                                    <small class="text-muted">New features have been added to the system</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: '500px'
            });
        }

        // User Menu
        function toggleUserMenu() {
            Swal.fire({
                title: '<?= $_SESSION['first_name'] ?? 'User' ?> <?= $_SESSION['last_name'] ?? '' ?>',
                html: `
                    <div class="text-start">
                        <a href="/profile" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="/settings" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <hr>
                        <a href="/auth/logout" class="btn btn-outline-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: '300px'
            });
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Loading states for buttons
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading-spinner me-2"></span>Loading...';
            button.disabled = true;
            
            return function() {
                button.innerHTML = originalText;
                button.disabled = false;
            };
        }

        // Confirmation dialogs
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        // Success notification
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Error notification
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message
            });
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>

    <!-- Page-specific scripts -->
    <?= $scripts ?? '' ?>
</body>
</html>
