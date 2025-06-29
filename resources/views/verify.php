<?php
$pageTitle = 'Verify Birth Certificate';
require_once __DIR__ . '/layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary mb-3">
                    <i class="fas fa-certificate me-3"></i>
                    Birth Certificate Verification
                </h1>
            <p class="lead text-muted">
                    Verify the authenticity of birth certificates issued by our secure system.
                    Enter the certificate number or scan the QR code for instant verification.
            </p>
    </div>

            <div class="row">
                <div class="col-lg-6">
            <!-- Manual Verification -->
                    <div class="card shadow-lg border-0 mb-4">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-keyboard me-2"></i>
                                Manual Verification
                            </h5>
                        </div>
                        <div class="card-body p-4">
                    <form id="verifyForm" action="/verify" method="GET" class="needs-validation" novalidate>
                        <div class="mb-4">
                                    <label for="certificateNumber" class="form-label fw-bold">
                                        <i class="fas fa-hashtag me-1"></i>Certificate Number
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control" 
                                       id="certificateNumber" name="certificate_number"
                                               placeholder="Enter 12-character code" required
                                               pattern="[A-Z0-9]{12}"
                                               maxlength="12"
                                               style="text-transform: uppercase;">
                                        <button class="btn btn-primary" type="submit" id="verifyBtn">
                                            <i class="fas fa-search me-2"></i>
                                            <span id="verifyBtnText">Verify</span>
                                            <span id="verifyBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                </button>
                            </div>
                            <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter the 12-character alphanumeric certificate number found on the document
                            </div>
                            <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Please enter a valid 12-character certificate number
                                    </div>
                                </div>
                            </form>

                            <!-- Quick Examples -->
                            <div class="alert alert-info border-0">
                                <h6 class="alert-heading">
                                    <i class="fas fa-lightbulb me-2"></i>Example Format
                                </h6>
                                <p class="mb-2">Certificate numbers follow this pattern:</p>
                                <code class="bg-white px-2 py-1 rounded">BC2024000001</code>
                                <hr class="my-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Verification typically takes 2-3 seconds
                                </small>
                            </div>
                </div>
            </div>

            <!-- QR Code Scanner -->
                    <div class="card shadow-lg border-0 mb-4">
                        <div class="card-header bg-success text-white text-center py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-qrcode me-2"></i>
                                QR Code Scanner
                            </h5>
                        </div>
                        <div class="card-body p-4 text-center">
                    <div id="qrReader" class="mb-3" style="display: none;">
                                <div class="position-relative">
                                    <video id="qrVideo" class="w-100 rounded border"></video>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="qr-scanner-overlay"></div>
                                    </div>
                                </div>
                    </div>
                            <button id="startScanner" class="btn btn-success btn-lg">
                                <i class="fas fa-qrcode me-2"></i>
                                <span id="scannerBtnText">Start Scanner</span>
                    </button>
                            <p class="text-muted mt-3 mb-0">
                                <i class="fas fa-mobile-alt me-1"></i>
                                Point your camera at the QR code on the certificate
                            </p>
                        </div>
                </div>
            </div>

                <div class="col-lg-6">
            <!-- Verification Result -->
            <?php if (isset($certificate)): ?>
                        <div class="card shadow-lg border-0">
                            <div class="card-header text-white text-center py-3 <?php echo $certificate['is_valid'] ? 'bg-success' : 'bg-danger'; ?>">
                                <h5 class="mb-0">
                            <?php if ($certificate['is_valid']): ?>
                                        <i class="fas fa-check-circle me-2"></i>Certificate Verified
                            <?php else: ?>
                                        <i class="fas fa-times-circle me-2"></i>Verification Failed
                            <?php endif; ?>
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($certificate['is_valid']): ?>
                                    <!-- Valid Certificate Details -->
                                    <div class="text-center mb-4">
                                        <div class="verification-badge mb-3">
                                            <i class="fas fa-shield-check fa-4x text-success"></i>
                                        </div>
                                        <h4 class="text-success fw-bold">Valid Certificate</h4>
                                        <p class="text-muted">This certificate has been verified and is authentic</p>
                        </div>

                                    <div class="certificate-details">
                                        <h5 class="mb-4">
                                            <i class="fas fa-file-alt me-2"></i>Certificate Details
                                        </h5>
                                        
                            <div class="row g-3">
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Certificate Number</label>
                                                <p class="fw-bold text-primary"><?php echo htmlspecialchars($certificate['number']); ?></p>
                                </div>
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Issue Date</label>
                                                <p class="fw-bold"><?php echo date('M d, Y', strtotime($certificate['issue_date'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Child's Name</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['child_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Date of Birth</label>
                                                <p class="fw-bold"><?php echo date('M d, Y', strtotime($certificate['date_of_birth'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Place of Birth</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['place_of_birth']); ?></p>
                                </div>
                                <div class="col-md-6">
                                                <label class="form-label text-muted small">Gender</label>
                                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['gender']); ?></p>
                                            </div>
                                            <?php if (isset($certificate['father_name'])): ?>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small">Father's Name</label>
                                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['father_name']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($certificate['mother_name'])): ?>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small">Mother's Name</label>
                                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['mother_name']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small">Registrar</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['registrar']); ?></p>
                                </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small">Verification Count</label>
                                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['verification_count'] ?? 1); ?> times</p>
                                            </div>
                            </div>

                            <!-- Blockchain Verification -->
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6 class="mb-3">
                                                <i class="fas fa-link me-2"></i>Blockchain Verification
                                            </h6>
                                <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle text-success me-3"></i>
                                    <div>
                                                    <p class="mb-1 fw-bold">Certificate Hash Verified</p>
                                                    <small class="text-muted font-monospace">
                                                        <?php echo htmlspecialchars($certificate['blockchain_hash']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- QR Code for this certificate -->
                                        <div class="mt-4 text-center">
                                            <h6 class="mb-3">
                                                <i class="fas fa-qrcode me-2"></i>Certificate QR Code
                                            </h6>
                                            <div id="certificateQR" class="d-inline-block p-3 bg-white border rounded"></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Invalid Certificate -->
                                    <div class="text-center mb-4">
                                        <div class="verification-badge mb-3">
                                            <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                                        </div>
                                        <h4 class="text-danger fw-bold">Verification Failed</h4>
                                        <p class="text-muted"><?php echo htmlspecialchars($certificate['message']); ?></p>
                                    </div>

                                    <?php if (isset($certificate['status'])): ?>
                                    <div class="alert alert-warning border-0">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>Certificate Status
                                        </h6>
                                        <p class="mb-2">Certificate found but status is: <strong><?php echo htmlspecialchars($certificate['status']); ?></strong></p>
                                        <?php if (isset($certificate['details'])): ?>
                                        <hr class="my-2">
                                        <small class="text-muted">
                                            Created: <?php echo date('M d, Y', strtotime($certificate['details']['created_at'])); ?><br>
                                            Last Updated: <?php echo date('M d, Y', strtotime($certificate['details']['updated_at'])); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="text-center">
                                        <a href="/verify" class="btn btn-primary">
                                            <i class="fas fa-redo me-2"></i>Try Another Certificate
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Message -->
                    <?php if (isset($error)): ?>
                        <div class="card shadow-lg border-0 border-danger">
                            <div class="card-header bg-danger text-white text-center py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Verification Error
                                </h5>
                            </div>
                            <div class="card-body p-4 text-center">
                                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                <h5 class="text-danger"><?php echo htmlspecialchars($error); ?></h5>
                                <a href="/verify" class="btn btn-primary mt-3">
                                    <i class="fas fa-redo me-2"></i>Try Again
                                </a>
                                </div>
                            </div>
                        <?php endif; ?>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-5">
                <div class="col-md-4 text-center mb-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h5>Secure Verification</h5>
                            <p class="text-muted">Advanced encryption and blockchain technology ensure document authenticity</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                            <h5>Instant Results</h5>
                            <p class="text-muted">Get verification results in seconds with our optimized database</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                            <h5>Mobile Friendly</h5>
                            <p class="text-muted">Scan QR codes directly from your mobile device camera</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verifyForm');
    const certificateInput = document.getElementById('certificateNumber');
    const verifyBtn = document.getElementById('verifyBtn');
    const verifyBtnText = document.getElementById('verifyBtnText');
    const verifyBtnSpinner = document.getElementById('verifyBtnSpinner');
    
    // Form validation
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Show loading state
                verifyBtn.disabled = true;
                verifyBtnText.textContent = 'Verifying...';
                verifyBtnSpinner.style.display = 'inline-block';
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-format certificate number
    certificateInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // QR Code Scanner
    const startScanner = document.getElementById('startScanner');
    const qrReader = document.getElementById('qrReader');
    const qrVideo = document.getElementById('qrVideo');
    let scanning = false;

    if (startScanner) {
        startScanner.addEventListener('click', function() {
            if (scanning) {
                stopScanner();
            } else {
                startQRScanner();
            }
        });
    }

    async function startQRScanner() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            qrVideo.srcObject = stream;
            qrReader.style.display = 'block';
            qrVideo.play();
            scanning = true;
            document.getElementById('scannerBtnText').textContent = 'Stop Scanner';
            startScanner.innerHTML = '<i class="fas fa-stop me-2"></i><span id="scannerBtnText">Stop Scanner</span>';
            startScanner.classList.replace('btn-success', 'btn-danger');
            
            // Initialize QR scanner
            const worker = new Worker('/js/qr-scanner-worker.min.js');
            const qrScanner = new QrScanner(qrVideo, result => {
                if (result) {
                    const certificateNumber = result.data;
                    certificateInput.value = certificateNumber;
                    form.submit();
                    stopScanner();
                }
            });
            qrScanner.start();
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Could not access camera. Please ensure camera permissions are granted.');
        }
    }

    function stopScanner() {
        const stream = qrVideo.srcObject;
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            qrVideo.srcObject = null;
        }
        qrReader.style.display = 'none';
        scanning = false;
        startScanner.innerHTML = '<i class="fas fa-qrcode me-2"></i><span id="scannerBtnText">Start Scanner</span>';
        startScanner.classList.replace('btn-danger', 'btn-success');
    }

    // Generate QR code for valid certificate
    <?php if (isset($certificate) && $certificate['is_valid'] && isset($certificate['qr_data'])): ?>
    const qrData = <?php echo $certificate['qr_data']; ?>;
    const qrContainer = document.getElementById('certificateQR');
    
    QRCode.toCanvas(qrContainer, qrData, {
        width: 150,
        height: 150,
        margin: 2,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function (error) {
        if (error) console.error(error);
    });
    <?php endif; ?>

    // Auto-focus certificate input
    certificateInput.focus();
});
</script>

<style>
.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-lg {
    border-radius: 10px;
}

.verification-badge {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background: rgba(25, 135, 84, 0.1);
}

.qr-scanner-overlay {
    width: 200px;
    height: 200px;
    border: 2px solid #fff;
    border-radius: 10px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

.certificate-details .form-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

/* Loading animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.btn:disabled {
    animation: pulse 1.5s infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
    
    .qr-scanner-overlay {
        width: 150px;
        height: 150px;
    }
}
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>