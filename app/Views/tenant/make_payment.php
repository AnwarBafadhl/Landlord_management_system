<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Make Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Make Payment
        </h1>
        <a href="<?= site_url('tenant/payments') ?>" class="btn btn-secondary">
            <i class="fas fa-history"></i> Payment History
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Payment Selection -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Select Payment to Make
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($pending_payments)): ?>
                        <form action="<?= site_url('tenant/payments/process') ?>" method="post" id="paymentForm">
                            <?= csrf_field() ?>
                            
                            <!-- Validation Errors -->
                            <?php if (session()->getFlashdata('validation')): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                                            <li><?= $error ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Payment Selection -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Late Fee</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_payments as $payment): ?>
                                            <tr class="<?= $payment['status'] === 'overdue' ? 'table-danger' : '' ?>">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input payment-checkbox" type="radio" 
                                                               name="payment_id" value="<?= $payment['id'] ?>" 
                                                               data-amount="<?= $payment['amount'] ?>"
                                                               data-late-fee="<?= $payment['late_fee'] ?? 0 ?>"
                                                               id="payment_<?= $payment['id'] ?>" required>
                                                        <label class="form-check-label" for="payment_<?= $payment['id'] ?>">
                                                            Select
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('M d, Y', strtotime($payment['due_date'])) ?>
                                                    <?php if ($payment['status'] === 'overdue'): ?>
                                                        <br><small class="text-danger">OVERDUE</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>$<?= number_format($payment['amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $payment['status'] === 'overdue' ? 'danger' : 'warning' ?>">
                                                        <?= ucfirst($payment['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (($payment['late_fee'] ?? 0) > 0): ?>
                                                        <span class="text-danger">$<?= number_format($payment['late_fee'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">$0.00</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong>$<?= number_format($payment['amount'] + ($payment['late_fee'] ?? 0), 2) ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-credit-card"></i> Payment Method
                                    </h6>
                                    
                                    <div class="row">
                                        <!-- Stripe Credit Card -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('stripe_card')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="stripe_card" 
                                                           id="stripe_card" class="d-none" required>
                                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                    <h6>Credit/Debit Card</h6>
                                                    <small class="text-muted">Visa, MasterCard, American Express</small>
                                                    <div class="mt-2">
                                                        <img src="https://js.stripe.com/v3/fingerprinted/img/visa-729c05c240c4bdb47b03ac81d9945bfe.svg" alt="Visa" style="height: 20px; margin: 2px;">
                                                        <img src="https://js.stripe.com/v3/fingerprinted/img/mastercard-4d8844094130711885b5e41b28c9848f.svg" alt="Mastercard" style="height: 20px; margin: 2px;">
                                                        <img src="https://js.stripe.com/v3/fingerprinted/img/amex-a49b82f46c5cd6a96a6e418a5ca1bd51.svg" alt="Amex" style="height: 20px; margin: 2px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Apple Pay -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('apple_pay')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="apple_pay" 
                                                           id="apple_pay" class="d-none" required>
                                                    <i class="fab fa-apple fa-2x text-dark mb-2"></i>
                                                    <h6>Apple Pay</h6>
                                                    <small class="text-muted">Pay with Touch ID or Face ID</small>
                                                    <div class="mt-2" id="apple-pay-availability" style="display: none;">
                                                        <span class="badge badge-success">Available</span>
                                                    </div>
                                                    <div class="mt-2" id="apple-pay-unavailable">
                                                        <span class="badge badge-secondary">Not Available</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Google Pay -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('google_pay')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="google_pay" 
                                                           id="google_pay" class="d-none" required>
                                                    <i class="fab fa-google-pay fa-2x text-success mb-2"></i>
                                                    <h6>Google Pay</h6>
                                                    <small class="text-muted">Quick and secure payments</small>
                                                    <div class="mt-2" id="google-pay-availability" style="display: none;">
                                                        <span class="badge badge-success">Available</span>
                                                    </div>
                                                    <div class="mt-2" id="google-pay-unavailable">
                                                        <span class="badge badge-secondary">Not Available</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PayPal -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('paypal')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="paypal" 
                                                           id="paypal" class="d-none" required>
                                                    <i class="fab fa-paypal fa-2x text-info mb-2"></i>
                                                    <h6>PayPal</h6>
                                                    <small class="text-muted">Secure PayPal payment</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bank Transfer -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('bank_transfer')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="bank_transfer" 
                                                           id="bank_transfer" class="d-none" required>
                                                    <i class="fas fa-university fa-2x text-success mb-2"></i>
                                                    <h6>Bank Transfer</h6>
                                                    <small class="text-muted">Direct bank account transfer</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cryptocurrency -->
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card payment-method-card" onclick="selectPaymentMethod('crypto')">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" value="crypto" 
                                                           id="crypto" class="d-none" required>
                                                    <i class="fab fa-bitcoin fa-2x text-warning mb-2"></i>
                                                    <h6>Cryptocurrency</h6>
                                                    <small class="text-muted">Bitcoin, Ethereum, USDC</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Forms -->
                            
                            <!-- Stripe Credit Card Form -->
                            <div id="stripe_card_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">Credit Card Information</h6>
                                <div class="mb-3">
                                    <label class="form-label">Card Details</label>
                                    <div id="stripe-card-element" style="padding: 10px; border: 1px solid #ced4da; border-radius: 0.375rem;">
                                        <!-- Stripe Elements will create input fields here -->
                                    </div>
                                    <div id="stripe-card-errors" role="alert" class="text-danger mt-2"></div>
                                </div>
                            </div>

                            <!-- Apple Pay Form -->
                            <div id="apple_pay_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">Apple Pay</h6>
                                <div class="text-center">
                                    <div id="apple-pay-button" style="height: 44px;"></div>
                                </div>
                            </div>

                            <!-- Google Pay Form -->
                            <div id="google_pay_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">Google Pay</h6>
                                <div class="text-center">
                                    <div id="google-pay-button"></div>
                                </div>
                            </div>

                            <!-- PayPal Form -->
                            <div id="paypal_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">PayPal Payment</h6>
                                <div id="paypal-button-container"></div>
                            </div>

                            <!-- Bank Transfer Form -->
                            <div id="bank_transfer_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">Bank Account Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="bank_account" class="form-label">Account Number</label>
                                            <input type="text" class="form-control" id="bank_account" name="bank_account" 
                                                   placeholder="Account Number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="routing_number" class="form-label">Routing Number</label>
                                            <input type="text" class="form-control" id="routing_number" name="routing_number" 
                                                   placeholder="Routing Number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Crypto Form -->
                            <div id="crypto_form" class="payment-details-form" style="display: none;">
                                <h6 class="text-primary mb-3">Cryptocurrency Payment</h6>
                                <div class="mb-3">
                                    <label for="crypto_type" class="form-label">Select Cryptocurrency</label>
                                    <select class="form-select" id="crypto_type" name="crypto_type">
                                        <option value="bitcoin">Bitcoin (BTC)</option>
                                        <option value="ethereum">Ethereum (ETH)</option>
                                        <option value="usdc">USD Coin (USDC)</option>
                                    </select>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    You'll be redirected to complete the cryptocurrency payment.
                                </div>
                            </div>

                            <input type="hidden" id="amount" name="amount" value="">
                            <input type="hidden" id="stripe_payment_method_id" name="stripe_payment_method_id" value="">

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success btn-lg" id="submitPayment" disabled>
                                    <i class="fas fa-credit-card"></i> Process Payment - $<span id="payment-amount">0.00</span>
                                </button>
                                <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>All Payments Up to Date!</h4>
                            <p class="text-muted">You don't have any pending payments at this time.</p>
                            <div class="mt-4">
                                <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-primary">
                                    <i class="fas fa-home"></i> Back to Dashboard
                                </a>
                                <a href="<?= site_url('tenant/payments') ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-history"></i> View Payment History
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Security Info -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Security & Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6><i class="fas fa-lock text-success"></i> Bank-Level Security</h6>
                                <small class="text-muted">256-bit SSL encryption protects your payment information.</small>
                            </div>
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-receipt text-info"></i> Instant Receipt</h6>
                                <small class="text-muted">Digital receipt sent immediately after payment.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6><i class="fas fa-clock text-warning"></i> Processing Time</h6>
                                <small class="text-muted">Payments processed within 1-2 business days.</small>
                            </div>
                            
                            <div>
                                <h6><i class="fas fa-question-circle text-primary"></i> Need Help?</h6>
                                <small class="text-muted">
                                    <a href="mailto:support@propertymanagement.com">Contact Support</a> 
                                    or call (123) 456-7890
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lease Information -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-home"></i> Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($lease): ?>
                        <h6><?= esc($lease['property_name']) ?></h6>
                        <p class="text-muted mb-2"><?= esc($lease['property_address']) ?></p>
                        <p class="mb-1">
                            <strong>Monthly Rent:</strong> $<?= number_format($lease['rent_amount'], 2) ?>
                        </p>
                        <p class="mb-3">
                            <strong>Lease End:</strong> <?= date('M d, Y', strtotime($lease['lease_end'])) ?>
                        </p>
                        <a href="<?= site_url('tenant/lease') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-file-contract"></i> View Full Lease
                        </a>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-file-contract fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-3">No active lease information available.</p>
                            <a href="<?= site_url('tenant/lease') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-file-contract"></i> View Lease Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-receipt"></i> Payment Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div id="payment-summary" style="display: none;">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Base Amount:</span>
                            <span id="base-amount">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Late Fee:</span>
                            <span id="late-fee">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Processing Fee:</span>
                            <span id="processing-fee">$2.50</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total Amount:</strong>
                            <strong id="total-amount">$0.00</strong>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                A small processing fee is applied to online payments.
                            </small>
                        </div>
                    </div>
                    
                    <div id="no-payment-selected">
                        <div class="text-center text-muted">
                            <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                            <p>Select a payment to see details</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Info -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Payment Methods
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <i class="fas fa-credit-card text-primary"></i> 
                            <strong>Cards:</strong> Instant processing
                        </div>
                        <div class="mb-2">
                            <i class="fab fa-apple text-dark"></i> 
                            <strong>Apple Pay:</strong> Touch/Face ID required
                        </div>
                        <div class="mb-2">
                            <i class="fab fa-google-pay text-success"></i> 
                            <strong>Google Pay:</strong> Android devices
                        </div>
                        <div class="mb-2">
                            <i class="fab fa-paypal text-info"></i> 
                            <strong>PayPal:</strong> Redirect to PayPal
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-university text-success"></i> 
                            <strong>Bank:</strong> 1-3 business days
                        </div>
                        <div class="mb-0">
                            <i class="fab fa-bitcoin text-warning"></i> 
                            <strong>Crypto:</strong> Network confirmation required
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include payment processor scripts -->
<script src="https://js.stripe.com/v3/"></script>
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=USD"></script>

<style>
.payment-method-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    min-height: 120px;
}

.payment-method-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-method-card.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.payment-method-card.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.payment-method-card.disabled:hover {
    transform: none;
    border-color: transparent;
}

.payment-details-form {
    background: #f8f9fc;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
}

#stripe-card-element {
    height: 40px;
}

.google-pay-button,
.apple-pay-button {
    height: 44px;
    border-radius: 4px;
}
</style>

<script>
let selectedPaymentMethod = '';
let selectedAmount = 0;
let selectedLateFee = 0;
let stripe, stripeCard, paymentRequest;

// Initialize payment processors
document.addEventListener('DOMContentLoaded', function() {
    initializeStripe();
    checkApplePayAvailability();
    checkGooglePayAvailability();
    initializePayPal();
});

// Initialize Stripe
function initializeStripe() {
    // Replace with your actual Stripe publishable key
    stripe = Stripe('pk_test_YOUR_STRIPE_PUBLISHABLE_KEY');
    const elements = stripe.elements();
    
    stripeCard = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
        },
    });
}

// Check Apple Pay availability
function checkApplePayAvailability() {
    if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
        document.getElementById('apple-pay-availability').style.display = 'block';
        document.getElementById('apple-pay-unavailable').style.display = 'none';
    } else {
        document.querySelector('input[value="apple_pay"]').closest('.payment-method-card').classList.add('disabled');
    }
}

// Check Google Pay availability
function checkGooglePayAvailability() {
    // Google Pay availability check would go here
    // For demo purposes, we'll assume it's available on Android devices
    const isAndroid = /Android/.test(navigator.userAgent);
    if (isAndroid) {
        document.getElementById('google-pay-availability').style.display = 'block';
        document.getElementById('google-pay-unavailable').style.display = 'none';
    } else {
        document.querySelector('input[value="google_pay"]').closest('.payment-method-card').classList.add('disabled');
    }
}

// Initialize PayPal
function initializePayPal() {
    // PayPal initialization will happen when the form is shown
}

// Payment method selection
function selectPaymentMethod(method) {
    // Check if method is disabled
    const card = document.querySelector(`input[value="${method}"]`).closest('.payment-method-card');
    if (card.classList.contains('disabled')) {
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Hide all payment forms
    document.querySelectorAll('.payment-details-form').forEach(form => {
        form.style.display = 'none';
    });
    
    // Select new method
    document.querySelector(`input[value="${method}"]`).checked = true;
    card.classList.add('selected');
    document.getElementById(`${method}_form`).style.display = 'block';
    
    selectedPaymentMethod = method;
    
    // Initialize specific payment method
    if (method === 'stripe_card') {
        stripeCard.mount('#stripe-card-element');
        stripeCard.on('change', ({error}) => {
            const displayError = document.getElementById('stripe-card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });
    } else if (method === 'paypal') {
        initializePayPalButtons();
    }
    
    updateSubmitButton();
}

// Initialize PayPal buttons
function initializePayPalButtons() {
    if (typeof paypal !== 'undefined') {
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: (selectedAmount + selectedLateFee + 2.50).toFixed(2)
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Process PayPal payment
                    processPayPalPayment(details);
                });
            },
            onError: function(err) {
                console.error('PayPal Error:', err);
                alert('PayPal payment failed. Please try again.');
            }
        }).render('#paypal-button-container');
    }
}

// Payment selection
document.querySelectorAll('.payment-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            selectedAmount = parseFloat(this.dataset.amount);
            selectedLateFee = parseFloat(this.dataset.lateFee || 0);
            document.getElementById('amount').value = selectedAmount + selectedLateFee;
            
            // Update payment summary
            updatePaymentSummary(selectedAmount, selectedLateFee);
            updateSubmitButton();
        }
    });
});

function updatePaymentSummary(amount, lateFee) {
    const processingFee = 2.50;
    const total = amount + lateFee + processingFee;
    
    document.getElementById('base-amount').textContent = ' + amount.toFixed(2)';
    document.getElementById('late-fee').textContent = ' + lateFee.toFixed(2)';
    document.getElementById('processing-fee').textContent = ' + processingFee.toFixed(2)';
    document.getElementById('total-amount').textContent = ' + total.toFixed(2)';
    document.getElementById('payment-amount').textContent = total.toFixed(2);
    
    document.getElementById('payment-summary').style.display = 'block';
    document.getElementById('no-payment-selected').style.display = 'none';
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitPayment');
    const hasPayment = selectedAmount > 0;
    const hasMethod = selectedPaymentMethod !== '';
    
    // Hide submit button for methods that handle their own submission
    if (selectedPaymentMethod === 'paypal' || selectedPaymentMethod === 'apple_pay' || selectedPaymentMethod === 'google_pay') {
        submitBtn.style.display = 'none';
    } else {
        submitBtn.style.display = 'inline-block';
        submitBtn.disabled = !(hasPayment && hasMethod);
    }
}

// Process PayPal payment
function processPayPalPayment(details) {
    fetch('<?= site_url('tenant/payments/process-paypal') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            payment_id: document.querySelector('input[name="payment_id"]:checked').value,
            paypal_order_id: details.id,
            paypal_payer_id: details.payer.payer_id,
            amount: selectedAmount + selectedLateFee,
            '<?= csrf_token() ?>': document.querySelector('input[name="<?= csrf_token() ?>"]').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?= site_url('tenant/payments/success') ?>?payment_id=' + data.payment_id;
        } else {
            alert('Payment processing failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment processing failed. Please try again.');
    });
}

// Process Apple Pay
function processApplePayment() {
    if (!window.ApplePaySession) {
        alert('Apple Pay is not supported on this device.');
        return;
    }

    const paymentRequest = {
        countryCode: 'US',
        currencyCode: 'USD',
        supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
        merchantCapabilities: ['supports3DS'],
        total: {
            label: 'Rent Payment',
            amount: (selectedAmount + selectedLateFee + 2.50).toFixed(2)
        }
    };

    const session = new ApplePaySession(3, paymentRequest);

    session.onvalidatemerchant = function(event) {
        // Validate merchant with your server
        fetch('<?= site_url('tenant/payments/apple-pay-validate') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                validationURL: event.validationURL,
                '<?= csrf_token() ?>': document.querySelector('input[name="<?= csrf_token() ?>"]').value
            })
        })
        .then(response => response.json())
        .then(merchantSession => {
            session.completeMerchantValidation(merchantSession);
        });
    };

    session.onpaymentauthorized = function(event) {
        // Process the payment
        const payment = event.payment;
        
        fetch('<?= site_url('tenant/payments/process-apple-pay') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                payment_id: document.querySelector('input[name="payment_id"]:checked').value,
                apple_pay_token: payment.token,
                amount: selectedAmount + selectedLateFee,
                '<?= csrf_token() ?>': document.querySelector('input[name="<?= csrf_token() ?>"]').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                session.completePayment(ApplePaySession.STATUS_SUCCESS);
                window.location.href = '<?= site_url('tenant/payments/success') ?>?payment_id=' + data.payment_id;
            } else {
                session.completePayment(ApplePaySession.STATUS_FAILURE);
                alert('Payment failed: ' + data.message);
            }
        })
        .catch(error => {
            session.completePayment(ApplePaySession.STATUS_FAILURE);
            console.error('Error:', error);
            alert('Payment processing failed. Please try again.');
        });
    };

    session.begin();
}

// Form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedPaymentMethod === 'stripe_card') {
        processStripePayment();
    } else if (selectedPaymentMethod === 'apple_pay') {
        processApplePayment();
    } else {
        // Traditional form submission for other methods
        processTraditionalPayment();
    }
});

// Process Stripe payment
async function processStripePayment() {
    const submitBtn = document.getElementById('submitPayment');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    try {
        const {token, error} = await stripe.createToken(stripeCard);
        
        if (error) {
            document.getElementById('stripe-card-errors').textContent = error.message;
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }

        // Submit payment to server
        const response = await fetch('<?= site_url('tenant/payments/process-stripe') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                payment_id: document.querySelector('input[name="payment_id"]:checked').value,
                stripe_token: token.id,
                amount: selectedAmount + selectedLateFee,
                '<?= csrf_token() ?>': document.querySelector('input[name="<?= csrf_token() ?>"]').value
            })
        });

        const data = await response.json();
        
        if (data.success) {
            window.location.href = '<?= site_url('tenant/payments/success') ?>?payment_id=' + data.payment_id;
        } else {
            alert('Payment failed: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Payment processing failed. Please try again.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Process traditional payment methods
function processTraditionalPayment() {
    const submitBtn = document.getElementById('submitPayment');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    
    // Validate payment method specific fields
    let isValid = true;
    
    if (selectedPaymentMethod === 'bank_transfer') {
        const bankAccount = document.getElementById('bank_account').value;
        const routingNumber = document.getElementById('routing_number').value;
        
        if (!bankAccount || !routingNumber) {
            alert('Please fill in all bank account information');
            isValid = false;
        }
    } else if (selectedPaymentMethod === 'crypto') {
        const cryptoType = document.getElementById('crypto_type').value;
        if (!cryptoType) {
            alert('Please select a cryptocurrency');
            isValid = false;
        }
    }
    
    if (!isValid) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    // Submit the form
    setTimeout(() => {
        this.submit();
    }, 1000);
}

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);

// Add click handler for Apple Pay button
document.addEventListener('click', function(e) {
    if (e.target.closest('#apple-pay-button')) {
        processApplePayment();
    }
});
</script>
<?= $this->endSection() ?>