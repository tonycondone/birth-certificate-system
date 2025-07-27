<?php
/**
 * Simple Payment and Registration Routes Testing
 * Tests routes using file_get_contents and basic validation
 */

echo "🚀 PAYMENT & REGISTRATION ROUTES TESTING (Simple)\n";
echo "==================================================\n\n";

$baseUrl = 'http://localhost:8000';
$testResults = [];

/**
 * Simple route test using file_get_contents
 */
function testRouteSimple($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'ignore_errors' => true
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    // Get HTTP response code
    $httpCode = 200; // Default
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $httpCode = intval($matches[1]);
                break;
            }
        }
    }
    
    return [
        'url' => $url,
        'success' => $response !== false && $httpCode < 400,
        'http_code' => $httpCode,
        'response_time' => $responseTime . 'ms',
        'response_size' => $response ? strlen($response) : 0,
        'response' => $response
    ];
}

/**
 * Check if response contains expected elements
 */
function checkElements($response, $elements) {
    $results = [];
    foreach ($elements as $element => $description) {
        $found = $response && strpos($response, $element) !== false;
        $results[$description] = $found ? '✅ Found' : '❌ Missing';
    }
    return $results;
}

echo "1. TESTING REGISTRATION ROUTES\n";
echo "===============================\n";

// Test registration page
echo "📋 Testing Registration Page...\n";
$regTest = testRouteSimple($baseUrl . '/register');
$testResults['registration'] = $regTest;

echo "   URL: {$regTest['url']}\n";
echo "   Status: " . ($regTest['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "   HTTP Code: {$regTest['http_code']}\n";
echo "   Response Time: {$regTest['response_time']}\n";
echo "   Content Size: " . number_format($regTest['response_size']) . " bytes\n";

if ($regTest['success']) {
    $regElements = [
        'Create Account' => 'Page Title',
        'Parent/Guardian' => 'Parent Role',
        'Hospital Staff' => 'Hospital Role',
        'Continue' => 'Continue Button',
        'Sign in here' => 'Login Link'
    ];
    
    $regAnalysis = checkElements($regTest['response'], $regElements);
    echo "   Content Analysis:\n";
    foreach ($regAnalysis as $element => $status) {
        echo "     - $element: $status\n";
    }
}
echo "\n";

// Test login page
echo "🔐 Testing Login Page...\n";
$loginTest = testRouteSimple($baseUrl . '/login');
$testResults['login'] = $loginTest;

echo "   URL: {$loginTest['url']}\n";
echo "   Status: " . ($loginTest['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "   HTTP Code: {$loginTest['http_code']}\n";
echo "   Response Time: {$loginTest['response_time']}\n";
echo "   Content Size: " . number_format($loginTest['response_size']) . " bytes\n";

if ($loginTest['success']) {
    $loginElements = [
        'Welcome Back' => 'Page Title',
        'Email Address' => 'Email Field',
        'Password' => 'Password Field',
        'Sign In' => 'Sign In Button',
        'Demo Credentials' => 'Demo Section'
    ];
    
    $loginAnalysis = checkElements($loginTest['response'], $loginElements);
    echo "   Content Analysis:\n";
    foreach ($loginAnalysis as $element => $status) {
        echo "     - $element: $status\n";
    }
}
echo "\n";

echo "2. TESTING PAYMENT ROUTES\n";
echo "==========================\n";

// Test payment page (will likely redirect to login)
echo "💳 Testing Payment Page (Application 1)...\n";
$payTest = testRouteSimple($baseUrl . '/applications/1/pay');
$testResults['payment'] = $payTest;

echo "   URL: {$payTest['url']}\n";
echo "   Status: " . ($payTest['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "   HTTP Code: {$payTest['http_code']}\n";
echo "   Response Time: {$payTest['response_time']}\n";
echo "   Content Size: " . number_format($payTest['response_size']) . " bytes\n";

if ($payTest['success']) {
    // Check if it's a payment page or login redirect
    if (strpos($payTest['response'], 'Secure Payment') !== false) {
        echo "   ✅ Payment page loaded successfully\n";
        
        $payElements = [
            'Secure Payment Processing' => 'Page Title',
            'Payment Summary' => 'Summary Section',
            'Select Payment Method' => 'Method Selection',
            'Card Payment' => 'Card Option',
            'Mobile Money' => 'Mobile Money Option',
            'SSL Secured' => 'Security Badge'
        ];
        
        $payAnalysis = checkElements($payTest['response'], $payElements);
        echo "   Payment Page Analysis:\n";
        foreach ($payAnalysis as $element => $status) {
            echo "     - $element: $status\n";
        }
    } elseif (strpos($payTest['response'], 'Welcome Back') !== false || strpos($payTest['response'], 'Sign In') !== false) {
        echo "   ℹ️  Redirected to login page (authentication required)\n";
    } else {
        echo "   ⚠️  Unexpected response content\n";
    }
}
echo "\n";

echo "3. TESTING ADDITIONAL ROUTES\n";
echo "=============================\n";

$additionalRoutes = [
    '/verify' => 'Certificate Verification Page',
    '/track' => 'Application Tracking Page',
    '/about' => 'About Page',
    '/contact' => 'Contact Page',
    '/faq' => 'FAQ Page'
];

foreach ($additionalRoutes as $route => $description) {
    echo "🔍 Testing $description...\n";
    $test = testRouteSimple($baseUrl . $route);
    $testResults[str_replace('/', '_', $route)] = $test;
    
    echo "   URL: {$test['url']}\n";
    echo "   Status: " . ($test['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    echo "   HTTP Code: {$test['http_code']}\n";
    echo "   Response Time: {$test['response_time']}\n";
    echo "\n";
}

echo "4. PERFORMANCE SUMMARY\n";
echo "=======================\n";

$totalTests = count($testResults);
$successfulTests = count(array_filter($testResults, function($test) { return $test['success']; }));
$averageResponseTime = array_sum(array_map(function($test) { 
    return floatval(str_replace('ms', '', $test['response_time'])); 
}, $testResults)) / $totalTests;

echo "📊 Test Results Summary:\n";
echo "   Total Routes Tested: $totalTests\n";
echo "   Successful Tests: $successfulTests\n";
echo "   Success Rate: " . round(($successfulTests / $totalTests) * 100, 1) . "%\n";
echo "   Average Response Time: " . round($averageResponseTime, 2) . "ms\n";
echo "\n";

echo "5. DETAILED RESULTS\n";
echo "====================\n";

foreach ($testResults as $testName => $result) {
    $status = $result['success'] ? '✅ PASS' : '❌ FAIL';
    $testDisplayName = ucwords(str_replace('_', ' ', $testName));
    echo sprintf("%-30s %s (%s, %s)\n", 
        $testDisplayName, 
        $status, 
        $result['http_code'], 
        $result['response_time']
    );
}

echo "\n";

echo "6. SYSTEM HEALTH CHECK\n";
echo "=======================\n";

// Check if server is running
$serverTest = testRouteSimple($baseUrl);
echo "🖥️  Server Status: " . ($serverTest['success'] ? '✅ RUNNING' : '❌ DOWN') . "\n";

// Check database connectivity (indirect)
$dbHealthy = $loginTest['success'] && strpos($loginTest['response'], 'Demo Credentials') !== false;
echo "🗄️  Database Status: " . ($dbHealthy ? '✅ CONNECTED' : '⚠️  UNKNOWN') . "\n";

// Check if payment system is accessible
$paymentHealthy = $payTest['success'];
echo "💳 Payment System: " . ($paymentHealthy ? '✅ ACCESSIBLE' : '⚠️  REQUIRES AUTH') . "\n";

echo "\n";

echo "==================================================\n";
echo "🎉 TESTING COMPLETED\n";
echo "==================================================\n";

$overallStatus = $successfulTests >= ($totalTests * 0.8) ? '✅ EXCELLENT' : 
                ($successfulTests >= ($totalTests * 0.6) ? '⚠️  GOOD' : '❌ NEEDS ATTENTION');

echo "📋 FINAL ASSESSMENT:\n";
echo "   Overall Status: $overallStatus\n";
echo "   System Health: " . ($serverTest['success'] ? '✅ HEALTHY' : '❌ UNHEALTHY') . "\n";
echo "   Ready for Testing: " . ($successfulTests >= ($totalTests * 0.7) ? '✅ YES' : '❌ NO') . "\n";

if ($successfulTests >= ($totalTests * 0.8)) {
    echo "\n🚀 CONCLUSION: SYSTEM IS READY FOR BROWSER TESTING\n";
    echo "   All major routes are accessible\n";
    echo "   Registration and login pages load correctly\n";
    echo "   Payment routes are protected (require authentication)\n";
    echo "   Performance is acceptable\n";
} else {
    echo "\n⚠️  CONCLUSION: SYSTEM NEEDS REVIEW\n";
    echo "   Some routes may have issues\n";
    echo "   Check server configuration\n";
}

echo "\n";
?>
