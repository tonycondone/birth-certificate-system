<?php
$pageTitle = 'Application Tracking - Digital Birth Certificate System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .status-timeline {
            position: relative;
            padding-left: 30px;
        }
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #28a745;
        }
        .timeline-item.pending::before {
            background: #ffc107;
            box-shadow: 0 0 0 2px #ffc107;
        }
        .timeline-item.rejected::before {
            background: #dc3545;
            box-shadow: 0 0 0 2px #dc3545;
        }
        .application-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card application-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fa fa-file-text me-2"></i>Application Tracking
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Application Details</h5>
                                <p><strong>Application Number:</strong> <?= htmlspecialchars($application['application_number'] ?? $application['reference_number'] ?? 'N/A') ?></p>
                                <p><strong>Child Name:</strong> <?= htmlspecialchars(($application['child_first_name'] ?? '') . ' ' . ($application['child_last_name'] ?? '')) ?></p>
                                <p><strong>Date of Birth:</strong> <?= !empty($application['date_of_birth']) ? date('F j, Y', strtotime($application['date_of_birth'])) : 'N/A' ?></p>
                                <p><strong>Place of Birth:</strong> <?= htmlspecialchars($application['place_of_birth'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Applicant Information</h5>
                                <p><strong>Applicant:</strong> <?= htmlspecialchars(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($application['email'] ?? 'N/A') ?></p>
                                <p><strong>Submitted:</strong> <?= !empty($application['created_at']) ? date('F j, Y g:i A', strtotime($application['created_at'])) : 'N/A' ?></p>
                                <p><strong>Status:</strong> 
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'submitted' => 'info',
                                        'under_review' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'certificate_issued' => 'success'
                                    ];
                                    $statusColor = $statusColors[$application['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusColor ?> status-badge">
                                        <?= ucwords(str_replace('_', ' ', $application['status'])) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($application['review_notes'])): ?>
                        <div class="alert alert-info">
                            <h6>Review Notes:</h6>
                            <p class="mb-0"><?= htmlspecialchars($application['review_notes']) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($application['rejection_reason'])): ?>
                        <div class="alert alert-warning">
                            <h6>Rejection Reason:</h6>
                            <p class="mb-0"><?= htmlspecialchars($application['rejection_reason']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="status-timeline">
                            <?php if (!empty($statusHistory)): ?>
                                <?php foreach ($statusHistory as $status): ?>
                                    <div class="timeline-item <?= $status['status'] ?>">
                                        <div class="timeline-content">
                                            <h6 class="mb-1"><?= htmlspecialchars($status['description'] ?? ucfirst($status['status'])) ?></h6>
                                            <small class="text-muted">
                                                <?= !empty($status['created_at']) ? date('M j, Y g:i A', strtotime($status['created_at'])) : 'N/A' ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Application Submitted</h6>
                                        <small class="text-muted">
                                            <?= !empty($application['created_at']) ? date('M j, Y g:i A', strtotime($application['created_at'])) : 'N/A' ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="/track" class="btn btn-primary btn-sm w-100 mb-2">
                            <i class="fa fa-search me-2"></i>Track Another Application
                        </a>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $application['user_id']): ?>
                            <a href="/applications/<?= $application['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                <i class="fa fa-eye me-2"></i>View Full Details
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($application['status'] === 'approved' || $application['status'] === 'certificate_issued'): ?>
                            <a href="/certificates/download/<?= $application['id'] ?>" class="btn btn-success btn-sm w-100 mb-2">
                                <i class="fa fa-download me-2"></i>Download Certificate
                            </a>
                        <?php endif; ?>
                        
                        <a href="/" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fa fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 