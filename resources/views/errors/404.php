<?php require_once __DIR__ . '/../layouts/base.php'; ?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center">
    <div class="max-w-xl w-full px-4">
        <div class="text-center">
            <!-- Error Illustration -->
            <div class="mb-8">
                <svg class="mx-auto h-32 w-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M6 6l36 36m0-36L6 42m18-18h.01M24 24h.01M24 24h.01"/>
                </svg>
            </div>

            <!-- Error Message -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Page Not Found</h1>
            <p class="text-lg text-gray-600 mb-8">
                Sorry, we couldn't find the page you're looking for. It might have been moved, deleted, or never existed.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <div class="flex justify-center space-x-4">
                    <a href="/" class="btn-primary">
                        Return Home
                    </a>
                    <a href="/contact" class="btn-secondary">
                        Contact Support
                    </a>
                </div>

                <!-- Helpful Links -->
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">You might want to check:</h2>
                    <ul class="space-y-2 text-gray-600">
                        <li>
                            <a href="/verify" class="text-blue-600 hover:text-blue-800">
                                → Verify a Certificate
                            </a>
                        </li>
                        <li>
                            <a href="/applications/parent-form" class="text-blue-600 hover:text-blue-800">
                                → Apply for a Birth Certificate
                            </a>
                        </li>
                        <li>
                            <a href="/faq" class="text-blue-600 hover:text-blue-800">
                                → Check our FAQ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Support Information -->
            <div class="mt-8 text-sm text-gray-500">
                <p>Need help? Contact our support team:</p>
                <p class="mt-1">
                    <a href="mailto:support@birthcert.gov" class="text-blue-600 hover:text-blue-800">
                        support@birthcert.gov
                    </a>
                    &nbsp;|&nbsp;
                    <a href="tel:+1234567890" class="text-blue-600 hover:text-blue-800">
                        +1 (234) 567-890
                    </a>
                </p>
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
// Log the 404 error
error_log("404 Error: " . $_SERVER['REQUEST_URI']);
?>