<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Verify Birth Certificate') ?> - Digital Birth Certificate System</title>
    <meta name="description" content="Verify the authenticity of birth certificates issued by our digital birth certificate system. Instant verification with QR code scanning or manual entry.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center space-x-3 text-gray-900 hover:text-primary-600 transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-certificate text-white text-sm"></i>
                        </div>
                        <span class="font-semibold text-lg">Birth Certificate System</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-600 hover:text-primary-600 transition-colors flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span class="hidden sm:inline">Home</span>
                    </a>
                    <a href="/login" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="hidden sm:inline">Login</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 rounded-full mb-4">
                <i class="fas fa-search-plus text-primary-600 text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Verify Birth Certificate
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Instantly verify the authenticity of birth certificates using our secure verification system.
                Enter the certificate number or scan the QR code for immediate results.
            </p>
        </div>

        <!-- Verification Methods -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 mb-8 animate-slide-up">
            <div class="p-6">
                <!-- Method Selector -->
                <div class="flex flex-col sm:flex-row bg-gray-100 rounded-xl p-1 mb-6">
                    <button id="manualTab" class="flex-1 flex items-center justify-center space-x-2 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 bg-white text-primary-600 shadow-sm">
                        <i class="fas fa-keyboard"></i>
                        <span>Manual Entry</span>
                    </button>
                    <button id="qrTab" class="flex-1 flex items-center justify-center space-x-2 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:text-primary-600">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Code Scan</span>
                    </button>
                </div>

                <!-- Manual Entry Form -->
                <div id="manualEntry" class="verification-method">
                    <form id="verificationForm" class="space-y-4">
                        <div>
                            <label for="certificate_number" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hashtag mr-2"></i>Certificate Number
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="certificate_number" 
                                    name="certificate_number"
                                    value="<?= htmlspecialchars($_GET['certificate_number'] ?? '') ?>"
                                    placeholder="e.g., BC2024AB12CD34EF"
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg font-mono"
                                    maxlength="14"
                                    required
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-certificate text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Enter the 14-character certificate number (BC followed by 12 alphanumeric characters)
                            </p>
                        </div>

                        <button 
                            type="submit" 
                            id="verifyButton"
                            class="w-full bg-gradient-to-r from-primary-600 to-primary-700 text-white py-4 px-6 rounded-xl hover:from-primary-700 hover:to-primary-800 focus:ring-4 focus:ring-primary-200 transition-all duration-200 flex items-center justify-center space-x-3 text-lg font-semibold"
                        >
                            <i class="fas fa-search"></i>
                            <span>Verify Certificate</span>
                        </button>
                    </form>
                </div>

                <!-- QR Code Scanner -->
                <div id="qrScanner" class="verification-method hidden">
                    <div class="text-center space-y-4">
                        <div id="qr-reader" class="mx-auto max-w-md border-2 border-dashed border-gray-300 rounded-xl overflow-hidden"></div>
                        
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-camera mr-2"></i>
                                Position the QR code within the camera view
                            </p>
                            <button 
                                id="toggleCamera" 
                                class="inline-flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <i class="fas fa-camera"></i>
                                <span>Start Camera</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 text-center animate-pulse-slow">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 rounded-full mb-4">
                    <i class="fas fa-spinner fa-spin text-primary-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Verifying Certificate...</h3>
                <p class="text-gray-600">Please wait while we validate the certificate authenticity.</p>
            </div>
        </div>

        <!-- Error Display -->
        <?php if (isset($error)): ?>
        <div class="bg-danger-50 border-l-4 border-danger-500 rounded-xl p-6 mb-8 animate-slide-up">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-danger-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-danger-800 mb-1">Verification Error</h3>
                    <p class="text-danger-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certificate Result -->
        <?php if (isset($certificate)): ?>
            <?php if ($certificate['is_valid']): ?>
                <!-- Valid Certificate -->
                <div class="bg-white rounded-2xl shadow-xl border border-success-200 mb-8 animate-slide-up overflow-hidden">
                    <!-- Success Header -->
                    <div class="bg-gradient-to-r from-success-500 to-success-600 px-6 py-8 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-4">
                            <i class="fas fa-check-circle text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Certificate Verified ✓</h2>
                        <p class="text-success-100">This is a valid birth certificate issued by our system</p>
                    </div>

                    <!-- Certificate Details -->
                    <div class="p-6">
                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Child Information -->
                            <div class="space-y-6">
                                <div class="border-b border-gray-200 pb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center space-x-2 mb-4">
                                        <i class="fas fa-baby text-primary-600"></i>
                                        <span>Child Information</span>
                                    </h3>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($certificate['child_name']) ?></p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                                        <p class="text-lg text-gray-900 flex items-center space-x-2">
                                            <i class="fas fa-calendar text-primary-600"></i>
                                            <span><?= date('F j, Y', strtotime($certificate['date_of_birth'])) ?></span>
                                        </p>
                                    </div>

                                    <?php if (!empty($certificate['gender'])): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Gender</label>
                                        <p class="text-lg text-gray-900"><?= ucfirst(htmlspecialchars($certificate['gender'])) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($certificate['place_of_birth'])): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Place of Birth</label>
                                        <p class="text-lg text-gray-900 flex items-center space-x-2">
                                            <i class="fas fa-map-marker-alt text-primary-600"></i>
                                            <span><?= htmlspecialchars($certificate['place_of_birth']) ?></span>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Parent & Certificate Information -->
                            <div class="space-y-6">
                                <!-- Parent Information -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center space-x-2 mb-4">
                                        <i class="fas fa-users text-primary-600"></i>
                                        <span>Parent Information</span>
                                    </h3>
                                </div>

                                <div class="space-y-4">
                                    <?php if (!empty($certificate['mother_name'])): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Mother's Name</label>
                                        <p class="text-lg text-gray-900"><?= htmlspecialchars($certificate['mother_name']) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($certificate['father_name'])): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Father's Name</label>
                                        <p class="text-lg text-gray-900"><?= htmlspecialchars($certificate['father_name']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Certificate Information -->
                                <div class="border-t border-gray-200 pt-6">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center space-x-2 mb-4">
                                        <i class="fas fa-certificate text-primary-600"></i>
                                        <span>Certificate Details</span>
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Certificate Number</label>
                                            <p class="text-lg font-mono font-bold text-primary-600 bg-primary-50 px-3 py-2 rounded-lg inline-block">
                                                <?= htmlspecialchars($certificate['certificate_number']) ?>
                                            </p>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Issue Date</label>
                                            <p class="text-lg text-gray-900"><?= date('F j, Y', strtotime($certificate['issued_at'])) ?></p>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Status</label>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-800">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                <?= ucfirst(htmlspecialchars($certificate['status'])) ?>
                                            </span>
                                        </div>

                                        <?php if (isset($certificate['verification_count'])): ?>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Verification Count</label>
                                            <p class="text-lg text-gray-900"><?= number_format($certificate['verification_count']) ?> times</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Timestamp -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="bg-success-50 rounded-xl p-4 flex items-center space-x-3">
                                <i class="fas fa-shield-check text-success-600 text-xl"></i>
                                <div>
                                    <p class="font-medium text-success-800">Verified Successfully</p>
                                    <p class="text-sm text-success-600">
                                        This certificate was verified on <?= date('F j, Y \a\t g:i A') ?> and is currently active in our system.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Invalid Certificate -->
                <div class="bg-white rounded-2xl shadow-xl border border-danger-200 mb-8 animate-slide-up overflow-hidden">
                    <!-- Error Header -->
                    <div class="bg-gradient-to-r from-danger-500 to-danger-600 px-6 py-8 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-4">
                            <i class="fas fa-times-circle text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Certificate Not Found</h2>
                        <p class="text-danger-100">The certificate number provided was not found in our system</p>
                    </div>

                    <!-- Error Details -->
                    <div class="p-6">
                        <div class="text-center space-y-4">
                            <p class="text-lg text-gray-700">
                                Certificate Number: <span class="font-mono font-bold"><?= htmlspecialchars($_GET['certificate_number'] ?? 'N/A') ?></span>
                            </p>
                            
                            <?php if (!empty($certificate['reason'])): ?>
                            <p class="text-gray-600"><?= htmlspecialchars($certificate['reason']) ?></p>
                            <?php endif; ?>

                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-6">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-exclamation-triangle text-amber-600 text-xl mt-1"></i>
                                    <div class="text-left">
                                        <h3 class="font-medium text-amber-800 mb-2">Possible Reasons:</h3>
                                        <ul class="text-sm text-amber-700 space-y-1">
                                            <li>• The certificate number may be incorrect</li>
                                            <li>• The certificate may not have been issued yet</li>
                                            <li>• The certificate may have been revoked or expired</li>
                                            <li>• There may be a typo in the certificate number</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Information Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 mb-8">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
                    <i class="fas fa-info-circle text-primary-600"></i>
                    <span>About Certificate Verification</span>
                </h2>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Security Features -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-4 flex items-center space-x-2">
                            <i class="fas fa-shield-alt text-success-600"></i>
                            <span>Security Features</span>
                        </h3>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check text-success-500 text-sm"></i>
                                <span>Unique 14-character certificate numbers</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check text-success-500 text-sm"></i>
                                <span>Blockchain-backed verification system</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check text-success-500 text-sm"></i>
                                <span>Real-time status checking</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check text-success-500 text-sm"></i>
                                <span>Tamper-proof digital records</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check text-success-500 text-sm"></i>
                                <span>QR code integration for instant verification</span>
                            </li>
                        </ul>
                    </div>

                    <!-- How to Use -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-4 flex items-center space-x-2">
                            <i class="fas fa-question-circle text-primary-600"></i>
                            <span>How to Verify</span>
                        </h3>
                        <ol class="space-y-2 text-gray-600">
                            <li class="flex items-start space-x-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs font-medium mt-0.5">1</span>
                                <span>Enter the 14-character certificate number or scan QR code</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs font-medium mt-0.5">2</span>
                                <span>Click the verify button to check authenticity</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs font-medium mt-0.5">3</span>
                                <span>View detailed verification results instantly</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs font-medium mt-0.5">4</span>
                                <span>Save or print the verification report if needed</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-2xl border border-primary-200 p-6 text-center">
            <h3 class="font-semibold text-gray-900 mb-2 flex items-center justify-center space-x-2">
                <i class="fas fa-headset text-primary-600"></i>
                <span>Need Help?</span>
            </h3>
            <p class="text-gray-600 mb-4">
                If you're having trouble verifying a certificate or need assistance with our verification system,
                our support team is here to help.
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <a href="mailto:support@birthcertificate.gov" class="inline-flex items-center space-x-2 text-primary-600 hover:text-primary-700 font-medium">
                    <i class="fas fa-envelope"></i>
                    <span>support@birthcertificate.gov</span>
                </a>
                <span class="hidden sm:inline text-gray-400">•</span>
                <a href="tel:+1234567890" class="inline-flex items-center space-x-2 text-primary-600 hover:text-primary-700 font-medium">
                    <i class="fas fa-phone"></i>
                    <span>+1 (234) 567-8900</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-certificate text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-semibold">Digital Birth Certificate System</span>
                </div>
                <p class="text-gray-400 mb-4">Secure • Reliable • Instant Verification</p>
                <p class="text-sm text-gray-500">
                    © <?= date('Y') ?> Digital Birth Certificate System. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Certificate number formatting and validation
        const certificateInput = document.getElementById('certificate_number');
        const verificationForm = document.getElementById('verificationForm');
        const verifyButton = document.getElementById('verifyButton');
        const loadingState = document.getElementById('loadingState');

        // Format certificate number input
        certificateInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 14) {
                value = value.substring(0, 14);
            }
            e.target.value = value;
            
            // Real-time validation
            const isValid = /^BC[A-Z0-9]{12}$/.test(value);
            if (value.length === 14) {
                if (isValid) {
                    e.target.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
                    e.target.classList.add('border-green-300', 'focus:ring-green-500', 'focus:border-green-500');
                } else {
                    e.target.classList.remove('border-green-300', 'focus:ring-green-500', 'focus:border-green-500');
                    e.target.classList.add('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
                }
            } else {
                e.target.classList.remove('border-green-300', 'focus:ring-green-500', 'focus:border-green-500', 'border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
            }
        });

        // Form submission with loading state
        verificationForm.addEventListener('submit', function(e) {
            const certificateNumber = certificateInput.value.trim();
            
            if (!certificateNumber) {
                e.preventDefault();
                showNotification('Please enter a certificate number', 'error');
                return;
            }

            if (!/^BC[A-Z0-9]{12}$/.test(certificateNumber)) {
                e.preventDefault();
                showNotification('Invalid certificate number format. Please enter BC followed by 12 alphanumeric characters.', 'error');
                return;
            }

            // Show loading state
            showLoading(true);
        });

        // Tab switching functionality
        const manualTab = document.getElementById('manualTab');
        const qrTab = document.getElementById('qrTab');
        const manualEntry = document.getElementById('manualEntry');
        const qrScanner = document.getElementById('qrScanner');

        manualTab.addEventListener('click', () => switchTab('manual'));
        qrTab.addEventListener('click', () => switchTab('qr'));

        function switchTab(tab) {
            if (tab === 'manual') {
                manualTab.classList.add('bg-white', 'text-primary-600', 'shadow-sm');
                manualTab.classList.remove('text-gray-600');
                qrTab.classList.remove('bg-white', 'text-primary-600', 'shadow-sm');
                qrTab.classList.add('text-gray-600');
                
                manualEntry.classList.remove('hidden');
                qrScanner.classList.add('hidden');
                
                stopQRScanner();
            } else {
                qrTab.classList.add('bg-white', 'text-primary-600', 'shadow-sm');
                qrTab.classList.remove('text-gray-600');
                manualTab.classList.remove('bg-white', 'text-primary-600', 'shadow-sm');
                manualTab.classList.add('text-gray-600');
                
                qrScanner.classList.remove('hidden');
                manualEntry.classList.add('hidden');
            }
        }

        // QR Code Scanner functionality
        let html5QrCode = null;
        let cameraStarted = false;

        const toggleCameraButton = document.getElementById('toggleCamera');
        toggleCameraButton.addEventListener('click', toggleQRScanner);

        function toggleQRScanner() {
            if (!cameraStarted) {
                startQRScanner();
            } else {
                stopQRScanner();
            }
        }

        function startQRScanner() {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    try {
                        const qrData = JSON.parse(decodedText);
                        if (qrData.certificate_number) {
                            certificateInput.value = qrData.certificate_number;
                            switchTab('manual');
                            showNotification('QR code scanned successfully! Verifying certificate...', 'success');
                            setTimeout(() => {
                                verificationForm.submit();
                            }, 1000);
                        } else {
                            showNotification('Invalid QR code format', 'error');
                        }
                    } catch (e) {
                        // Try to extract certificate number from plain text
                        const certMatch = decodedText.match(/BC[A-Z0-9]{12}/);
                        if (certMatch) {
                            certificateInput.value = certMatch[0];
                            switchTab('manual');
                            showNotification('Certificate number extracted from QR code!', 'success');
                        } else {
                            showNotification('Could not extract certificate number from QR code', 'error');
                        }
                    }
                },
                (errorMessage) => {
                    // Handle scan errors silently
                }
            ).then(() => {
                cameraStarted = true;
                toggleCameraButton.innerHTML = '<i class="fas fa-stop"></i><span>Stop Camera</span>';
                toggleCameraButton.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                toggleCameraButton.classList.add('bg-red-100', 'text-red-700', 'hover:bg-red-200');
            }).catch((err) => {
                console.error('Error starting QR scanner:', err);
                showNotification('Could not access camera. Please check permissions.', 'error');
            });
        }

        function stopQRScanner() {
            if (html5QrCode && cameraStarted) {
                html5QrCode.stop().then(() => {
                    cameraStarted = false;
                    toggleCameraButton.innerHTML = '<i class="fas fa-camera"></i><span>Start Camera</span>';
                    toggleCameraButton.classList.remove('bg-red-100', 'text-red-700', 'hover:bg-red-200');
                    toggleCameraButton.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                }).catch((err) => {
                    console.error('Error stopping QR scanner:', err);
                });
            }
        }

        // Utility functions
        function showLoading(show) {
            if (show) {
                verifyButton.disabled = true;
                verifyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Verifying...</span>';
                if (loadingState) {
                    loadingState.classList.remove('hidden');
                }
            } else {
                verifyButton.disabled = false;
                verifyButton.innerHTML = '<i class="fas fa-search"></i><span>Verify Certificate</span>';
                if (loadingState) {
                    loadingState.classList.add('hidden');
                }
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const bgColor = type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-blue-500';
            
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        // Auto-focus on certificate input
        if (certificateInput && !certificateInput.value) {
            certificateInput.focus();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopQRScanner();
        });
    </script>
</body>
</html>