<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Payment Receipt<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-receipt"></i> Payment Receipt
        </h1>
        <div class="btn-group">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button class="btn btn-success" onclick="downloadReceipt()">
                <i class="fas fa-download"></i> Download PDF
            </button>
            <a href="<?= site_url('tenant/payments') ?>" class="btn btn-secondary">
                <i class="fas fa-list"></i> Back to Payments
            </a>
        </div>
    </div>

    <?php if ($payment): ?>
        <!-- Receipt Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow" id="receipt-content">
                    <!-- Receipt Header -->
                    <div class="card-header bg-success text-white text-center">
                        <div class="row align-items-center">
                            <div class="col-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0">Payment Receipt</h4>
                                <p class="mb-0">Thank you for your payment!</p>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0">PAID</h5>
                                <small><?= date('M d, Y', strtotime($payment['payment_date'])) ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Company/Property Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">Property Management Company</h6>
                                <p class="mb-1"><strong>ABC Property Management</strong></p>
                                <p class="mb-1">123 Main Street</p>
                                <p class="mb-1">City, State 12345</p>
                                <p class="mb-0">Phone: (123) 456-7890</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-primary">Receipt Details</h6>
                                <p class="mb-1"><strong>Receipt #:</strong> <?= 'RCP-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                <p class="mb-1"><strong>Transaction ID:</strong> <?= $payment['transaction_id'] ?></p>
                                <p class="mb-1"><strong>Payment Date:</strong> <?= date('M d, Y g:i A', strtotime($payment['payment_date'])) ?></p>
                                <p class="mb-0"><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></p>
                            </div>
                        </div>

                        <!-- Tenant Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">Bill To</h6>
                                <p class="mb-1"><strong><?= esc($payment['tenant_first_name'] . ' ' . $payment['tenant_last_name']) ?></strong></p>
                                <p class="mb-1"><?= esc($payment['tenant_email']) ?></p>
                                <?php if (!empty($payment['tenant_phone'])): ?>
                                    <p class="mb-0"><?= esc($payment['tenant_phone']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Property Information</h6>
                                <p class="mb-1"><strong><?= esc($payment['property_name']) ?></strong></p>
                                <p class="mb-0"><?= esc($payment['property_address']) ?></p>
                            </div>
                        </div>

                        <!-- Payment Details Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th>Period</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Monthly Rent</strong>
                                            <br><small class="text-muted"><?= esc($payment['property_name']) ?></small>
                                        </td>
                                        <td><?= date('M Y', strtotime($payment['due_date'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($payment['due_date'])) ?></td>
                                        <td class="text-end">$<?= number_format($payment['amount'], 2) ?></td>
                                    </tr>
                                    
                                    <?php if (($payment['late_fee'] ?? 0) > 0): ?>
                                        <tr>
                                            <td>
                                                <strong>Late Fee</strong>
                                                <br><small class="text-muted">Payment received after due date</small>
                                            </td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="text-end text-danger">$<?= number_format($payment['late_fee'], 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <tr>
                                        <td>
                                            <strong>Processing Fee</strong>
                                            <br><small class="text-muted">Online payment processing</small>
                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-end">$2.50</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="3" class="text-end">Total Amount Paid:</th>
                                        <th class="text-end">$<?= number_format($payment['amount'] + ($payment['late_fee'] ?? 0) + 2.50, 2) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle"></i> Payment Status</h6>
                                    <p class="mb-1"><strong>Status:</strong> PAID IN FULL</p>
                                    <p class="mb-0"><strong>Processed:</strong> <?= date('M d, Y g:i A', strtotime($payment['payment_date'])) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Next Payment</h6>
                                    <p class="mb-1"><strong>Due Date:</strong> <?= date('M d, Y', strtotime($payment['due_date'] . ' +1 month')) ?></p>
                                    <p class="mb-0"><strong>Amount:</strong> $<?= number_format($payment['amount'], 2) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary">Important Notes</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i>
                                        This receipt serves as proof of payment for the period indicated above.
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i>
                                        Payment has been successfully processed and applied to your account.
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i>
                                        Keep this receipt for your records and tax purposes.
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-info-circle text-info"></i>
                                        For questions about this payment, contact us at (123) 456-7890 or support@propertymanagement.com
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Footer -->
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Generated on <?= date('M d, Y g:i A') ?><br>
                                    This is an electronic receipt.
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">
                                    ABC Property Management<br>
                                    www.abcpropertymanagement.com
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-credit-card"></i><br>
                                    Make Another Payment
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="<?= site_url('tenant/payments') ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-history"></i><br>
                                    Payment History
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-info btn-block">
                                    <i class="fas fa-home"></i><br>
                                    Dashboard
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-secondary btn-block" onclick="emailReceipt()">
                                    <i class="fas fa-envelope"></i><br>
                                    Email Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Receipt Not Found -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                        <h4>Receipt Not Found</h4>
                        <p class="text-muted">The requested payment receipt could not be found or you don't have permission to view it.</p>
                        <div class="mt-4">
                            <a href="<?= site_url('tenant/payments') ?>" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All Payments
                            </a>
                            <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function downloadReceipt() {
    // In a real application, this would generate and download a PDF
    alert('PDF download functionality will be implemented soon. For now, please use the print option.');
}

function emailReceipt() {
    if (confirm('Send a copy of this receipt to your email address?')) {
        fetch('<?= site_url('tenant/payments/email-receipt/' . ($payment['id'] ?? '0')) ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Receipt has been sent to your email address!');
            } else {
                alert('Error sending email: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

// Print styles
const printStyle = document.createElement('style');
printStyle.textContent = `
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt-content, #receipt-content * {
            visibility: visible;
        }
        #receipt-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .btn, .d-print-none {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .alert {
            border: 1px solid #ddd !important;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }
        .bg-success {
            background-color: #28a745 !important;
            -webkit-print-color-adjust: exact;
        }
        .text-white {
            color: #fff !important;
            -webkit-print-color-adjust: exact;
        }
    }
`;
document.head.appendChild(printStyle);

// Auto-focus on print button for keyboard users
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.querySelector('button[onclick="window.print()"]');
    if (printBtn) {
        printBtn.focus();
    }
});
</script>

<style>
.receipt-container {
    background: #f8f9fc;
    min-height: 100vh;
}

.card {
    border: none;
    border-radius: 15px;
}

.table th {
    font-weight: 600;
}

.alert {
    border-radius: 10px;
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-group .btn {
        width: 100%;
    }
}
</style>
<?= $this->endSection() ?>