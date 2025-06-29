<?php
/**
 * Digital Birth Certificate System
 * Main Entry Point
 */

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
require_once __DIR__ . '/../app/Database/Database.php';

// Simple router
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Default route
if ($path === '' || $path === '/') {
    $path = '/home';
}

// Debug information
if ($_ENV['APP_DEBUG'] ?? false) {
    echo "<!-- Debug Info: Path=$path, Method=$requestMethod -->\n";
}

// Route definitions
$routes = [
    'GET' => [
        '/home' => 'App\Controllers\HomeController@index',
        '/login' => 'App\Controllers\AuthController@showLogin',
        '/register' => 'App\Controllers\AuthController@showRegister',
        '/auth/forgot-password' => 'App\Controllers\AuthController@forgotPassword',
        '/dashboard' => 'App\Controllers\DashboardController@index',
        '/verify' => 'App\Controllers\CertificateController@showVerify',
        '/verify/{id}' => 'App\Controllers\CertificateController@verify',
        
        // Static pages
        '/about' => 'App\Controllers\StaticPageController@about',
        '/contact' => 'App\Controllers\StaticPageController@contact',
        '/faq' => 'App\Controllers\StaticPageController@faq',
        '/privacy' => 'App\Controllers\StaticPageController@privacy',
        '/terms' => 'App\Controllers\StaticPageController@terms',
        '/api-docs' => 'App\Controllers\StaticPageController@apiDocs',
        
        // User profile and settings
        '/profile' => 'App\Controllers\UserController@profile',
        '/notifications' => 'App\Controllers\NotificationController@index',
        
        // Application routes
        '/applications/new' => 'App\Controllers\ApplicationController@create',
        '/applications' => 'App\Controllers\ApplicationController@index',
        '/applications/{id}' => 'App\Controllers\ApplicationController@show',
        
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
        '/settings' => 'App\Controllers\UserController@settings',
    ],
    'POST' => [
        '/auth/login' => 'App\Controllers\AuthController@login',
        '/auth/register' => 'App\Controllers\AuthController@register',
        '/auth/logout' => 'App\Controllers\AuthController@logout',
        '/auth/reset-password' => 'App\Controllers\AuthController@resetPassword',
        '/applications/submit' => 'App\Controllers\ApplicationController@submit',
        '/applications/{id}/update' => 'App\Controllers\ApplicationController@update',
        '/applications/{id}/delete' => 'App\Controllers\ApplicationController@delete',
        '/profile/update' => 'App\Controllers\UserController@updateProfile',
        '/settings/update' => 'App\Controllers\UserController@updateSettings',
        '/notifications/mark-read' => 'App\Controllers\NotificationController@markAsRead',
        '/hospital/records/submit' => 'App\Controllers\AdminController@hospitalRecordSubmit',
        '/hospital/records/{id}/update' => 'App\Controllers\AdminController@hospitalRecordUpdate',
        '/hospital/verify/submit' => 'App\Controllers\AdminController@hospitalVerifySubmit',
        '/registrar/review/submit' => 'App\Controllers\AdminController@registrarReviewSubmit',
        '/admin/users/create' => 'App\Controllers\AdminController@createUser',
        '/admin/users/{id}/update' => 'App\Controllers\AdminController@updateUser',
        '/admin/users/{id}/delete' => 'App\Controllers\AdminController@deleteUser',
    ]
];

// Route matching
$matchedRoute = null;
$params = [];

foreach ($routes[$requestMethod] ?? [] as $route => $handler) {
    $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $path, $matches)) {
        $matchedRoute = $handler;
        array_shift($matches); // Remove full match
        $params = $matches;
        break;
    }
}

// Debug information
if ($_ENV['APP_DEBUG'] ?? false) {
    echo "<!-- Debug Info: Matched Route=$matchedRoute -->\n";
}

// Handle route
if ($matchedRoute) {
    list($controller, $method) = explode('@', $matchedRoute);
    
    try {
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                http_response_code(404);
                include __DIR__ . '/../resources/views/errors/404.php';
            }
        } else {
            http_response_code(404);
            include __DIR__ . '/../resources/views/errors/404.php';
        }
    } catch (Exception $e) {
        http_response_code(500);
        include __DIR__ . '/../resources/views/errors/500.php';
    }
} else {
    // Show 404 instead of redirecting to prevent loops
    http_response_code(404);
    include __DIR__ . '/../resources/views/errors/404.php';
} 