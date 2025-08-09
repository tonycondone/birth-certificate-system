<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Certificate Verification Dashboard') ?> - Digital Birth Certificate System</title>
    <meta name="description" content="Certificate verification dashboard for registrars and administrators to manage and process birth certificate applications.">
    
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
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <span class="text-sm text-gray-600">
                        Welcome, <span class="font-medium"><?= htmlspecialchars($user->getFirstName()) ?></span>
                    </span>
                    <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-primary-600 text-sm"></i>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Certificate Verification Dashboard
                    </h1>
                    <p class="text-gray-600">
                        Manage and process birth certificate applications for verification and approval.
                    </p>
                </div>
                
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button 
                        id="batchVerifyBtn" 
                        class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2"
                        disabled
                    >
                        <i class="fas fa-check-double"></i>
                        <span>Batch Verify</span>
                    </button>
                    <button 
                        id="refreshBtn" 
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2"
                    >
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-slide-up">
            <!-- Total Attempts -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Attempts</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats->totalAttempts) ?></p>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Success Rate</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats->successRate, 1) ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Unique Certificates -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-certificate text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Unique Certificates</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats->uniqueCertificates) ?></p>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Applications</p>
                        <p class="text-2xl font-bold text-gray-900"><?= count($pendingApplications) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8 animate-slide-up">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center space-x-2">
                        <i class="fas fa-list text-primary-600"></i>
                        <span>Pending Applications</span>
                        <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= count($pendingApplications) ?>
                        </span>
                    </h2>
                    
                    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <input 
                                type="checkbox" 
                                id="selectAll" 
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                            >
                            <label for="selectAll" class="text-sm text-gray-600">Select All</label>
                        </div>
                        
                        <div class="relative">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search applications..." 
                                class="w-64 px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <?php if (!empty($pendingApplications)): ?>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" class="w-4 h-4 text-primary-600 border-gray-300 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Application
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Child Details
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Parent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Submitted
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="applicationsTable">
                        <?php foreach ($pendingApplications as $application): ?>
                        <tr class="hover:bg-gray-50 application-row" data-application-id="<?= htmlspecialchars($application['id']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input 
                                    type="checkbox" 
                                    class="application-checkbox w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                    data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                >
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-alt text-primary-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            #<?= htmlspecialchars($application['id']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($application['hospital_name'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars(trim($application['child_first_name'] . ' ' . ($application['child_middle_name'] ?? '') . ' ' . $application['child_last_name'])) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= date('M j, Y', strtotime($application['date_of_birth'])) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= htmlspecialchars($application['place_of_birth'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($application['parent_first_name'] . ' ' . $application['parent_last_name']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-envelope mr-1"></i>
                                    <?= htmlspecialchars($application['parent_email']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-col">
                                    <span><?= date('M j, Y', strtotime($application['created_at'])) ?></span>
                                    <span class="text-xs text-gray-400"><?= date('g:i A', strtotime($application['created_at'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?= ucfirst(htmlspecialchars($application['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button 
                                        class="view-details-btn text-primary-600 hover:text-primary-900 transition-colors"
                                        data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button 
                                        class="verify-btn text-green-600 hover:text-green-900 transition-colors"
                                        data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        title="Verify & Approve"
                                    >
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button 
                                        class="reject-btn text-red-600 hover:text-red-900 transition-colors"
                                        data-application-id="<?= htmlspecialchars($application['id']) ?>"
                                        title="Reject Application"
                                    >
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Applications</h3>
                    <p class="text-gray-600">All applications have been processed. Great job!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-slide-up">
            <!-- Verification Trends Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                    <i class="fas fa-chart-line text-primary-600"></i>
                    <span>Verification Trends</span>
                </h3>
                <div class="h-64">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                    <i class="fas fa-chart-pie text-primary-600"></i>
                    <span>Status Distribution</span>
                </h3>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals -->
    
    <!-- Application Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Application Details</h3>
                    <button id="closeDetailsModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div id="detailsContent" class="p-6">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div id="verificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Verify Application</h3>
                    <button id="closeVerificationModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="verificationForm">
                    <input type="hidden" id="verificationApplicationId" name="application_id">
                    <div class="mb-6">
                        <label for="verificationNotes" class="block text-sm font-medium text-gray-700 mb-2">
                            Verification Notes (Optional)
                        </label>
                        <textarea 
                            id="verificationNotes" 
                            name="notes" 
                            rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Add any notes about this verification..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            id="cancelVerification" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-check"></i>
                            <span>Verify & Approve</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Reject Application</h3>
                    <button id="closeRejectionModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="rejectionForm">
                    <input type="hidden" id="rejectionApplicationId" name="application_id">
                    <div class="mb-6">
                        <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Rejection *
                        </label>
                        <textarea 
                            id="rejectionReason" 
                            name="reason" 
                            rows="4" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Please provide a detailed reason for rejecting this application..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            id="cancelRejection" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-times"></i>
                            <span>Reject Application</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Global variables
        let selectedApplications = new Set();

        // DOM Elements
        const selectAllCheckbox = document.getElementById('selectAll');
        const applicationCheckboxes = document.querySelectorAll('.application-checkbox');
        const batchVerifyBtn = document.getElementById('batchVerifyBtn');
        const searchInput = document.getElementById('searchInput');
        const refreshBtn = document.getElementById('refreshBtn');

        // Modal elements
        const detailsModal = document.getElementById('detailsModal');
        const verificationModal = document.getElementById('verificationModal');
        const rejectionModal = document.getElementById('rejectionModal');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            initializeCharts();
            updateBatchVerifyButton();
        });

        // Event Listeners
        function initializeEventListeners() {
            // Select all functionality
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                applicationCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const applicationId = checkbox.dataset.applicationId;
                    if (isChecked) {
                        selectedApplications.add(applicationId);
                    } else {
                        selectedApplications.delete(applicationId);
                    }
                });
                updateBatchVerifyButton();
            });

            // Individual checkbox handling
            applicationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const applicationId = this.dataset.applicationId;
                    if (this.checked) {
                        selectedApplications.add(applicationId);
                    } else {
                        selectedApplications.delete(applicationId);
                    }
                    
                    // Update select all checkbox
                    const checkedCount = document.querySelectorAll('.application-checkbox:checked').length;
                    selectAllCheckbox.checked = checkedCount === applicationCheckboxes.length;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < applicationCheckboxes.length;
                    
                    updateBatchVerifyButton();
                });
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.application-row');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Button handlers
            document.querySelectorAll('.view-details-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const applicationId = this.dataset.applicationId;
                    viewApplicationDetails(applicationId);
                });
            });

            document.querySelectorAll('.verify-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const applicationId = this.dataset.applicationId;
                    openVerificationModal(applicationId);
                });
            });

            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const applicationId = this.dataset.applicationId;
                    openRejectionModal(applicationId);
                });
            });

            // Modal close handlers
            document.getElementById('closeDetailsModal').addEventListener('click', () => closeModal(detailsModal));
            document.getElementById('closeVerificationModal').addEventListener('click', () => closeModal(verificationModal));
            document.getElementById('closeRejectionModal').addEventListener('click', () => closeModal(rejectionModal));

            // Form handlers
            document.getElementById('verificationForm').addEventListener('submit', handleVerification);
            document.getElementById('rejectionForm').addEventListener('submit', handleRejection);

            // Cancel button handlers
            document.getElementById('cancelVerification').addEventListener('click', () => closeModal(verificationModal));
            document.getElementById('cancelRejection').addEventListener('click', () => closeModal(rejectionModal));

            // Batch verify handler
            batchVerifyBtn.addEventListener('click', handleBatchVerification);

            // Refresh handler
            refreshBtn.addEventListener('click', () => location.reload());

            // Close modals on outside click
            [detailsModal, verificationModal, rejectionModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal(modal);
                    }
                });
            });
        }

        // Functions
        function updateBatchVerifyButton() {
            const selectedCount = selectedApplications.size;
            batchVerifyBtn.disabled = selectedCount === 0;
            batchVerifyBtn.innerHTML = `
                <i class="fas fa-check-double"></i>
                <span>Batch Verify${selectedCount > 0 ? ` (${selectedCount})` : ''}</span>
            `;
        }

        function openModal(modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function viewApplicationDetails(applicationId) {
            // TODO: Implement application details view
            const content = document.getElementById('detailsContent');
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-primary-600 text-2xl mb-4"></i>
                    <p>Loading application details...</p>
                </div>
            `;
            openModal(detailsModal);
            
            // Simulate loading details
            setTimeout(() => {
                content.innerHTML = `
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Application #${applicationId}</h4>
                            <p class="text-sm text-gray-600">Detailed information would be loaded here from the server.</p>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        function openVerificationModal(applicationId) {
            document.getElementById('verificationApplicationId').value = applicationId;
            document.getElementById('verificationNotes').value = '';
            openModal(verificationModal);
        }

        function openRejectionModal(applicationId) {
            document.getElementById('rejectionApplicationId').value = applicationId;
            document.getElementById('rejectionReason').value = '';
            openModal(rejectionModal);
        }

        async function handleVerification(e) {
            e.preventDefault();
            
            const applicationId = document.getElementById('verificationApplicationId').value;
            const notes = document.getElementById('verificationNotes').value;
            
            try {
                const response = await fetch('/api/certificates/verify-application', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        application_id: parseInt(applicationId),
                        notes: notes,
                        metadata: {
                            source: 'dashboard',
                            timestamp: new Date().toISOString()
                        }
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Application verified successfully!', 'success');
                    closeModal(verificationModal);
                    removeApplicationFromTable(applicationId);
                } else {
                    showNotification(result.error || 'Verification failed', 'error');
                }
            } catch (error) {
                console.error('Verification error:', error);
                showNotification('An error occurred during verification', 'error');
            }
        }

        async function handleRejection(e) {
            e.preventDefault();
            
            const applicationId = document.getElementById('rejectionApplicationId').value;
            const reason = document.getElementById('rejectionReason').value;
            
            try {
                const response = await fetch('/api/certificates/reject-application', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        application_id: parseInt(applicationId),
                        reason: reason,
                        metadata: {
                            source: 'dashboard',
                            timestamp: new Date().toISOString()
                        }
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Application rejected successfully', 'success');
                    closeModal(rejectionModal);
                    removeApplicationFromTable(applicationId);
                } else {
                    showNotification(result.error || 'Rejection failed', 'error');
                }
            } catch (error) {
                console.error('Rejection error:', error);
                showNotification('An error occurred during rejection', 'error');
            }
        }

        async function handleBatchVerification() {
            if (selectedApplications.size === 0) return;

            const applicationIds = Array.from(selectedApplications).map(id => parseInt(id));
            
            try {
                const response = await fetch('/api/certificates/batch-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        application_ids: applicationIds,
                        metadata: {
                            source: 'batch_dashboard',
                            timestamp: new Date().toISOString()
                        }
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(`Successfully processed ${result.processed_count} applications`, 'success');
                    
                    // Remove processed applications from table
                    applicationIds.forEach(id => removeApplicationFromTable(id.toString()));
                    
                    // Clear selection
                    selectedApplications.clear();
                    updateBatchVerifyButton();
                    selectAllCheckbox.checked = false;
                } else {
                    showNotification(result.error || 'Batch verification failed', 'error');
                }
            } catch (error) {
                console.error('Batch verification error:', error);
                showNotification('An error occurred during batch verification', 'error');
            }
        }

        function removeApplicationFromTable(applicationId) {
            const row = document.querySelector(`tr[data-application-id="${applicationId}"]`);
            if (row) {
                row.remove();
                selectedApplications.delete(applicationId);
                updateBatchVerifyButton();
                
                // Update pending count
                const pendingCount = document.querySelectorAll('.application-row').length;
                document.querySelector('.bg-amber-100.text-amber-800').textContent = pendingCount;
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

        function initializeCharts() {
            // Trends Chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Verifications',
                        data: [12, 19, 3, 5, 2, 3, 9],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Verified', 'Pending', 'Rejected'],
                    datasets: [{
                        data: [<?= $stats->successfulAttempts ?>, <?= count($pendingApplications) ?>, <?= $stats->totalAttempts - $stats->successfulAttempts ?>],
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>