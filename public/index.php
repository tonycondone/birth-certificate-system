<?php
/**
 * Digital Birth Certificate System
 * Main Entry Point
 */

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__));

// Load environment variables
if (file_exists(__DIR__ . '/../env.example')) {
    $envFile = __DIR__ . '/../env.example';
} elseif (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
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

// Set error reporting
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

// Debug information
if ($_ENV['APP_DEBUG'] ?? false) {
    // echo "<!-- Debug Info: Path=$path, Method=$requestMethod -->\n";
}

// Route definitions
$routes = [
    '/' => 'App\Controllers\HomeController@index',
    '/login' => 'App\Controllers\AuthController@showLogin',
    '/register' => 'App\Controllers\AuthController@showRegister',
    '/auth/logout' => 'App\Controllers\AuthController@logout',
    '/auth/forgot-password' => 'App\Controllers\AuthController@showForgotPassword',
    '/auth/reset-password' => 'App\Controllers\AuthController@showResetPassword',
    '/auth/2fa' => 'App\Controllers\AuthController@showTwoFactorAuth',
    '/auth/verify' => 'App\Controllers\AuthController@verifyEmail',
    '/auth/verify-email' => 'App\Controllers\AuthController@verifyEmail',
    '/dashboard' => 'App\Controllers\DashboardController@index',
    '/dashboard/pending' => 'App\Controllers\DashboardController@pending',
    '/dashboard/approved' => 'App\Controllers\DashboardController@approved',
    '/dashboard/reports' => 'App\Controllers\DashboardController@reports',
    '/dashboard/settings' => 'App\Controllers\DashboardController@settings',
    '/profile' => 'App\Controllers\UserController@profile',
    '/settings' => 'App\Controllers\UserController@settings',
    '/user/delete-account' => 'App\Controllers\UserController@deleteAccount',
    '/certificate/apply' => 'App\Controllers\CertificateController@apply',
    '/certificate/verify' => 'App\Controllers\CertificateController@verifyFromRequest',
    '/certificate/approve' => 'App\Controllers\CertificateController@approveApplication',
    '/certificate/download' => 'App\Controllers\CertificateController@downloadCertificate',
    '/certificates' => 'App\Controllers\CertificateController@listCertificates',
    '/verify' => 'App\Controllers\CertificateController@showVerify',
    '/reports' => 'App\Controllers\ReportController@index',
    '/reports/export' => 'App\Controllers\ReportController@exportData',
    
    // Static pages
    '/about' => 'App\Controllers\StaticPageController@about',
    '/contact' => 'App\Controllers\StaticPageController@contact',
    '/faq' => 'App\Controllers\StaticPageController@faq',
    '/privacy' => 'App\Controllers\StaticPageController@privacy',
    '/terms' => 'App\Controllers\StaticPageController@terms',
    '/api-docs' => 'App\Controllers\StaticPageController@apiDocs',
    
    // User profile and settings
    '/notifications' => 'App\Controllers\NotificationController@index',
    
    // Application routes
    '/applications/new' => 'App\Controllers\ApplicationController@create',
    '/applications' => 'App\Controllers\ApplicationController@index',
    '/applications/{id}' => 'App\Controllers\ApplicationController@show',
    
    // Generic Application Submission
    '/applications/submit' => 'App\Controllers\GenericApplicationController@create',
    '/applications/submit/store' => 'App\Controllers\GenericApplicationController@store',

    // Payment routes
    '/applications/{id}/pay' => 'App\Controllers\PaymentController@pay',
    '/applications/{id}/payment-callback' => 'App\Controllers\PaymentController@callback',

    // Tracking lookup form and handler (Phase 3)
    '/track' => 'App\Controllers\TrackingController@form',
    '/track/search' => 'App\Controllers\TrackingController@search',
    // Actual tracking detail route
    '/track/{tracking_number}' => 'App\Controllers\TrackingController@show',

    // Feedback routes
    '/applications/{id}/feedback' => 'App\Controllers\FeedbackController@create',
    '/applications/feedback/store' => 'App\Controllers\FeedbackController@store',
    
    // Hospital routes
    '/hospital/submissions' => 'App\Controllers\AdminController@hospitalSubmissions',
    '/hospital/dashboard' => 'App\Controllers\AdminController@hospitalDashboard',
    '/hospital/records' => 'App\Controllers\AdminController@hospitalRecords',
    '/hospital/records/new' => 'App\Controllers\AdminController@hospitalRecordCreate',
    '/hospital/records/{id}' => 'App\Controllers\AdminController@hospitalRecordShow',
    '/hospital/records/{id}/edit' => 'App\Controllers\AdminController@hospitalRecordEdit',
    '/hospital/verifications' => 'App\Controllers\AdminController@hospitalVerifications',
    '/hospital/verify/{id}' => 'App\Controllers\AdminController@hospitalVerify',
    '/hospital/settings' => 'App\Controllers\AdminController@hospitalSettings',
    
    // Registrar routes
    '/registrar/applications' => 'App\Controllers\AdminController@registrarApplications',
    '/registrar/dashboard' => 'App\Controllers\AdminController@registrarDashboard',
    '/registrar/pending' => 'App\Controllers\AdminController@registrarPending',
    '/registrar/approved' => 'App\Controllers\AdminController@registrarApproved',
    '/registrar/reports' => 'App\Controllers\AdminController@registrarReports',
    '/registrar/settings' => 'App\Controllers\AdminController@registrarSettings',
    '/registrar/review/{id}' => 'App\Controllers\AdminController@registrarReview',
    
    // Admin routes
    '/admin/dashboard' => 'App\Controllers\AdminController@dashboard',
    '/admin/users' => 'App\Controllers\AdminController@users',
    '/admin/applications' => 'App\Controllers\AdminController@applications',
    '/admin/generic-applications' => 'App\Controllers\AdminController@genericApplications',
    '/admin/certificates' => 'App\Controllers\AdminController@certificates',
    '/admin/reports' => 'App\Controllers\AdminController@reports',
    '/admin/settings' => 'App\Controllers\AdminController@settings',
    
    // Certificate routes
    '/certificates/download/{id}' => 'App\Controllers\CertificateController@download',
    '/certificates/{id}' => 'App\Controllers\CertificateController@show',
    
    // Verification routes
    '/verifications' => 'App\Controllers\CertificateController@verifications',
    '/verifications/history' => 'App\Controllers\CertificateController@verificationHistory',
    
    // Settings routes
    '/dashboard/registrar' => 'App\Controllers\DashboardController@registrar',

    // Certificate Verification Routes
    '/verify/certificate/[*:number]' => 'CertificateController@validateCertificate',
    '/certificates/verify/[i:id]' => 'CertificateController@verifyCertificate',
    '/certificates/reject/[i:id]' => 'CertificateController@rejectCertificate',
];

// Comprehensive error handling for dependency injection
function createCertificateController() {
    try {
        // Get dependencies from the container
        $container = DependencyContainer::getInstance();
        
        // Detailed logging of dependency creation
        try {
            $db = $container->getDatabase();
        } catch (Exception $e) {
            error_log("Database Connection Error in createCertificateController: " . $e->getMessage());
            throw $e;
        }

        try {
            $authService = $container->getAuthService();
        } catch (Exception $e) {
            error_log("Auth Service Creation Error in createCertificateController: " . $e->getMessage());
            throw $e;
        }

        try {
            $verificationService = $container->getCertificateVerificationService();
        } catch (Exception $e) {
            error_log("Certificate Verification Service Creation Error in createCertificateController: " . $e->getMessage());
            throw $e;
        }
        
        // Create and return CertificateController with dependencies
        return new CertificateController($db, $authService, $verificationService);
    } catch (Exception $e) {
        // Log the full error details
        error_log("Fatal Error in createCertificateController: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        
        // Depending on environment, either show a generic error or detailed error
        if ($_ENV['APP_DEBUG'] ?? false) {
            throw $e; // Rethrow for detailed error display
        } else {
            // Show a generic error page
            http_response_code(500);
            die('An internal server error occurred. Please contact support.');
        }
    }
}

// Check if route exists
if (isset($routes[$path])) {
    $handler = $routes[$path];
    
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
    
    if (class_exists($controller_class)) {
        // Special handling for CertificateController
        if ($controller_class === \App\Controllers\CertificateController::class) {
            $controller = createCertificateController();
        } else {
            $controller = new $controller_class();
        }
        
        if (method_exists($controller, $method_name)) {
            try {
                $controller->$method_name();
            } catch (Exception $e) {
                error_log("Controller error: " . $e->getMessage());
                http_response_code(500);
                echo "Internal Server Error";
            }
        } else {
            http_response_code(404);
            echo "Method not found";
        }
    } else {
        http_response_code(404);
        echo "Controller class not found";
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
            <a href='/' class='btn btn-primary'>Go Home</a>
        </div>
    </body>
    </html>";
} 