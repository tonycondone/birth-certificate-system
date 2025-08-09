<?php
$pageTitle = 'Mock Payment - Development Only';
require_once BASE_PATH . '/resources/views/layouts/base.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="alert alert-warning mb-4">
                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Development Mode Only</h4>
                <p>This is a mock payment page for development testing only. In production, you would be redirected to the Paystack payment gateway.</p>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-credit-card me-2"></i>Mock Payment Page</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($application['email']) ?></p>
                            <p class="mb-3"><strong>Application ID:</strong> <?= htmlspecialchars($application['id']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Summary</h5>
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Birth Certificate Fee:</span>
                                    <span>GH₵<?= number_format($amount, 2) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span>GH₵<?= number_format($amount, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is a simulated payment for testing purposes. No actual payment will be processed.
                    </div>

                    <div class="mb-4">
                        <h5 class="mb-3">Payment Details (Mock)</h5>
                        <div class="card">
                            <div class="card-body">
                                <form action="/mock-payment/<?= $applicationId ?>/<?= $reference ?>/process" method="POST">
                                    <div class="mb-3">
                                        <label for="mockCardNumber" class="form-label">Card Number (Mock)</label>
                                        <input type="text" class="form-control" id="mockCardNumber" value="4111 1111 1111 1111" readonly>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="mockExpiry" class="form-label">Expiry Date (Mock)</label>
                                            <input type="text" class="form-control" id="mockExpiry" value="12/25" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mockCvv" class="form-label">CVV (Mock)</label>
                                            <input type="text" class="form-control" id="mockCvv" value="123" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mockName" class="form-label">Cardholder Name (Mock)</label>
                                        <input type="text" class="form-control" id="mockName" value="<?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?>" readonly>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>Complete Mock Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="/applications/<?= $applicationId ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel and Return
                    </a>
                </div>
            </div>
            
            <div class="mt-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Mock Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Reference:</strong> <?= htmlspecialchars($reference) ?></p>
                        <p class="mb-0"><strong>Mode:</strong> <span class="badge bg-warning">Development Testing</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 