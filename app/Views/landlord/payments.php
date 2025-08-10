<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Payment History<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Payment History
        </h1>
        <div>
            <button class="btn btn-success" onclick="generatePaymentReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                This Month Collected
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($payment_stats['this_month_collected'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Outstanding
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($payment_stats['outstanding'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Collection Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $expected = $payment_stats['this_month_expected'] ?? 0;
                                $collected = $payment_stats['this_month_collected'] ?? 0;
                                $rate = $expected > 0 ? round(($collected / $expected) * 100, 1) : 0;
                                echo $rate . '%';
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Year to Date
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($payment_stats['year_to_date'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="property_filter" class="form-label">Property</label>
                    <select class="form-control" id="property_filter" name="property_id">
                        <option value="">All Properties</option>
                        <?php if (!empty($properties)): ?>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= $property['id'] ?>"
                                    <?= (isset($_GET['property_id']) && $_GET['property_id'] == $property['id']) ? 'selected' : '' ?>>
                                    <?= esc($property['property_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-control" id="status_filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="paid" <?= (isset($_GET['status']) && $_GET['status'] == 'paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="overdue" <?= (isset($_GET['status']) && $_GET['status'] == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month_filter" class="form-label">Month</label>
                    <select class="form-control" id="month_filter" name="month">
                        <option value="">All Months</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"
                                <?= (isset($_GET['month']) && $_GET['month'] == str_pad($i, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year_filter" class="form-label">Year</label>
                    <select class="form-control" id="year_filter" name="year">
                        <option value="">All Years</option>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= $currentYear - 5; $year--):
                        ?>
                            <option value="<?= $year ?>"
                                <?= (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= site_url('landlord/payments') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Records -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Records</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Period</th>
                                <th>Total Rent</th>
                                <th>Your Share</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($payment['payment_date'])) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('h:i A', strtotime($payment['created_at'] ?? $payment['payment_date'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= esc($payment['property_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($payment['property_address']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= esc($payment['tenant_first_name'] . ' ' . $payment['tenant_last_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($payment['tenant_email'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?= date('M Y', strtotime($payment['payment_period'] ?? $payment['payment_date'])) ?>
                                        <?php if (isset($payment['late_days']) && $payment['late_days'] > 0): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-clock"></i> <?= $payment['late_days'] ?> days late
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">$<?= number_format($payment['amount'], 2) ?></strong>
                                        <?php if (isset($payment['late_fee']) && $payment['late_fee'] > 0): ?>
                                            <br>
                                            <small class="text-warning">
                                                +$<?= number_format($payment['late_fee'], 2) ?> late fee
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            $<?= number_format($payment['amount'] * $payment['ownership_percentage'] / 100, 2) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            (<?= $payment['ownership_percentage'] ?>%)
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['payment_method'])): ?>
                                            <i class="fas fa-<?= $payment['payment_method'] === 'cash' ? 'money-bill' : ($payment['payment_method'] === 'check' ? 'money-check' : 'credit-card') ?>"></i>
                                            <?= ucfirst($payment['payment_method']) ?>
                                            <?php if (!empty($payment['reference_number'])): ?>
                                                <br>
                                                <small class="text-muted">Ref: <?= esc($payment['reference_number']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="viewPaymentDetails(<?= $payment['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($payment['status'] !== 'paid'): ?>
                                                <button class="btn btn-sm btn-outline-success"
                                                    onclick="markAsPaid(<?= $payment['id'] ?>)" title="Mark as Paid">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="downloadReceipt(<?= $payment['id'] ?>)" title="Download Receipt">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-4x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-500">No Payment Records Found</h4>
                    <p class="text-muted">No payment records match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Summary Chart -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Payment Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Collected:</span>
                            <strong class="text-success">$<?= number_format($payment_stats['total_collected'] ?? 0, 2) ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Outstanding:</span>
                            <strong class="text-warning">$<?= number_format($payment_stats['outstanding'] ?? 0, 2) ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Average Payment:</span>
                            <strong>$<?= number_format($payment_stats['average_payment'] ?? 0, 2) ?></strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6>Payment Methods:</h6>
                        <?php if (!empty($payment_stats['methods'])): ?>
                            <?php foreach ($payment_stats['methods'] as $method => $count): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= ucfirst($method) ?>:</span>
                                    <span><?= $count ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPaymentDetails()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Payment as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="markPaidForm">
                <div class="modal-body">
                    <input type="hidden" id="payment_id" name="payment_id">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number"
                            placeholder="Check number, transaction ID, etc.">
                    </div>
                    <div class="mb-3">
                        <label for="payment_date_confirm" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="payment_date_confirm" name="payment_date"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Any additional notes about this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Payment chart
    const ctx = document.getElementById('paymentChart').getContext('2d');
    const paymentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) ?>,
            datasets: [{
                label: 'Monthly Collections',
                data: <?= json_encode($chart_data['data'] ?? [0, 0, 0, 0, 0, 0]) ?>,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '' +
                                value.toLocaleString()
                            ;
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Collections:'  +
                                context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    function viewPaymentDetails(paymentId) {
        fetch('<?= site_url('landlord/payments/details') ?>/' + paymentId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('paymentDetailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
            })
            .catch(error => {
                alert('Error loading payment details: ' + error.message);
            });
    }

    function markAsPaid(paymentId) {
        document.getElementById('payment_id').value = paymentId;
        new bootstrap.Modal(document.getElementById('markPaidModal')).show();
    }

    function downloadReceipt(paymentId) {
        window.open('<?= site_url('landlord/payments/receipt') ?>/' + paymentId, '_blank');
    }

    function generatePaymentReport() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        window.open('<?= site_url('landlord/payments/export') ?>?' + params, '_blank');
    }

    function printPaymentDetails() {
        const content = document.getElementById('paymentDetailsContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <html>
            <head>
                <title>Payment Details</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="p-4">
                ${content}
                <script>window.print(); window.close();
</script>
</body>

</html>
`);
printWindow.document.close();
}

// Mark as paid form submission
document.getElementById('markPaidForm').addEventListener('submit', function(e) {
e.preventDefault();

const formData = new FormData(this);

fetch('<?= site_url('landlord/payments/mark-paid') ?>', {
method: 'POST',
body: formData,
headers: {
'X-Requested-With': 'XMLHttpRequest'
}
})
.then(response => response.json())
.then(data => {
if (data.success) {
location.reload();
} else {
alert('Error: ' + data.message);
}
})
.catch(error => {
alert('Error: ' + error.message);
});
});

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
e.preventDefault();

const formData = new FormData(this);
const params = new URLSearchParams(formData).toString();
window.location.href = '<?= site_url('landlord/payments') ?>?' + params;
});
</script>

<?= $this->endSection() ?>