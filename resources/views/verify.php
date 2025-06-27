<?php
$pageTitle = 'Verify Birth Certificate';
require_once __DIR__ . '/layouts/base.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5">
            <h2 class="mb-4">Birth Certificate Verification</h2>
            <p class="lead text-muted">
                Verify the authenticity of birth certificates issued by our system.
                Enter the certificate number or scan the QR code.
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Manual Verification -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Enter Certificate Details</h5>
                    <form id="verifyForm" action="/verify" method="GET" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="certificateNumber" class="form-label">Certificate Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" 
                                       id="certificateNumber" name="certificate_number"
                                       placeholder="Enter certificate number" required
                                       pattern="[A-Z0-9]{12}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-2"></i>Verify
                                </button>
                            </div>
                            <div class="form-text">
                                Enter the 12-character certificate number found on the document
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid certificate number
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- QR Code Scanner -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title mb-4">Scan QR Code</h5>
                    <div id="qrReader" class="mb-3" style="display: none;">
                        <video id="qrVideo" class="w-100"></video>
                    </div>
                    <button id="startScanner" class="btn btn-primary btn-lg">
                        <i class="fas fa-qrcode me-2"></i>Start Scanner
                    </button>
                </div>
            </div>

            <!-- Verification Result -->
            <?php if (isset($certificate)): ?>
                <div class="card shadow-sm border-<?php echo $certificate['is_valid'] ? 'success' : 'danger'; ?>">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <?php if ($certificate['is_valid']): ?>
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4 class="text-success">Certificate Verified</h4>
                                <p class="text-muted">This is a valid birth certificate</p>
                            <?php else: ?>
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h4 class="text-danger">Invalid Certificate</h4>
                                <p class="text-muted">This certificate could not be verified</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($certificate['is_valid']): ?>
                            <h5 class="mb-4">Certificate Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Certificate Number</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['number']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Issue Date</label>
                                    <p class="fw-bold">
                                        <?php echo date('M d, Y', strtotime($certificate['issue_date'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Child's Name</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['child_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Date of Birth</label>
                                    <p class="fw-bold">
                                        <?php echo date('M d, Y', strtotime($certificate['date_of_birth'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Place of Birth</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['place_of_birth']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Registrar</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($certificate['registrar']); ?></p>
                                </div>
                            </div>

                            <!-- Blockchain Verification -->
                            <div class="mt-4">
                                <h5 class="mb-3">Blockchain Verification</h5>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-link fa-2x text-primary me-3"></i>
                                    <div>
                                        <p class="mb-1">Certificate Hash</p>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($certificate['blockchain_hash']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('verifyForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

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
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
            qrVideo.srcObject = stream;
            qrReader.style.display = 'block';
            qrVideo.play();
            scanning = true;
            startScanner.innerHTML = '<i class="fas fa-stop me-2"></i>Stop Scanner';
            startScanner.classList.replace('btn-primary', 'btn-danger');
            
            // Initialize QR scanner
            const worker = new Worker('/js/qr-scanner-worker.min.js');
            const qrScanner = new QrScanner(qrVideo, result => {
                if (result) {
                    const certificateNumber = result.data;
                    document.getElementById('certificateNumber').value = certificateNumber;
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
        startScanner.innerHTML = '<i class="fas fa-qrcode me-2"></i>Start Scanner';
        startScanner.classList.replace('btn-danger', 'btn-primary');
    }
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>