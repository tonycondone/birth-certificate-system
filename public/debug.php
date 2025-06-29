<?php
/**
 * Debug routing logic
 */

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>Routing Debug</h1>";

// Simulate the routing logic
$requestUri = '/register'; // Test with register path
$requestMethod = 'GET';

echo "<h2>Request Info:</h2>";
echo "<p>URI: $requestUri</p>";
echo "<p>Method: $requestMethod</p>";

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);
echo "<p>Path: $path</p>";

// Remove base path if exists
$basePath = dirname($_SERVER['SCRIPT_NAME']);
echo "<p>Base Path: $basePath</p>";

if ($basePath !== '/') {
    $path = substr($path, strlen($basePath));
    echo "<p>Path after base removal: $path</p>";
}

// Route definitions
$routes = [
    'GET' => [
        '/home' => 'App\Controllers\HomeController@index',
        '/login' => 'App\Controllers\AuthController@showLogin',
        '/register' => 'App\Controllers\AuthController@showRegister',
        '/dashboard' => 'App\Controllers\DashboardController@index',
        '/verify' => 'App\Controllers\CertificateController@showVerify',
        '/verify/{id}' => 'App\Controllers\CertificateController@verify',
    ]
];

echo "<h2>Available Routes:</h2>";
foreach ($routes['GET'] as $route => $handler) {
    echo "<p><strong>$route</strong> → $handler</p>";
}

// Route matching
$matchedRoute = null;
$params = [];

echo "<h2>Route Matching:</h2>";
foreach ($routes[$requestMethod] ?? [] as $route => $handler) {
    $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    echo "<p>Testing route: <strong>$route</strong></p>";
    echo "<p>Pattern: <code>$pattern</code></p>";
    echo "<p>Path: <code>$path</code></p>";
    
    if (preg_match($pattern, $path, $matches)) {
        echo "<p style='color: green;'>✅ MATCH FOUND!</p>";
        $matchedRoute = $handler;
        array_shift($matches); // Remove full match
        $params = $matches;
        break;
    } else {
        echo "<p style='color: red;'>❌ No match</p>";
    }
}

echo "<h2>Result:</h2>";
if ($matchedRoute) {
    echo "<p style='color: green;'>Matched Route: $matchedRoute</p>";
    echo "<p>Parameters: " . implode(', ', $params) . "</p>";
    
    // Test if we can instantiate the controller
    list($controller, $method) = explode('@', $matchedRoute);
    echo "<p>Controller: $controller</p>";
    echo "<p>Method: $method</p>";
    
    if (class_exists($controller)) {
        echo "<p style='color: green;'>✅ Controller class exists</p>";
        $controllerInstance = new $controller();
        if (method_exists($controllerInstance, $method)) {
            echo "<p style='color: green;'>✅ Method exists</p>";
            echo "<p>Ready to call: $controller->$method()</p>";
        } else {
            echo "<p style='color: red;'>❌ Method does not exist</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Controller class does not exist</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No route matched</p>";
}
?> 