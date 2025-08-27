<?= $this->extend('layouts/landlord') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-money-bill-wave"></i> Income & Expense Tracking
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <!-- Export Dropdown -->
            <div class="dropdown d-inline-block me-2">
                <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                    data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export Data
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportPayments('pdf')">
                            <i class="fas fa-file-pdf text-danger"></i> Export as PDF
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportPayments('excel')">
                            <i class="fas fa-file-excel text-success"></i> Export as CSV (Standard)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportPayments('excel-alt')">
                            <i class="fas fa-file-csv text-info"></i> Export as CSV (Alternative)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportPayments('tsv')">
                            <i class="fas fa-file-alt text-warning"></i> Export as Text File
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Add Income Button -->
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus"></i> Add Income
            </button>

            <!-- Add Expense Button -->
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus"></i> Add Expense
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Net Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format($totals['net_income'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Expenses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format($totals['total_expenses'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Monthly Net Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format($totals['monthly_net'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter Payment Records
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= site_url('landlord/payments') ?>" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_type">Payment Type</label>
                            <select name="payment_type" id="payment_type" class="form-control">
                                <option value="all" <?= ($filters['payment_type'] ?? 'all') === 'all' ? 'selected' : '' ?>>
                                    All Payments</option>
                                <option value="income" <?= ($filters['payment_type'] ?? '') === 'income' ? 'selected' : '' ?>>Income</option>
                                <option value="expense" <?= ($filters['payment_type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="property_id">Property</label>
                            <select name="property_id" id="property_id" class="form-control">
                                <option value="">All Properties</option>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>" <?= ($filters['property_id'] ?? '') == $property['id'] ? 'selected' : '' ?>>
                                        <?= esc($property['property_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                value="<?= esc($filters['date_from'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                value="<?= esc($filters['date_to'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-grid gap-1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="<?= site_url('landlord/payments') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Payment Records
                <span class="badge bg-secondary ms-2"><?= count($payments ?? []) ?> records</span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($payments)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No payments found</h5>
                    <p class="text-muted">Start recording your income and expense payments.</p>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                        data-bs-target="#addIncomeModal">
                        <i class="fas fa-plus"></i> Add Income
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fas fa-minus"></i> Add Expense
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Receipt</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($payment['date'] ?? $payment['created_at'])) ?></td>
                                    <td>
                                        <?php if ($payment['type'] === 'income'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up"></i> Income
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down"></i> Expense
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($payment['property_name'] ?? 'N/A') ?></strong>
                                        <?php if (!empty($payment['property_address'])): ?>
                                            <br><small class="text-muted"><?= esc($payment['property_address']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['unit_name'])): ?>
                                            <span class="badge bg-info"><?= esc($payment['unit_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-<?= $payment['type'] === 'income' ? 'success' : 'danger' ?>">
                                            SAR <?= number_format($payment['amount'] ?? 0, 2) ?>
                                        </strong>
                                        <?php if (!empty($payment['source'])): ?>
                                            <br><small class="text-muted"><?= esc($payment['source']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['period'])): ?>
                                            <?= esc($payment['period']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['receipt_file'])): ?>
                                            <a href="#" onclick="viewReceipt('<?= esc($payment['receipt_file']) ?>')"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-alt"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No receipt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= ucfirst(str_replace('_', ' ', $payment['method'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Income Payment Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add Income Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('landlord/income-payment/store') ?>" method="post" enctype="multipart/form-data"
                id="incomeForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Record income received for a specific property and unit.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="income_date" class="form-label">Date *</label>
                                <input type="date" name="date" id="income_date" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="income_property" class="form-label">Property *</label>
                                <select name="property_id" id="income_property" class="form-control" required
                                    onchange="loadIncomeUnits()">
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="income_unit" class="form-label">Unit *</label>
                                <select name="unit_id" id="income_unit" class="form-control" required>
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="income_amount" class="form-label">Income Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">SAR</span>
                                    <input type="number" name="amount" id="income_amount" class="form-control"
                                        step="0.01" min="0" required placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="income_source" class="form-label">Income Source *</label>
                        <input type="text" name="source" id="income_source" class="form-control" required
                            placeholder="Enter income source (e.g., Rent Payment, Security Deposit, Late Fees, etc.)">
                        <small class="form-text text-muted">
                            Common sources: Rent Payment, Security Deposit, Late Fees, Parking Fees, Utility
                            Reimbursement, Other Income
                        </small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="income_description" class="form-label">Description *</label>
                        <textarea name="description" id="income_description" class="form-control" rows="3"
                            placeholder="Describe the income payment details" required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="income_method" class="form-label">Payment Method</label>
                        <select name="method" id="income_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="income_receipt" class="form-label">Receipt/Invoice (Optional)</label>
                        <input type="file" name="receipt_file" id="income_receipt" class="form-control" accept=".pdf">
                        <small class="form-text text-muted">
                            Only PDF files are accepted (Max 5MB)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitIncomeBtn">
                        <i class="fas fa-save"></i> Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Payment Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-minus"></i> Add Expense Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('landlord/expense-payment/store') ?>" method="post" enctype="multipart/form-data"
                id="expenseForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Record expense payments made for a specific property and unit.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="expense_date" class="form-label">Date *</label>
                                <input type="date" name="date" id="expense_date" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="expense_property" class="form-label">Property *</label>
                                <select name="property_id" id="expense_property" class="form-control" required
                                    onchange="loadExpenseUnits()">
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="expense_unit" class="form-label">Unit *</label>
                                <select name="unit_id" id="expense_unit" class="form-control" required>
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="expense_amount" class="form-label">Expense Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">SAR</span>
                                    <input type="number" name="amount" id="expense_amount" class="form-control"
                                        step="0.01" min="0" required placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="expense_details" class="form-label">Expense Details *</label>
                        <select name="expense_type" id="expense_details" class="form-control" required>
                            <option value="">Select Expense Type</option>
                            <option value="maintenance">Maintenance & Repairs</option>
                            <option value="utilities">Utilities</option>
                            <option value="insurance">Insurance</option>
                            <option value="property_tax">Property Tax</option>
                            <option value="cleaning">Cleaning Services</option>
                            <option value="advertising">Advertising & Marketing</option>
                            <option value="legal">Legal Fees</option>
                            <option value="management">Management Fees</option>
                            <option value="other">Other Expenses</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="expense_description" class="form-label">Description *</label>
                        <textarea name="description" id="expense_description" class="form-control" rows="3"
                            placeholder="Describe the expense payment details" required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="expense_method" class="form-label">Payment Method</label>
                        <select name="method" id="expense_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="expense_receipt" class="form-label">Receipt/Invoice (Optional)</label>
                        <input type="file" name="receipt_file" id="expense_receipt" class="form-control" accept=".pdf">
                        <small class="form-text text-muted">
                            Only PDF files are accepted (Max 5MB)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="submitExpenseBtn">
                        <i class="fas fa-save"></i> Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt"></i> Payment Receipt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="receiptContent">
                    <!-- Receipt content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Load units for income form
    function loadIncomeUnits() {
        const propertyId = document.getElementById('income_property').value;
        const unitSelect = document.getElementById('income_unit');

        if (propertyId) {
            // Show loading state
            unitSelect.innerHTML = '<option value="">Loading units...</option>';
            unitSelect.disabled = true;

            fetch(`<?= site_url('landlord/get-units-by-property') ?>/${propertyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    unitSelect.disabled = false;

                    if (data.error) {
                        unitSelect.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                        console.error('Server error:', data.error);
                        return;
                    }

                    unitSelect.innerHTML = '<option value="">Select Unit</option>';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(unit => {
                            const displayName = unit.unit_name || `Unit ${unit.id}`;
                            unitSelect.innerHTML += `<option value="${unit.id}">${displayName}</option>`;
                        });
                    } else {
                        unitSelect.innerHTML = '<option value="">No units found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    unitSelect.disabled = false;
                    unitSelect.innerHTML = '<option value="">Failed to load units</option>';
                    alert('Failed to load units. Please check your connection and try again.');
                });
        } else {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            unitSelect.disabled = false;
        }
    }

    // Load units for expense form
    function loadExpenseUnits() {
        const propertyId = document.getElementById('expense_property').value;
        const unitSelect = document.getElementById('expense_unit');

        if (propertyId) {
            // Show loading state
            unitSelect.innerHTML = '<option value="">Loading units...</option>';
            unitSelect.disabled = true;

            fetch(`<?= site_url('landlord/get-units-by-property') ?>/${propertyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    unitSelect.disabled = false;

                    if (data.error) {
                        unitSelect.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                        console.error('Server error:', data.error);
                        return;
                    }

                    unitSelect.innerHTML = '<option value="">Select Unit</option>';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(unit => {
                            const displayName = unit.unit_name || `Unit ${unit.id}`;
                            unitSelect.innerHTML += `<option value="${unit.id}">${displayName}</option>`;
                        });
                    } else {
                        unitSelect.innerHTML = '<option value="">No units found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    unitSelect.disabled = false;
                    unitSelect.innerHTML = '<option value="">Failed to load units</option>';
                    alert('Failed to load units. Please check your connection and try again.');
                });
        } else {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            unitSelect.disabled = false;
        }
    }

    // Form validation
    document.getElementById('incomeForm').addEventListener('submit', function (e) {
        const propertyId = document.getElementById('income_property').value;
        const unitId = document.getElementById('income_unit').value;
        const amount = document.getElementById('income_amount').value;

        if (!propertyId || !unitId || !amount || parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('Please fill all required fields correctly.');
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitIncomeBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Income...';
        submitBtn.disabled = true;
    });

    document.getElementById('expenseForm').addEventListener('submit', function (e) {
        const propertyId = document.getElementById('expense_property').value;
        const unitId = document.getElementById('expense_unit').value;
        const amount = document.getElementById('expense_amount').value;

        if (!propertyId || !unitId || !amount || parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('Please fill all required fields correctly.');
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitExpenseBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Expense...';
        submitBtn.disabled = true;
    });

    // FIXED: Receipt viewing function with proper error handling
    function viewReceipt(filename) {
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        const content = document.getElementById('receiptContent');

        // FIXED: Enhanced receipt viewer with proper error handling
        if (!filename) {
            content.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h5>لا يوجد إيصال متاح</h5>
                <p>لم يتم رفع ملف إيصال لهذه المعاملة.</p>
            </div>
        `;
            modal.show();
            return;
        }

        // FIXED: Use the new view route for proper file serving
        const receiptViewUrl = `<?= site_url('landlord/receipt/view/') ?>${filename}`;
        const receiptDownloadUrl = `<?= site_url('landlord/receipt/download/') ?>${filename}`;

        content.innerHTML = `
        <div class="text-center">
            <div class="mb-3">
                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                <h5>إيصال: ${filename}</h5>
            </div>
            
            <!-- FIXED: Improved PDF embedding with proper URL -->
            <div id="pdfContainer" style="position: relative; height: 500px; border: 1px solid #ddd;">
                <iframe src="${receiptViewUrl}" 
                        width="100%" 
                        height="100%"
                        style="border: none;"
                        onload="handlePdfLoad()"
                        onerror="handlePdfError()">
                </iframe>
            </div>
            
            <div class="mt-3">
                <a href="${receiptDownloadUrl}" 
                   class="btn btn-primary me-2" 
                   download="${filename}">
                    <i class="fas fa-download"></i> تحميل PDF
                </a>
                <a href="${receiptViewUrl}" 
                   class="btn btn-outline-primary" 
                   target="_blank">
                    <i class="fas fa-external-link-alt"></i> فتح في نافذة جديدة
                </a>
            </div>
        </div>
    `;

        modal.show();
    }

    // FIXED: Error handling functions
    function handlePdfLoad() {
        console.log('PDF loaded successfully');
        // Hide loading indicator if present
        const loadingDiv = document.querySelector('.pdf-loading');
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
    }

    function handlePdfError() {
        const container = document.getElementById('pdfContainer');
        if (container) {
            container.innerHTML = `
            <div class="alert alert-danger text-center p-4">
                <i class="fas fa-times-circle fa-3x mb-3"></i>
                <h5>لا يمكن عرض ملف PDF</h5>
                <p>تعذر عرض ملف PDF في المتصفح.</p>
                <p class="mb-0">يرجى استخدام زر التحميل لعرض الملف.</p>
            </div>
        `;
        }
    }

    // Updated export function with multiple format options
    function exportPayments(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);

        // Show loading
        const exportBtn = document.getElementById('exportDropdown');
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        exportBtn.disabled = true;

        // Create form for download
        const exportForm = document.createElement('form');
        exportForm.method = 'GET';
        exportForm.style.display = 'none';

        // Set action based on format
        switch (format) {
            case 'pdf':
                exportForm.action = '<?= site_url('landlord/payments/export-pdf') ?>';
                break;
            case 'excel':
                exportForm.action = '<?= site_url('landlord/payments/export-excel') ?>';
                break;
            case 'excel-alt':
                exportForm.action = '<?= site_url('landlord/payments/export-excel-alt') ?>';
                break;
            case 'tsv':
                exportForm.action = '<?= site_url('landlord/payments/export-tsv') ?>';
                break;
            default:
                exportForm.action = '<?= site_url('landlord/payments/export-excel') ?>';
        }

        // Add form parameters
        for (const [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            exportForm.appendChild(input);
        }

        document.body.appendChild(exportForm);
        exportForm.submit();
        document.body.removeChild(exportForm);

        // Reset button
        setTimeout(() => {
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        }, 3000);
    }

    // Reset forms when modals are closed
    document.getElementById('addIncomeModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('incomeForm').reset();
        document.getElementById('submitIncomeBtn').innerHTML = '<i class="fas fa-save"></i> Add Income';
        document.getElementById('submitIncomeBtn').disabled = false;
    });

    document.getElementById('addExpenseModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('expenseForm').reset();
        document.getElementById('submitExpenseBtn').innerHTML = '<i class="fas fa-save"></i> Add Expense';
        document.getElementById('submitExpenseBtn').disabled = false;
    });

    // Initialize DataTable if available - FIXED VERSION
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('paymentsTable');
        if (table) {
            // Check if DataTable library is loaded
            if (typeof DataTable !== 'undefined') {
                new DataTable(table, {
                    "order": [[0, "desc"]], // Sort by date descending
                    "pageLength": 25,
                    "responsive": true,
                    "columnDefs": [
                        { "orderable": false, "targets": [6] } // Make receipt column non-sortable
                    ]
                });
            } else if (window.jQuery && typeof window.jQuery.fn.DataTable === 'function') {
                // Fallback to jQuery DataTable if available
                window.jQuery(table).DataTable({
                    "order": [[0, "desc"]],
                    "pageLength": 25,
                    "responsive": true,
                    "columnDefs": [
                        { "orderable": false, "targets": [6] }
                    ]
                });
            }
        }
    });
</script>

<?= $this->endSection() ?>