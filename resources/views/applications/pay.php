<?php
$pageTitle = 'Payment Page - Birth Certificate Application';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-credit-card me-2"></i>Payment Page</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
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
                        Please complete the payment to proceed with your birth certificate application.
                    </div>
                    
                    <div class="text-center mb-4">
                        <button type="button" id="paystackPayButton" class="btn btn-success btn-lg">
                            <i class="fas fa-lock me-2"></i>Pay with Paystack
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Payment Methods</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/visa.png" alt="Visa" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/mastercard.png" alt="Mastercard" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/mtn-momo.png" alt="MTN Mobile Money" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/vodafone-cash.png" alt="Vodafone Cash" width="50" height="30">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Secure Payment</h5>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-0">
                                        <i class="fas fa-shield-alt me-2 text-success"></i>
                                        Your payment information is secure. We use industry-standard encryption to protect your data.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="/applications" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Applications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystackPayButton').addEventListener('click', function() {
    // Initialize payment
    fetch('/applications/<?= $application['id'] ?>/initialize-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Open Paystack payment modal
            const paymentData = result.data;
            window.location.href = paymentData.authorization_url;
        } else {
            alert('Payment initialization failed: ' + result.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while initializing payment. Please try again.');
    });
});
</script> 