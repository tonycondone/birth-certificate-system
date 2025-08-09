<?php
$pageTitle = $pageTitle ?? 'Certificate Details';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .certificate-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .certificate-card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .section-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 3px solid #667eea;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #2d3748;
        }
        .info-row {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #f1f3f4;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #667eea;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #2d3748;
            font-size: 1rem;
            margin-top: 0.25rem;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .action-buttons {
            background: #f8f9fa;
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .certificate-preview {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
        }
        .certificate-number {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        .breadcrumb-custom .breadcrumb-item + .breadcrumb-item::before {
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="certificate-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Certificate Details</h1>
                    <div class="certificate-number">
                        <?= htmlspecialchars($certificate['certificate_number'] ?? 'BC' . date('Y') . '-PENDING') ?>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="text-white-50">
                        <i class="fa fa-certificate fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/certificates">Certificates</a></li>
                <li class="breadcrumb-item active" aria-current="page">Certificate Details</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Certificate Details -->
            <div class="col-lg-8">
                <div class="certificate-card mb-4">
                    <!-- Certificate Status -->
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-info-circle me-2"></i>Certificate Information</span>
                        <?php
                        $statusColors = [
                            'active' => 'success',
                            'revoked' => 'danger',
                            'expired' => 'warning'
                        ];
                        $statusColor = $statusColors[$certificate['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $statusColor ?> status-badge">
                            <?= ucfirst($certificate['status'] ?? 'Unknown') ?>
                        </span>
                    </div>

                    <!-- Child Information -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Child's Full Name</div>
                                <div class="info-value">
                                    <strong><?= htmlspecialchars(trim(($certificate['child_first_name'] ?? '') . ' ' . ($certificate['child_middle_name'] ?? '') . ' ' . ($certificate['child_last_name'] ?? ''))) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value">
                                    <?= !empty($certificate['date_of_birth']) ? date('F j, Y', strtotime($certificate['date_of_birth'])) : 'Not specified' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Time of Birth</div>
                                <div class="info-value">
                                    <?= !empty($certificate['time_of_birth']) ? date('g:i A', strtotime($certificate['time_of_birth'])) : 'Not specified' ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Gender</div>
                                <div class="info-value">
                                    <?= htmlspecialchars(!empty($certificate['gender']) ? ucfirst($certificate['gender']) : 'Not specified') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Place of Birth</div>
                        <div class="info-value">
                            <?= htmlspecialchars($certificate['place_of_birth'] ?? 'Not specified') ?>
                        </div>
                    </div>

                    <?php if (!empty($certificate['weight_at_birth']) || !empty($certificate['length_at_birth'])): ?>
                    <div class="info-row">
                        <div class="row">
                            <?php if (!empty($certificate['weight_at_birth'])): ?>
                            <div class="col-md-6">
                                <div class="info-label">Weight at Birth</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['weight_at_birth'] ?? '0') ?> kg
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($certificate['length_at_birth'])): ?>
                            <div class="col-md-6">
                                <div class="info-label">Length at Birth</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['length_at_birth'] ?? '0') ?> cm
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Parent Information -->
                <div class="certificate-card mb-4">
                    <div class="section-header">
                        <i class="fa fa-users me-2"></i>Parent Information
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Father's Name</div>
                                <div class="info-value">
                                    <?php 
                                    $fatherName = trim(($certificate['father_first_name'] ?? '') . ' ' . ($certificate['father_last_name'] ?? ''));
                                    echo htmlspecialchars(!empty($fatherName) ? $fatherName : 'Not provided');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Father's National ID</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['father_national_id'] ?? 'Not provided') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Mother's Name</div>
                                <div class="info-value">
                                    <?php 
                                    $motherName = trim(($certificate['mother_first_name'] ?? '') . ' ' . ($certificate['mother_last_name'] ?? ''));
                                    echo htmlspecialchars(!empty($motherName) ? $motherName : 'Not provided');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Mother's National ID</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['mother_national_id'] ?? 'Not provided') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hospital Information -->
                <?php if (!empty($certificate['hospital_name'])): ?>
                <div class="certificate-card mb-4">
                    <div class="section-header">
                        <i class="fa fa-hospital-o me-2"></i>Birth Institution Information
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Hospital/Institution</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['hospital_name']) ?>
                                </div>
                            </div>
                            <?php if (!empty($certificate['attending_physician'])): ?>
                            <div class="col-md-6">
                                <div class="info-label">Attending Physician</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($certificate['attending_physician']) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($certificate['physician_license'])): ?>
                    <div class="info-row">
                        <div class="info-label">Physician License</div>
                        <div class="info-value">
                            <?= htmlspecialchars($certificate['physician_license']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="certificate-card mb-4">
                    <div class="section-header">
                        <i class="fa fa-cogs me-2"></i>Actions
                    </div>
                    <div class="action-buttons">
                        <div class="d-grid gap-2">
                            <a href="/certificates/<?= $certificate['id'] ?>/download" class="btn btn-gradient">
                                <i class="fa fa-download me-2"></i>Download Certificate
                            </a>
                            
                            <?php if (in_array($_SESSION['role'], ['admin', 'registrar'])): ?>
                            <a href="/certificates/<?= $certificate['id'] ?>/email" class="btn btn-outline-primary">
                                <i class="fa fa-envelope me-2"></i>Email Certificate
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($certificate['certificate_number'])): ?>
                            <a href="/verify/<?= htmlspecialchars($certificate['certificate_number']) ?>" class="btn btn-outline-success" target="_blank">
                                <i class="fa fa-check-circle me-2"></i>Verify Certificate
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fa fa-exclamation-triangle me-2"></i>Certificate Not Issued
                            </button>
                            <?php endif; ?>
                            
                            <a href="/certificates" class="btn btn-outline-secondary">
                                <i class="fa fa-list me-2"></i>Back to Certificates
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Certificate Metadata -->
                <div class="certificate-card mb-4">
                    <div class="section-header">
                        <i class="fa fa-info me-2"></i>Certificate Metadata
                    </div>

                    <div class="info-row">
                        <div class="info-label">Certificate Number</div>
                        <div class="info-value">
                            <code><?= htmlspecialchars($certificate['certificate_number'] ?? 'BC' . date('Y') . '-PENDING') ?></code>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Application Number</div>
                        <div class="info-value">
                            <code><?= htmlspecialchars($certificate['application_number'] ?? 'N/A') ?></code>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Issue Date</div>
                        <div class="info-value">
                            <?= !empty($certificate['issued_at']) ? date('F j, Y g:i A', strtotime($certificate['issued_at'])) : 'Not available' ?>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Applicant Email</div>
                        <div class="info-value">
                            <?= htmlspecialchars($certificate['applicant_email'] ?? 'Not available') ?>
                        </div>
                    </div>

                    <?php if (!empty($certificate['digital_signature'])): ?>
                    <div class="info-row">
                        <div class="info-label">Digital Signature</div>
                        <div class="info-value">
                            <span class="badge bg-success">
                                <i class="fa fa-shield me-1"></i>Verified
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Certificate Preview -->
                <div class="certificate-card">
                    <div class="section-header">
                        <i class="fa fa-eye me-2"></i>Certificate Preview
                    </div>
                    <div class="certificate-preview">
                        <i class="fa fa-file-text fa-4x text-muted mb-3"></i>
                        <h6 class="text-muted">Birth Certificate</h6>
                        <p class="small text-muted mb-3">
                            <?= htmlspecialchars(trim(($certificate['child_first_name'] ?? '') . ' ' . ($certificate['child_last_name'] ?? ''))) ?>
                        </p>
                        <a href="/certificates/<?= $certificate['id'] ?>/download" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-download me-1"></i>View Full Certificate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fa fa-check-circle me-2"></i>
                    <strong class="me-auto">Success</strong>
                </div>
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="fa fa-exclamation-circle me-2"></i>
                    <strong class="me-auto">Error</strong>
                </div>
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toasts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 5000);
            });
        });
    </script>
</body>
</html> 