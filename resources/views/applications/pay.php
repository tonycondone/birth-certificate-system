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
                        Choose your preferred payment method. You will be redirected to Paystack's secure checkout.
                    </div>

                    <div class="mb-3">
                        <h5 class="mb-2">Select Payment Method</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="w-100">
                                    <input type="radio" name="payment_method" value="card" class="form-check-input me-2" checked>
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-credit-card fa-lg text-primary me-2"></i>
                                            <div>
                                                <div class="fw-semibold">Card (Visa/Mastercard)</div>
                                                <small class="text-muted">Pay with your debit/credit card</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="w-100">
                                    <input type="radio" name="payment_method" value="mobile-money" class="form-check-input me-2">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-mobile-alt fa-lg text-success me-2"></i>
                                            <div>
                                                <div class="fw-semibold">Mobile Money (Ghana)</div>
                                                <small class="text-muted">MTN, Vodafone, AirtelTigo</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 mt-2 align-items-center">
                                            <img src="/images/payment/mtn-momo.svg" alt="MTN MoMo" width="40" height="24">
                                            <img src="/images/payment/vodafone-cash.svg" alt="Vodafone Cash" width="40" height="24">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="button" id="paystackPayButton" class="btn btn-success btn-lg">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            <i class="fas fa-lock me-2"></i><span id="btnText">Proceed to Pay</span>
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
                                            <img src="/images/payment/visa.svg" alt="Visa" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/mastercard.svg" alt="Mastercard" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/mtn-momo.svg" alt="MTN Mobile Money" width="50" height="30">
                                        </div>
                                        <div class="payment-method-icon">
                                            <img src="/images/payment/vodafone-cash.svg" alt="Vodafone Cash" width="50" height="30">
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

<script>
const payButton = document.getElementById('paystackPayButton');
const btnSpinner = document.getElementById('btnSpinner');
const btnText = document.getElementById('btnText');

payButton.addEventListener('click', function() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    const paymentMethod = selected ? selected.value : 'card';

    btnSpinner.classList.remove('d-none');
    payButton.disabled = true;
    btnText.textContent = 'Initializing...';

    fetch('/applications/<?= $application['id'] ?>/initialize-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ payment_method: paymentMethod })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success && result.data && result.data.authorization_url) {
            window.location.href = result.data.authorization_url; // Paystack hosted checkout
        } else {
            alert('Payment initialization failed: ' + (result.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while initializing payment. Please try again.');
    })
    .finally(() => {
        btnSpinner.classList.add('d-none');
        payButton.disabled = false;
        btnText.textContent = 'Proceed to Pay';
    });
});
</script> 