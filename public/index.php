<?php
/**
 * Digital Birth Certificate System
 * Main Entry Point
 */

// Enable detailed error reporting (will be adjusted below per endpoint)
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start a global output buffer to prevent stray warnings breaking JSON endpoints
if (ob_get_level() === 0) { ob_start(); }

define('BASE_PATH', dirname(__DIR__));

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
} elseif (file_exists(__DIR__ . '/../env.example')) {
    $envFile = __DIR__ . '/../env.example';
} else {
    die('Environment file not found. Please copy env.example to .env');
}

// Load environment variables
$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);
foreach ($envLines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Set secure session cookie parameters
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Start session
session_start();

// Set error reporting (default from APP_DEBUG)
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load database connection
use App\Services\DependencyContainer;
use App\Controllers\CertificateController;

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove trailing slash
$path = rtrim($path, '/');

// If path is empty, set to home
if (empty($path)) {
    $path = '/';
}

// For API-like endpoints that must return JSON, force suppress display errors
if (preg_match('#^/applications/\d+/initialize-payment$#', $path) ||
    preg_match('#^/paystack/webhook$#', $path)) {
    ini_set('display_errors', '0');
}

// Debug information
if ($_ENV['APP_DEBUG'] ?? false) {
    // echo "<!-- Debug Info: Path=$path, Method=$requestMethod -->\n";
}

// Route definitions
$routes = [
    // Home and authentication routes
    '/' => 'App\Controllers\HomeController@index',
    '/home' => 'App\Controllers\HomeController@index',
    '/login' => 'App\Controllers\AuthController@showLogin',
    '/login/process' => 'App\Controllers\AuthController@login',
    '/register' => 'App\Controllers\AuthController@showRegister',
    '/register/process' => 'App\Controllers\AuthController@register',
    '/logout' => 'App\Controllers\AuthController@logout',
    '/auth/logout' => 'App\Controllers\AuthController@logout',
    '/auth/forgot-password' => 'App\Controllers\AuthController@showForgotPassword',
    '/auth/reset-password' => 'App\Controllers\AuthController@showResetPassword',
    '/auth/2fa' => 'App\Controllers\AuthController@showTwoFactorAuth',
    '/auth/verify' => 'App\Controllers\AuthController@verifyEmail',
    '/auth/verify-email' => 'App\Controllers\AuthController@verifyEmail',
    
    // Dashboard routes
    '/dashboard' => 'App\Controllers\DashboardController@index',
    '/dashboard/registrar' => 'App\Controllers\DashboardController@registrar',
    '/dashboard/hospital' => 'App\Controllers\DashboardController@hospital',
    '/dashboard/parent' => 'App\Controllers\DashboardController@parent',
    '/dashboard/admin' => 'App\Controllers\DashboardController@admin',
    '/dashboard/pending' => 'App\Controllers\DashboardController@pending',
    '/dashboard/approved' => 'App\Controllers\DashboardController@approved',
    '/dashboard/reports' => 'App\Controllers\DashboardController@reports',
    '/dashboard/settings' => 'App\Controllers\DashboardController@settings',
    '/dashboard/registrar/approved' => 'App\Controllers\RegistrarController@approved',
    '/dashboard/registrar/reports' => 'App\Controllers\RegistrarController@reports',
    '/dashboard/reports/daily' => 'App\Controllers\ReportsController@daily',
    '/dashboard/reports/weekly' => 'App\Controllers\ReportsController@weekly',
    '/dashboard/reports/monthly' => 'App\Controllers\ReportsController@monthly',
    
    // Admin routes
    '/admin' => 'App\Controllers\AdminPortalController@dashboard',
    '/admin/dashboard' => 'App\Controllers\AdminPortalController@dashboard',
    '/admin/users' => 'App\Controllers\AdminPortalController@users',
    '/admin/users/create' => 'App\Controllers\AdminPortalController@createUser',
    '/admin/users/store' => 'App\Controllers\AdminPortalController@storeUser',
    '/admin/users/{id}' => 'App\Controllers\AdminPortalController@showUser',
    '/admin/users/{id}/edit' => 'App\Controllers\AdminPortalController@editUser',
    '/admin/users/{id}/update' => 'App\Controllers\AdminPortalController@updateUser',
    '/admin/users/{id}/delete' => 'App\Controllers\AdminPortalController@deleteUser',
    '/admin/users/bulk-action' => 'App\Controllers\AdminPortalController@bulkUserAction',
    '/admin/users/export' => 'App\Controllers\AdminPortalController@exportUsers',
    '/admin/users/import' => 'App\Controllers\AdminPortalController@importUsers',
    '/admin/user-action' => 'App\Controllers\AdminPortalController@userAction',
    '/admin/applications' => 'App\Controllers\AdminController@applications',
    '/admin/applications/create' => 'App\Controllers\AdminController@createApplication',
    '/admin/applications/{id}' => 'App\Controllers\AdminController@showApplication',
    '/admin/applications/{id}/edit' => 'App\Controllers\AdminController@editApplication',
    '/admin/applications/{id}/approve' => 'App\Controllers\AdminController@approveApplication',
    '/admin/applications/{id}/reject' => 'App\Controllers\AdminController@rejectApplication',
    '/admin/applications/bulk-action' => 'App\Controllers\AdminController@bulkApplicationAction',
    '/admin/applications/export' => 'App\Controllers\AdminController@exportApplications',
    '/admin/generic-applications' => 'App\Controllers\AdminController@genericApplications',
    '/admin/applications/download/{id}' => 'App\Controllers\ApplicationController@download',
    '/admin/certificates' => 'App\Controllers\AdminPortalController@certificates',
    '/admin/certificates/create' => 'App\Controllers\AdminController@createCertificate',
    '/admin/certificates/{id}' => 'App\Controllers\AdminController@showCertificate',
    '/admin/certificates/{id}/edit' => 'App\Controllers\AdminController@editCertificate',
    '/admin/certificates/{id}/revoke' => 'App\Controllers\AdminController@revokeCertificate',
    '/admin/certificates/bulk-action' => 'App\Controllers\AdminController@bulkCertificateAction',
    '/admin/certificates/templates' => 'App\Controllers\AdminController@certificateTemplates',
    '/admin/certificates/download/{id}' => 'App\Controllers\CertificateController@download',
    '/admin/monitoring' => 'App\Controllers\AdminPortalController@systemMonitoring',
    '/admin/settings' => 'App\Controllers\AdminPortalController@settings',
    '/admin/reports' => 'App\Controllers\AdminPortalController@reports',
    '/admin/backup' => 'App\Controllers\AdminPortalController@backup',
    '/admin/backup/create' => 'App\Controllers\AdminPortalController@createBackup',
    '/admin/backup/restore' => 'App\Controllers\AdminPortalController@restoreBackup',
    '/admin/audit-trail' => 'App\Controllers\AdminPortalController@auditTrail',
    '/admin/system-health' => 'App\Controllers\AdminPortalController@systemHealth',
    '/admin/logs' => 'App\Controllers\AdminPortalController@systemLogs',
    '/admin/mail-templates' => 'App\Controllers\AdminPortalController@mailTemplates',
    '/admin/mail-templates/create' => 'App\Controllers\AdminPortalController@createMailTemplate',
    '/admin/mail-templates/{id}/edit' => 'App\Controllers\AdminPortalController@editMailTemplate',
    '/admin/notifications' => 'App\Controllers\AdminPortalController@notifications',
    '/admin/api-keys' => 'App\Controllers\AdminPortalController@apiKeys',
    '/admin/webhooks' => 'App\Controllers\AdminPortalController@webhooks',
    '/admin/system-monitoring' => 'App\Controllers\AdminPortalController@systemMonitoring',
    
    // Application routes
    '/applications' => 'App\Controllers\ApplicationController@index',
    '/applications/new' => 'App\Controllers\ApplicationController@create',
    '/applications/submit' => 'App\Controllers\ApplicationController@showSubmitForm',
    '/applications/create' => 'App\Controllers\ApplicationController@create',
    '/applications/{id}' => 'App\Controllers\ApplicationController@show',
    '/applications/{id}/pay' => 'App\Controllers\PaymentController@pay',
    '/applications/{id}/initialize-payment' => 'App\Controllers\PaymentController@initializePayment',
    '/applications/{id}/payment-callback' => 'App\Controllers\PaymentController@callback',
    '/applications/{id}/delete' => 'App\Controllers\ApplicationController@delete',
    '/applications/download/{id}' => 'App\Controllers\ApplicationController@download',
    '/applications/approve/{id}' => 'App\Controllers\ApplicationController@approve',
    '/applications/reject/{id}' => 'App\Controllers\ApplicationController@reject',
    
    // Generic Application Submission
    '/applications/submit/store' => 'App\Controllers\GenericApplicationController@store',
    
    // Certificate routes
    '/certificates' => 'App\Controllers\CertificateController@index',
    '/certificates/{id}' => 'App\Controllers\CertificateController@show',
    '/certificates/{id}/download' => 'App\Controllers\CertificateController@download',
    '/certificates/{id}/verify' => 'App\Controllers\CertificateController@verify',
    '/certificate/apply' => 'App\Controllers\CertificateController@apply',
    '/certificate/verify' => 'App\Controllers\CertificateController@verifyFromRequest',
    '/certificate/approve' => 'App\Controllers\CertificateController@approveApplication',
    '/certificate/download' => 'App\Controllers\CertificateController@downloadCertificate',
    '/certificate/sample' => 'App\Controllers\CertificateController@sample',
    '/certificate/generate/{id}' => 'App\Controllers\CertificateController@generate',
    '/certificate/list' => 'App\Controllers\CertificateController@listCertificates',
    '/certificates/download/{id}' => 'App\Controllers\CertificateController@download',
    '/certificates/generate/{id}' => 'App\Controllers\CertificateController@generate',
    '/certificates/verify/{id}' => 'App\Controllers\CertificateController@verifyCertificate',
    '/certificates/reject/{id}' => 'App\Controllers\CertificateController@rejectCertificate',
    '/certificates/approve/{id}' => 'App\Controllers\CertificateController@approveApplication',
    
    // Verification routes
    '/verify' => 'App\Controllers\CertificateController@verify',
    '/verify/{number}' => 'App\Controllers\CertificateController@verify',
    '/verify/certificate/{number}' => 'App\Controllers\CertificateController@validateCertificate',
    '/verifications' => 'App\Controllers\CertificateController@verifications',
    '/verifications/history' => 'App\Controllers\CertificateController@verificationHistory',
    
    // Tracking routes
    '/track' => 'App\Controllers\TrackingController@showTrackingForm',
    '/track/{trackingNumber}' => 'App\Controllers\TrackingController@trackApplication',
    '/track/search' => 'App\Controllers\TrackingController@search',
    
    // User profile routes
    '/profile' => 'App\Controllers\UserController@profile',
    '/profile/update' => 'App\Controllers\UserController@updateProfile',
    '/profile/change-password' => 'App\Controllers\UserController@changePassword',
    '/settings' => 'App\Controllers\UserController@settings',
    '/user/delete-account' => 'App\Controllers\UserController@deleteAccount',
    
    // Notification routes
    '/notifications' => 'App\Controllers\NotificationController@index',
    '/notifications/{id}/mark-as-read' => 'App\Controllers\NotificationController@markAsRead',
    '/notifications/mark-all-as-read' => 'App\Controllers\NotificationController@markAllAsRead',
    
    // Payment routes
    '/paystack/webhook' => 'App\Controllers\PaymentController@webhook',
    '/mock-payment/{id}/{reference}' => 'App\Controllers\MockPaymentController@showPaymentPage',
    '/mock-payment/{id}/{reference}/process' => 'App\Controllers\MockPaymentController@processPayment',
    
    // Hospital routes
    '/hospital/submissions' => 'App\Controllers\AdminController@hospitalSubmissions',
    '/hospital/dashboard' => 'App\Controllers\AdminController@hospitalDashboard',
    '/hospital/records' => 'App\Controllers\AdminController@hospitalRecords',
    '/hospital/records/new' => 'App\Controllers\AdminController@hospitalRecordCreate',
    '/hospital/records/{id}' => 'App\Controllers\AdminController@hospitalRecordShow',
    '/hospital/records/{id}/edit' => 'App\Controllers\AdminController@hospitalRecordEdit',
    '/hospital/records/download/{id}' => 'App\Controllers\AdminController@hospitalRecordDownload',
    '/hospital/verifications' => 'App\Controllers\AdminController@hospitalVerifications',
    '/hospital/verify/{id}' => 'App\Controllers\AdminController@hospitalVerify',
    '/hospital/settings' => 'App\Controllers\AdminController@hospitalSettings',
    
    // Registrar routes
    '/registrar/dashboard' => 'App\Controllers\RegistrarController@dashboard',
    '/registrar/pending' => 'App\Controllers\RegistrarController@pendingApplications',
    '/registrar/review/{id}' => 'App\Controllers\RegistrarController@reviewApplication',
    '/registrar/process' => 'App\Controllers\RegistrarController@processApplication',
    '/registrar/batch-process' => 'App\Controllers\RegistrarController@batchProcess',
    '/registrar/reports' => 'App\Controllers\RegistrarController@reports',
    '/registrar/applications' => 'App\Controllers\AdminController@registrarApplications',
    '/registrar/approved' => 'App\Controllers\AdminController@registrarApproved',
    '/registrar/settings' => 'App\Controllers\AdminController@registrarSettings',
    '/registrar/certificates' => 'App\Controllers\CertificateController@listCertificates',
    '/registrar/certificates/download/{id}' => 'App\Controllers\CertificateController@download',
    '/registrar/create-table' => 'App\Controllers\RegistrarController@createTable',
    
    // Feedback routes
    '/applications/{id}/feedback' => 'App\Controllers\FeedbackController@create',
    '/applications/feedback/store' => 'App\Controllers\FeedbackController@store',
    
    // API routes
    '/api/certificate/verify' => 'App\Controllers\CertificateController@apiVerify',
    '/api/certificates/download/{id}' => 'App\Controllers\CertificateController@download',
    '/api/certificates/generate/{id}' => 'App\Controllers\CertificateController@generate',
    
    // Static pages
    '/about' => 'App\Controllers\StaticPageController@about',
    '/contact' => 'App\Controllers\StaticPageController@contact',
    '/faq' => 'App\Controllers\StaticPageController@faq',
    '/privacy' => 'App\Controllers\StaticPageController@privacy',
    '/terms' => 'App\Controllers\StaticPageController@terms',
    '/api-docs' => 'App\Controllers\StaticPageController@apiDocs',
    
    // Guide routes
    '/guide' => 'App\Controllers\StaticPageController@guide',
    '/guide/section/{section}' => 'App\Controllers\GuideController@section',
    '/guide/tutorial/{topic}' => 'App\Controllers\GuideController@tutorial',
    '/guide/video/{id}' => 'App\Controllers\GuideController@video',
    '/guide/support' => 'App\Controllers\GuideController@support',
    '/guide/videos' => 'App\Controllers\GuideController@videos',
    
    // Reports
    '/reports' => 'App\Controllers\ReportController@index',
    '/reports/export' => 'App\Controllers\ReportController@exportData',
];

// Comprehensive error handling for dependency injection
function createControllerWithDependencies($controller_class) {
    try {
        // Handle different controller types with appropriate dependency injection
        switch ($controller_class) {
            case 'App\Controllers\DashboardController':
                // Try to create with proper dependencies, fallback to default constructor
                try {
                    require_once BASE_PATH . '/app/Repositories/DashboardRepository.php';
                    require_once BASE_PATH . '/app/Services/AuthService.php';
                    $dashboardRepo = new App\Repositories\DashboardRepository();
                    $authService = new App\Services\AuthService();
                    return new App\Controllers\DashboardController($dashboardRepo, $authService);
                } catch (Exception $e) {
                    // Fallback to default constructor
                    return new App\Controllers\DashboardController();
                }
                
            case 'App\Controllers\CertificateController':
                // Try to create with dependencies from container
                try {
                    if (class_exists('App\Services\DependencyContainer')) {
                        $container = DependencyContainer::getInstance();
                        $db = $container->getDatabase();
                        $authService = $container->getAuthService();
                        $verificationService = $container->getCertificateVerificationService();
                        return new App\Controllers\CertificateController($db, $authService, $verificationService);
                    } else {
                        return new App\Controllers\CertificateController();
                    }
                } catch (Exception $e) {
                    return new App\Controllers\CertificateController();
                }
                
            case 'App\Controllers\RegistrarController':
            case 'App\Controllers\AdminPortalController':
            case 'App\Controllers\ApplicationController':
                // Try to create with basic dependencies
                try {
                    return new $controller_class();
                } catch (Exception $e) {
                    error_log("Error creating controller $controller_class: " . $e->getMessage());
                    throw $e;
                }
                
            default:
                // Default controller creation
                return new $controller_class();
        }
    } catch (Exception $e) {
        error_log("Fatal Error creating controller $controller_class: " . $e->getMessage());
        if ($_ENV['APP_DEBUG'] ?? false) {
            throw $e;
        } else {
            http_response_code(500);
            die('An internal server error occurred. Please contact support.');
        }
    }
}

// Handle dynamic routes with parameters
function matchDynamicRoute($path, $routes) {
    foreach ($routes as $route => $handler) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove full match
            return [$handler, $matches];
        }
    }
    return null;
}

// Check if route exists (exact match first)
if (isset($routes[$path])) {
    $handler = $routes[$path];
    $params = [];
} else {
    // Try dynamic route matching
    $match = matchDynamicRoute($path, $routes);
    if ($match) {
        [$handler, $params] = $match;
    } else {
        $handler = null;
        $params = [];
    }
}

if ($handler) {
    if (is_string($handler) && strpos($handler, '@') !== false) {
        [$controller_class, $method_name] = explode('@', $handler);
    } else if (is_array($handler)) {
        // Fallback for old array-based routes
        [$controller_name, $method_name] = $handler;
        $controller_class = "App\\Controllers\\{$controller_name}";
    } else {
        http_response_code(500);
        echo "Invalid route handler configuration.";
        exit;
    }
    
    // Load the controller class file if it exists
    $controller_file = BASE_PATH . '/app/Controllers/' . basename($controller_class) . '.php';
    if (file_exists($controller_file)) {
        require_once $controller_file;
    }
    
    if (class_exists($controller_class)) {
        try {
            $controller = createControllerWithDependencies($controller_class);
            
            if (method_exists($controller, $method_name)) {
                try {
                    // Call method with parameters if any
                    if (!empty($params)) {
                        call_user_func_array([$controller, $method_name], $params);
                    } else {
                        $controller->$method_name();
                    }
                } catch (Exception $e) {
                    error_log("Controller method error: " . $e->getMessage());
                    if ($_ENV['APP_DEBUG'] ?? false) {
                        echo "Controller Error: " . $e->getMessage();
                    } else {
                        http_response_code(500);
                        echo "Internal Server Error";
                    }
                }
            } else {
                error_log("Method $method_name not found in $controller_class");
                http_response_code(404);
                echo "Method not found: $method_name";
            }
        } catch (Exception $e) {
            error_log("Controller creation error: " . $e->getMessage());
            if ($_ENV['APP_DEBUG'] ?? false) {
                echo "Controller Creation Error: " . $e->getMessage();
            } else {
                http_response_code(500);
                echo "Internal Server Error";
            }
        }
    } else {
        error_log("Controller class not found: $controller_class");
        http_response_code(404);
        echo "Controller class not found: $controller_class";
    }
} else {
    // 404 Not Found
    http_response_code(404);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Page Not Found</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light'>
        <div class='container mt-5 text-center'>
            <h1 class='display-1 text-muted'>404</h1>
            <h2>Page Not Found</h2>
            <p class='lead'>The page you're looking for doesn't exist.</p>
            <p class='text-muted'>Requested path: " . htmlspecialchars($path) . "</p>
            <a href='/' class='btn btn-primary'>Go Home</a>
        </div>
    </body>
    </html>";
}

// Flush the global buffer
if (ob_get_level() > 0) { ob_end_flush(); }
