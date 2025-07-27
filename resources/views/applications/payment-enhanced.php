<?php
$pageTitle = 'Secure Payment - Birth Certificate Application';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Secure Payment Processing
                    </h2>
                </div>
                
                <div class="card-body">
                    <!-- Payment Progress Indicator -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="progress-steps">
                                <div class="step active">
                                    <div class="step-number">1</div>
                                    <div class="step-label">Review</div>
                                </div>
                                <div class="step">
                                    <div class="step-number">2</div>
                                    <div class="step-label">Payment</div>
                                </div>
                                <div class="step">
                                    <div class="step-number">3</div>
                                    <div class="step-label">Confirmation</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary Card -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Payment Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Applicant Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                    <p class="mb-1"><strong>Application ID:</strong> #<?= htmlspecialchars($application['id']) ?></p>
                                    <p class="mb-0"><strong>Tracking:</strong> <span class="badge bg-info"><?= htmlspecialchars($application['tracking_number'] ?? 'Pending') ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Payment Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Birth Certificate Fee:</td>
                                                <td class="text-end">GH₵<?= number_format($amount, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>Processing Fee:</td>
                                                <td class="text-end">GH₵5.00</td>
                                            </tr>
                                            <tr class="table-primary">
                                                <th>Total Amount:</th>
                                                <th class="text-end">GH₵<?= number_format($amount + 5, 2) ?></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Selection -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Select Payment Method
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="payment-method-card" data-method="paystack">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                <h6>Card Payment</h6>
                                                <small class="text-muted">Visa, Mastercard</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="payment-method-card" data-method="mobile-money">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-mobile-alt fa-2x text-success mb-2"></i>
                                                <h6>Mobile Money</h6>
                                                <small class="text-muted">MTN, Vodafone, AirtelTigo</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div id="paymentForm" class="d-none">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You will be redirected to our secure payment gateway to complete your transaction.
                        </div>
                        
                        <div class="text-center">
                            <button type="button" id="payButton" class="btn btn-success btn-lg">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <i class="fas fa-lock me-2"></i>
                                <span id="payButtonText">Proceed to Payment</span>
                            </button>
                        </div>
                    </div>

                    <!-- Security Badges -->
                    <div class="text-center mt-4">
                        <div class="security-badges">
                            <span class="badge bg-success me-2">
                                <i class="fas fa-shield-alt me-1"></i>SSL Secured
                            </span>
                            <span class="badge bg-info me-2">
                                <i class="fas fa-lock me-1"></i>PCI Compliant
                            </span>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-user-shield me-1"></i>Data Protected
                            </span>
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

<style>
.progress-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 1rem;
    opacity: 0.5;
}

.step.active {
    opacity: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.step.active .step-number {
    background: #007bff;
    color: white;
}

.payment-method-card {
    cursor: pointer;
    transition: transform 0.2s;
}

.payment-method-card:hover {
    transform: translateY(-2px);
}

.payment-method-card.selected .card {
    border-color: #007bff !important;
    background-color: #f8f9fa;
}

.security-badges {
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .progress-steps {
        flex-direction: column;
    }
    
    .step {
        margin: 0.5rem 0;
    }
}
</style>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedMethod = null;
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const spinner = payButton.querySelector('.spinner-border');

    // Payment method selection
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectedMethod = this.dataset.method;
            paymentForm.classList.remove('d-none');
        });
    });

    // Payment initiation
    payButton.addEventListener('click', async function() {
        if (!selectedMethod) {
            alert('Please select a payment method');
            return;
        }

        // Show loading state
        payButton.disabled = true;
        spinner.classList.remove('d-none');
        payButtonText.textContent = 'Processing...';

        try {
            const response = await fetch('/applications/<?= $application['id'] ?>/initialize-payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ payment_method: selectedMethod })
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to Paystack payment page
                window.location.href = result.data.authorization_url;
            } else {
                throw new Error(result.error || 'Payment initialization failed');
            }
        } catch (error) {
            console.error('Payment error:', error);
            alert('Payment initialization failed: ' + error.message);
        } finally {
            // Reset button state
            payButton.disabled = false;
            spinner.classList.add('d-none');
            payButtonText.textContent = 'Proceed to Payment';
        }
    });

    // Handle browser back button
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache, reset form state
            payButton.disabled = false;
            spinner.classList.add('d-none');
            payButtonText.textContent = 'Proceed to Payment';
        }
    });
});
</script>
