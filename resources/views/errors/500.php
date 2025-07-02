
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
// Get error details if in development mode
$isDev = $_ENV['APP_ENV'] === 'development';
$errorDetails = $isDev ? error_get_last() : null;

require_once __DIR__ . '/../layouts/base.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center">
    <div class="max-w-xl w-full px-4">
        <div class="text-center">
            <!-- Error Illustration -->
            <div class="mb-8">
                <svg class="mx-auto h-32 w-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M24 8v4m0 4v4m0 4v4m0 4v4M8 24h4m4 0h4m4 0h4m4 0h4"/>
                </svg>
            </div>

            <!-- Error Message -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Server Error</h1>
            <p class="text-lg text-gray-600 mb-8">
                Sorry, something went wrong on our end. Our team has been notified and is working to fix the issue.
            </p>

            <!-- Technical Details (Development Only) -->
            <?php if ($isDev && $errorDetails): ?>
            <div class="mb-8 p-4 bg-gray-100 rounded-lg">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Error Details</h2>
                <div class="text-left text-sm font-mono overflow-x-auto">
                    <p class="text-red-600"><?php echo htmlspecialchars($errorDetails['message']); ?></p>
                    <p class="text-gray-600 mt-2">
                        File: <?php echo htmlspecialchars($errorDetails['file']); ?>
                        Line: <?php echo htmlspecialchars($errorDetails['line']); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <div class="flex justify-center space-x-4">
                    <button onclick="window.location.reload()" class="btn-primary">
                        Try Again
                    </button>
                    <a href="/" class="btn-secondary">
                        Return Home
                    </a>
                </div>

                <!-- Alternative Actions -->
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">In the meantime, you can:</h2>
                    <ul class="space-y-2 text-gray-600">
                        <li>
                            <a href="/contact" class="text-blue-600 hover:text-blue-800">
                                → Contact our support team
                            </a>
                        </li>
                        <li>
                            <a href="/faq" class="text-blue-600 hover:text-blue-800">
                                → Check our FAQ for common issues
                            </a>
                        </li>
                        <li>
                            <a href="/status" class="text-blue-600 hover:text-blue-800">
                                → Check our system status
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Support Information -->
            <div class="mt-8 text-sm text-gray-500">
                <p>If the problem persists, please contact our support team:</p>
                <p class="mt-1">
                    <a href="mailto:support@birthcert.gov" class="text-blue-600 hover:text-blue-800">
                        support@birthcert.gov
                    </a>
                    &nbsp;|&nbsp;
                    <a href="tel:+1234567890" class="text-blue-600 hover:text-blue-800">
                        +1 (234) 567-890
                    </a>
                </p>
                <?php if (!$isDev): ?>
                <p class="mt-4">
                    Error Reference: <?php echo htmlspecialchars(uniqid('ERR-')); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.btn-primary {
    @apply bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-150 inline-flex items-center;
}

.btn-secondary {
    @apply bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition duration-150 inline-flex items-center;
}
</style>

<?php
// Log the error
$errorMessage = $errorDetails ? $errorDetails['message'] : 'Unknown server error';
$errorFile = $errorDetails ? $errorDetails['file'] : 'Unknown file';
$errorLine = $errorDetails ? $errorDetails['line'] : 'Unknown line';

error_log("500 Error: {$errorMessage} in {$errorFile} on line {$errorLine}");
?>