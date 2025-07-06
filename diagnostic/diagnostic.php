<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manually include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Services/DependencyContainer.php';

// Check autoloading
echo "Autoloading Check:\n";
try {
    // Use dependency container
    $container = App\Services\DependencyContainer::getInstance();
    
    // Try to get various services
    $db = $container->getDatabase();
    echo "✓ Database connection successful\n";

    $authService = $container->getAuthService();
    echo "✓ AuthService loaded successfully\n";

    $verificationService = $container->getCertificateVerificationService();
    echo "✓ CertificateVerificationService loaded successfully\n";

    $user = new App\Models\User([
        'id' => 1,
        'username' => 'test',
        'email' => 'test@example.com',
        'role' => 'user'
    ]);
    echo "✓ User model loaded successfully\n";
} catch (Exception $e) {
    echo "× Autoloading Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Check environment configuration
echo "\nEnvironment Configuration:\n";
try {
    // Load .env file manually
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envContents = file_get_contents($envFile);
        preg_match_all('/^([^=\n]+)=(.*)$/m', $envContents, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $key = trim($match[1]);
            $value = trim($match[2], '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }

    $requiredEnvVars = [
        'APP_ENV',
        'APP_DEBUG',
        'DB_HOST',
        'DB_NAME',
        'DB_USER'
    ];

    foreach ($requiredEnvVars as $var) {
        $value = $_ENV[$var] ?? getenv($var);
        echo "✓ $var: " . ($value ?: 'NOT SET') . "\n";
    }
} catch (Exception $e) {
    echo "× Environment Configuration Error: " . $e->getMessage() . "\n";
}

// Routing Simulation
echo "\nRouting Simulation:\n";
try {
    $routes = [
        '/' => 'HomeController@index',
        '/login' => 'AuthController@showLogin',
        '/verify' => 'CertificateController@showVerify'
    ];

    foreach ($routes as $path => $handler) {
        list($controllerName, $method) = explode('@', $handler);
        $fullControllerName = "App\\Controllers\\$controllerName";
        
        // Attempt to find and load the controller file
        $controllerPath = __DIR__ . "/app/Controllers/{$controllerName}.php";
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
        }
        
        if (class_exists($fullControllerName)) {
            echo "✓ $path: $controllerName exists\n";
            
            try {
                // Use dependency container to create controller
                $container = App\Services\DependencyContainer::getInstance();
                
                if ($fullControllerName === App\Controllers\CertificateController::class) {
                    $controller = new $fullControllerName(
                        $container->getDatabase(),
                        $container->getAuthService(),
                        $container->getCertificateVerificationService()
                    );
                } else {
                    $controller = new $fullControllerName();
                }
                
                if (method_exists($controller, $method)) {
                    echo "✓ $path: $method method exists\n";
                } else {
                    echo "× $path: $method method NOT found\n";
                }
            } catch (Exception $e) {
                echo "× $path: Failed to instantiate controller: " . $e->getMessage() . "\n";
            }
        } else {
            echo "× $path: $controllerName class NOT found\n";
        }
    }
} catch (Exception $e) {
    echo "× Routing Simulation Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDiagnostic Complete.\n"; 