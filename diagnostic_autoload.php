<?php
// Diagnostic Autoload Verification Script

// Print PHP version and configuration
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Include Path: " . get_include_path() . "\n";

// Check if Composer's autoloader exists
$autoloaderPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    require_once $autoloaderPath;
    echo "Composer Autoloader: Loaded successfully\n";
} else {
    echo "Composer Autoloader: NOT FOUND at $autoloaderPath\n";
}

// Attempt to load the specific repository class
try {
    $repositoryClass = 'App\Repositories\BirthApplicationRepository';
    if (class_exists($repositoryClass)) {
        echo "Class $repositoryClass: FOUND ✓\n";
        $reflection = new ReflectionClass($repositoryClass);
        echo "Class File Location: " . $reflection->getFileName() . "\n";
    } else {
        echo "Class $repositoryClass: NOT FOUND ✗\n";
    }
} catch (Exception $e) {
    echo "Error checking class: " . $e->getMessage() . "\n";
}

// List all loaded classes in the App namespace
echo "\nLoaded Classes in App namespace:\n";
$loadedClasses = get_declared_classes();
foreach ($loadedClasses as $class) {
    if (strpos($class, 'App\\') === 0) {
        echo "- $class\n";
    }
} 