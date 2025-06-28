<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user
$auth = new \App\Auth\Authentication();
$currentUser = $auth->getCurrentUser();

// Get certificate ID from URL
$certificateId = $_GET['id'] ?? null;

if (!$certificateId) {
    header('Location: /404');
    exit;
}

// Check access rights
if (!$currentUser && !isset($_GET['public_token'])) {
    header('Location: /auth/login');
    exit;
}
?>

<?php require_once __DIR__ . '/../layouts/base.php'; ?>

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="toastIcon" class="fas fa-info-circle me-2"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<div class="container py-5">
    <!-- Certificate Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <h1 class="h3 mb-0">Birth Certificate</h1>
        <div class="btn-group">
            <button onclick="downloadCertificate()" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Download
            </button>
            <button onclick="printCertificate()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <button onclick="verifyCertificate()" class="btn btn-outline-primary">
                <i class="fas fa-shield-alt me-2"></i>Verify
            </button>
        </div>
    </div>

    <!-- Certificate Content -->
    <div class="card shadow-sm">
        <!-- Certificate Status Banner -->
        <div class="card-header border-0" id="certificateStatus">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="text-success">This certificate is valid and verified on the blockchain</span>
            </div>
        </div>

        <!-- Certificate Details -->
        <div class="card-body">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-md-6">
                    <!-- Child Information -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3">Child Information</h3>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Full Name</small>
                                <strong id="childName" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Date of Birth</small>
                                <strong id="dateOfBirth" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Place of Birth</small>
                                <strong id="placeOfBirth" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Gender</small>
                                <strong id="gender" class="loading-placeholder">Loading...</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Parents Information -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3">Parents Information</h3>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Father's Name</small>
                                <strong id="fatherName" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Mother's Name</small>
                                <strong id="motherName" class="loading-placeholder">Loading...</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Certificate Information -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3">Certificate Information</h3>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Certificate Number</small>
                                <strong id="certificateNumber" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Date of Issue</small>
                                <strong id="dateOfIssue" class="loading-placeholder">Loading...</strong>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted d-block">Issuing Authority</small>
                                <strong id="issuingAuthority" class="loading-placeholder">Loading...</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Verification -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3">Verification</h3>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">Blockchain Hash</small>
                                <code class="d-block text-break" id="blockchainHash">Loading...</code>
                            </div>
                        </div>
                        <div class="text-center">
                            <div id="qrCode" class="bg-white p-3 d-inline-block rounded shadow-sm">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div class="text-muted small mt-2">Scan to verify certificate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate History -->
            <div class="mt-4 pt-4 border-top">
                <h3 class="h5 mb-3">Certificate History</h3>
                <div id="certificateHistory">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Certificate Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="verification-icon mb-3">
                    <i class="fas fa-shield-check fa-3x text-success"></i>
                </div>
                <h4 class="mb-3">Certificate Verified</h4>
                <p class="text-muted" id="verificationDetails"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>

<script>
let verificationModal;

document.addEventListener('DOMContentLoaded', function() {
    verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
    loadCertificateDetails();
    generateQRCode();
});

function loadCertificateDetails() {
    const certificateId = new URLSearchParams(window.location.search).get('id');
    
    fetch(`/api/certificates/${certificateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const certificate = data.certificate;
                
                // Update child information
                document.getElementById('childName').textContent = certificate.childName;
                document.getElementById('dateOfBirth').textContent = new Date(certificate.dateOfBirth).toLocaleDateString();
                document.getElementById('placeOfBirth').textContent = certificate.placeOfBirth;
                document.getElementById('gender').textContent = certificate.gender;

                // Update parents information
                document.getElementById('fatherName').textContent = certificate.fatherName;
                document.getElementById('motherName').textContent = certificate.motherName;

                // Update certificate information
                document.getElementById('certificateNumber').textContent = certificate.certificateNumber;
                document.getElementById('dateOfIssue').textContent = new Date(certificate.dateOfIssue).toLocaleDateString();
                document.getElementById('issuingAuthority').textContent = certificate.issuingAuthority;
                document.getElementById('blockchainHash').textContent = certificate.blockchainHash;

                // Update certificate history
                renderCertificateHistory(certificate.history);

                // Update verification status
                updateVerificationStatus(certificate.verified);

                // Remove loading placeholders
                document.querySelectorAll('.loading-placeholder').forEach(el => {
                    el.classList.remove('loading-placeholder');
                });
            } else {
                showNotification('error', 'Error', data.message || 'Error loading certificate details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to load certificate details');
        });
}

function renderCertificateHistory(history) {
    const container = document.getElementById('certificateHistory');
    
    if (!history || history.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-history fa-2x mb-2"></i>
                <p>No history available</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="timeline">
            ${history.map((item, index) => `
                <div class="timeline-item">
                    <div class="timeline-icon bg-${getHistoryIconColor(item.action)}">
                        <i class="fas ${getHistoryIcon(item.action)}"></i>
                    </div>
                    <div class="timeline-content">
                        <p class="mb-1">${item.action}</p>
                        <small class="text-muted">
                            ${new Date(item.timestamp).toLocaleString()} by ${item.actor}
                        </small>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function getHistoryIcon(action) {
    const icons = {
        created: 'fa-plus-circle',
        verified: 'fa-check-circle',
        downloaded: 'fa-download',
        updated: 'fa-edit'
    };
    return icons[action] || 'fa-circle';
}

function getHistoryIconColor(action) {
    const colors = {
        created: 'success',
        verified: 'primary',
        downloaded: 'info',
        updated: 'warning'
    };
    return colors[action] || 'secondary';
}

function generateQRCode() {
    const certificateId = new URLSearchParams(window.location.search).get('id');
    const verificationUrl = `${window.location.origin}/verify?id=${certificateId}`;
    
    QRCode.toCanvas(document.getElementById('qrCode'), verificationUrl, {
        width: 160,
        margin: 2,
        color: {
            dark: '#000000',
            light: '#ffffff'
        }
    });
}

function downloadCertificate() {
    const certificateId = new URLSearchParams(window.location.search).get('id');
    window.location.href = `/api/certificates/${certificateId}/download`;
}

function printCertificate() {
    window.print();
}

function verifyCertificate() {
    const certificateId = new URLSearchParams(window.location.search).get('id');
    
    fetch(`/api/certificates/${certificateId}/verify`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('verificationDetails').textContent = data.message;
                verificationModal.show();
                updateVerificationStatus(data.verified);
            } else {
                showNotification('error', 'Error', data.message || 'Error verifying certificate');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Failed to verify certificate');
        });
}

function updateVerificationStatus(verified) {
    const statusDiv = document.getElementById('certificateStatus');
    if (verified) {
        statusDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="text-success">This certificate is valid and verified on the blockchain</span>
            </div>
        `;
    } else {
        statusDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                <span class="text-danger">This certificate could not be verified on the blockchain</span>
            </div>
        `;
    }
}

function showNotification(type, title, message) {
    const toast = document.getElementById('notificationToast');
    const toastInstance = new bootstrap.Toast(toast);
    
    // Set icon and color based on type
    const icon = document.getElementById('toastIcon');
    icon.className = `fas ${type === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger'} me-2`;
    
    // Set title and message
    document.getElementById('toastTitle').textContent = title;
    document.getElementById('toastMessage').textContent = message;
    
    toastInstance.show();
}
</script>

<style>
.loading-placeholder {
    position: relative;
    color: transparent;
    animation: loading 1.5s infinite;
    background: linear-gradient(100deg, #eceff1 30%, #f5f5f5 50%, #eceff1 70%);
    background-size: 400%;
    border-radius: 4px;
}

@keyframes loading {
    0% { background-position: 100% 50%; }
    100% { background-position: 0 50%; }
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.timeline-content {
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.bg-success { background-color: #28a745; }
.bg-primary { background-color: #007bff; }
.bg-info { background-color: #17a2b8; }
.bg-warning { background-color: #ffc107; }
.bg-secondary { background-color: #6c757d; }

.verification-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media print {
    .btn-group, .modal, .toast-container {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }
}

@media (max-width: 768px) {
    .btn-group {
        width: 100%;
        margin-top: 1rem;
    }
    
    .timeline-item {
        padding-left: 40px;
    }
    
    .timeline-icon {
        width: 24px;
        height: 24px;
        font-size: 12px;
    }
}
</style>
</div>
</div>