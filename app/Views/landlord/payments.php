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
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus"></i> Add Income
            </button>
            <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-minus"></i> Add Expense
            </button>
            <button type="button" class="btn btn-warning" onclick="showTransferReceiptModal()">
                <i class="fas fa-receipt"></i> Add Transfer Receipt
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
                                Total Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format($totals['total_income'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted">Sum of all income transactions (rent + other income) from all
                        properties.</small>
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
                    <small class="text-muted">Sum of all properties' Expenses</small>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Remaining Balance
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format((float) ($total_remaining_balance ?? 0), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-warning"></i>
                        </div>
                    </div>
                    <small class="text-muted">Sum of all properties' remaining balances</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Balance Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-warning shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-wallet"></i> Property Balance
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($property_balances)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No properties with shares found</h5>
                            <p class="text-muted">Once you have shares in properties, their balance information will appear
                                here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Current Balance</th>
                                        <th>Last Transfer Date</th>
                                        <th>Last Transfer Amount</th>
                                        <th>Notes</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($property_balances as $balance): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($balance['property_name']) ?></strong>
                                                <?php if (!empty($balance['address'])): ?>
                                                    <br><small class="text-muted"><?= esc($balance['address']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-<?= $balance['remaining_balance'] > 0 ? 'success' : 'muted' ?> font-weight-bold">
                                                    SAR <?= number_format($balance['remaining_balance'] ?? 0, 2) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($balance['last_transfer_date'])): ?>
                                                    <?= date('M d, Y', strtotime($balance['last_transfer_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No transfers yet</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($balance['last_transfer_amount'])): ?>
                                                    <span class="text-danger font-weight-bold">
                                                        - SAR <?= number_format($balance['last_transfer_amount'], 2) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($balance['last_transfer_notes'])): ?>
                                                    <?= esc($balance['last_transfer_notes']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($balance['transfer_count']) && $balance['transfer_count'] > 0): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                        onclick="showTransferHistory(<?= $balance['property_id'] ?>, '<?= esc($balance['property_name']) ?>')">
                                                        <i class="fas fa-history"></i> History (<?= $balance['transfer_count'] ?>)
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">No transfers</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-calculator"></i>
                                    <strong>Balance Calculation:</strong>
                                    Property Balance = (Total Income - Management Fees - Total Expenses) - Total Transfers
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
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
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
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

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add Income Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="incomeForm" method="POST" action="<?= site_url('landlord/income-payment/store') ?>"
                enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_property" class="form-label">Property *</label>
                                <select class="form-control" id="income_property" name="property_id" required
                                    onchange="loadIncomeUnits()">
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_unit" class="form-label">Unit *</label>
                                <select class="form-control" id="income_unit" name="unit_id" required>
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="income_date" name="date" required
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_amount" class="form-label">Amount (SAR) *</label>
                                <input type="number" class="form-control" id="income_amount" name="amount" required
                                    min="0.01" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="income_source" class="form-label">Income Source *</label>
                        <input type="text" class="form-control" id="income_source" name="source" required
                            placeholder="e.g., Monthly Rent">
                    </div>

                    <div class="mb-3">
                        <label for="income_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="income_description" name="description" rows="3" required
                            placeholder="Detailed description of the income"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_method" class="form-label">Payment Method</label>
                                <select class="form-control" id="income_method" name="method">
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_receipt" class="form-label">Receipt File (PDF Only)</label>
                                <input type="file" class="form-control" id="income_receipt" name="receipt_file"
                                    accept=".pdf">
                                <small class="text-muted">Upload receipt file (PDF only)</small>
                            </div>
                        </div>
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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-minus"></i> Add Expense Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseForm" method="POST" action="<?= site_url('landlord/expense-payment/store') ?>"
                enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_property" class="form-label">Property *</label>
                                <select class="form-control" id="expense_property" name="property_id" required
                                    onchange="loadExpenseUnits()">
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_unit" class="form-label">Unit *</label>
                                <select class="form-control" id="expense_unit" name="unit_id" required>
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="date" required
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_amount" class="form-label">Amount (SAR) *</label>
                                <input type="number" class="form-control" id="expense_amount" name="amount" required
                                    min="0.01" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expense_type" class="form-label">Expense Type *</label>
                        <select class="form-control" id="expense_type" name="expense_type" required>
                            <option value="">Select Expense Type</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="utilities">Utilities</option>
                            <option value="insurance">Insurance</option>
                            <option value="property_tax">Property Tax</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="advertising">Advertising</option>
                            <option value="legal">Legal Fees</option>
                            <option value="management">Management Fees</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="expense_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="expense_description" name="description" rows="3" required
                            placeholder="Detailed description of the expense"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_method" class="form-label">Payment Method</label>
                                <select class="form-control" id="expense_method" name="method">
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_receipt" class="form-label">Receipt File (PDF Only)</label>
                                <input type="file" class="form-control" id="expense_receipt" name="receipt_file"
                                    accept=".pdf">
                                <small class="text-muted">Upload receipt file (PDF only)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="submitExpenseBtn">
                        <i class="fas fa-save"></i> Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced Transfer Receipt Modal -->
<div class="modal fade" id="transferReceiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt"></i> Add Transfer Receipt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferReceiptForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>How it works:</strong> Enter the total amount transferred to ALL shareholders for this
                        property.
                        The system will deduct this amount from the property's remaining balance.
                    </div>

                    <div class="mb-3">
                        <label for="transfer_property_id" class="form-label">Property *</label>
                        <select class="form-control" id="transfer_property_id" name="property_id" required
                            onchange="loadPropertyBalance()">
                            <option value="">Select Property</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $prop): ?>
                                    <option value="<?= $prop['id'] ?>">
                                        <?= esc($prop['property_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>

                        <!-- Property Balance Info Display -->
                        <div id="property_balance_info" class="mt-2" style="display: none;">
                            <div class="alert alert-warning mb-0">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <small class="fw-bold">Available for Transfer:</small>
                                        <div class="h6 mb-0 text-warning" id="max_transfer_amount">SAR 0.00</div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <small class="text-muted" id="shareholders_info">-</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_date" class="form-label">Transfer Date *</label>
                        <input type="date" class="form-control" id="transfer_date" name="transfer_date"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_amount" class="form-label">Total Transfer Amount (SAR) *</label>
                        <div class="input-group">
                            <span class="input-group-text">SAR</span>
                            <input type="number" class="form-control" id="transfer_amount" name="transfer_amount"
                                required min="0.01" step="0.01" placeholder="0.00" onchange="validateTransferAmount()"
                                oninput="validateTransferAmount()">
                        </div>
                        <small class="form-text text-muted" id="transfer_amount_help">
                            Enter the total amount distributed to ALL shareholders for this property
                        </small>
                        <div id="transfer_amount_error" class="invalid-feedback" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="transfer_notes" name="transfer_notes" rows="2"
                            placeholder="Optional notes about this transfer (e.g., 'Q3 profit distribution to all shareholders')"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_receipt_file" class="form-label">Receipt File (PDF Only)</label>
                        <input type="file" class="form-control" id="transfer_receipt_file" name="transfer_receipt_file"
                            accept=".pdf">
                        <small class="text-muted">Upload bank transfer receipt or proof of payment (PDF only)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="transfer_submit_btn">
                        <i class="fas fa-save"></i> Save Transfer Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Viewer Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-pdf"></i> Receipt Viewer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Transfer History Modal -->
<div class="modal fade" id="transferHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalTitle">
                    <i class="fas fa-history"></i> Transfer History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historyLoadingSpinner" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transfer history...</p>
                </div>

                <div id="historyContent">
                    <!-- History content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="downloadHistoryBtn">
                    <i class="fas fa-file-pdf"></i> Download PDF Report
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // FIXED: Define global variables for site URL and CSRF token
    const site_url = '<?= site_url() ?>';
    const csrf_token = '<?= csrf_token() ?>';
    const csrf_hash = '<?= csrf_hash() ?>';

    // Global variables for transfer modal
    let currentPropertyBalance = 0;
    let currentPropertyId = null;

    // FIXED: Force balance refresh after income/expense added
    function refreshRemainingBalances(propertyId) {
        if (!propertyId) return;

        fetch(`<?= site_url('landlord/refresh-property-balance') ?>/${propertyId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`Property ${propertyId} balance refreshed to: ${data.new_balance}`);
                } else {
                    console.error('Failed to refresh balance:', data.message);
                }
            })
            .catch(error => console.error('Error refreshing balance:', error));
    }

    /**
     * Load property balance information when property is selected
     */
    function loadPropertyBalance() {
        const propertyId = document.getElementById('transfer_property_id').value;
        const balanceInfo = document.getElementById('property_balance_info');
        const maxAmountDisplay = document.getElementById('max_transfer_amount');
        const shareholdersInfo = document.getElementById('shareholders_info');
        const transferAmountInput = document.getElementById('transfer_amount');
        const helpText = document.getElementById('transfer_amount_help');

        // Reset state
        currentPropertyBalance = 0;
        currentPropertyId = null;
        transferAmountInput.max = '';

        if (!propertyId) {
            balanceInfo.style.display = 'none';
            helpText.textContent = 'Enter the total amount distributed to ALL shareholders for this property';
            return;
        }

        // Show loading state
        balanceInfo.style.display = 'block';
        maxAmountDisplay.textContent = 'Loading...';
        shareholdersInfo.textContent = 'Loading...';

        // Fetch property balance
        fetch(`<?= site_url('landlord/get-property-remaining-balance') ?>/${propertyId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    currentPropertyBalance = parseFloat(data.remaining_balance || 0);
                    currentPropertyId = propertyId;

                    // Update display
                    maxAmountDisplay.textContent = `SAR ${data.formatted_balance}`;
                    shareholdersInfo.textContent = `${data.shareholders_count} shareholder${data.shareholders_count > 1 ? 's' : ''}`;

                    // Set max attribute for input validation
                    transferAmountInput.max = currentPropertyBalance;

                    // Update help text
                    if (currentPropertyBalance > 0) {
                        helpText.innerHTML = `Maximum transferable amount: <strong>SAR ${data.formatted_balance}</strong>`;
                        helpText.className = 'form-text text-success';
                    } else {
                        helpText.innerHTML = '<strong class="text-warning">No balance available for transfer</strong>';
                        helpText.className = 'form-text text-warning';
                    }

                } else {
                    throw new Error(data.message || 'Failed to load property balance');
                }
            })
            .catch(error => {
                console.error('Error loading property balance:', error);
                maxAmountDisplay.textContent = 'Error loading balance';
                shareholdersInfo.textContent = 'Error';
                helpText.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
                helpText.className = 'form-text text-danger';

                // Show error alert
                showAlert(`Failed to load property balance: ${error.message}`, 'danger');
            });
    }

    /**
     * Validate transfer amount against available balance
     */
    function validateTransferAmount() {
        const transferAmountInput = document.getElementById('transfer_amount');
        const errorDiv = document.getElementById('transfer_amount_error');
        const submitBtn = document.getElementById('transfer_submit_btn');

        const transferAmount = parseFloat(transferAmountInput.value || 0);

        // Clear previous validation
        transferAmountInput.classList.remove('is-invalid');
        errorDiv.style.display = 'none';
        submitBtn.disabled = false;

        // Basic validation
        if (transferAmount <= 0) {
            if (transferAmountInput.value !== '') { // Only show error if user has entered something
                showValidationError('Transfer amount must be greater than 0');
            }
            return false;
        }

        // Check against available balance
        if (currentPropertyBalance > 0 && transferAmount > currentPropertyBalance) {
            showValidationError(`Transfer amount cannot exceed available balance of SAR ${currentPropertyBalance.toFixed(2)}`);
            return false;
        }

        return true;
    }

    /**
     * Show validation error for transfer amount
     */
    function showValidationError(message) {
        const transferAmountInput = document.getElementById('transfer_amount');
        const errorDiv = document.getElementById('transfer_amount_error');
        const submitBtn = document.getElementById('transfer_submit_btn');

        transferAmountInput.classList.add('is-invalid');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        submitBtn.disabled = true;
    }

    // Load units for income form
    function loadIncomeUnits() {
        const propertyId = document.getElementById('income_property').value;
        const unitSelect = document.getElementById('income_unit');

        if (!propertyId) {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            unitSelect.disabled = false;
            return;
        }

        unitSelect.innerHTML = '<option value="">Loading units...</option>';
        unitSelect.disabled = true;

        fetch(`<?= site_url('landlord/get-units-by-property') ?>/${propertyId}`)
            .then(r => r.json())
            .then(data => {
                unitSelect.disabled = false;

                if (!data.success) {
                    unitSelect.innerHTML = `<option value="">${data.message || 'Failed to load units'}</option>`;
                    return;
                }

                const units = Array.isArray(data.units) ? data.units : [];
                unitSelect.innerHTML = '<option value="">Select Unit</option>';

                if (units.length) {
                    units.forEach(u => {
                        const name = u.unit_name || `Unit ${u.id}`;
                        unitSelect.innerHTML += `<option value="${u.id}">${name}</option>`;
                    });
                } else {
                    unitSelect.innerHTML = '<option value="">No units found</option>';
                }
            })
            .catch(() => {
                unitSelect.disabled = false;
                unitSelect.innerHTML = '<option value="">Failed to load units</option>';
            });
    }

    // Load units for expense form
    function loadExpenseUnits() {
        const propertyId = document.getElementById('expense_property').value;
        const unitSelect = document.getElementById('expense_unit');

        if (!propertyId) {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            unitSelect.disabled = false;
            return;
        }

        unitSelect.innerHTML = '<option value="">Loading units...</option>';
        unitSelect.disabled = true;

        fetch(`<?= site_url('landlord/get-units-by-property') ?>/${propertyId}`)
            .then(r => r.json())
            .then(data => {
                unitSelect.disabled = false;

                if (!data.success) {
                    unitSelect.innerHTML = `<option value="">${data.message || 'Failed to load units'}</option>`;
                    return;
                }

                const units = Array.isArray(data.units) ? data.units : [];
                unitSelect.innerHTML = '<option value="">Select Unit</option>';

                if (units.length) {
                    units.forEach(u => {
                        const name = u.unit_name || `Unit ${u.id}`;
                        unitSelect.innerHTML += `<option value="${u.id}">${name}</option>`;
                    });
                } else {
                    unitSelect.innerHTML = '<option value="">No units found</option>';
                }
            })
            .catch(() => {
                unitSelect.disabled = false;
                unitSelect.innerHTML = '<option value="">Failed to load units</option>';
            });
    }

    // Show Transfer Receipt Modal for specific property
    function showTransferModalForProperty(propertyId, propertyName) {
        const modal = new bootstrap.Modal(document.getElementById('transferReceiptModal'));
        const form = document.getElementById('transferReceiptForm');
        const propertySelect = document.getElementById('transfer_property_id');

        // Reset form and pre-select property
        form.reset();
        document.getElementById('transfer_date').value = new Date().toISOString().split('T')[0];

        // Set the property selection
        propertySelect.value = propertyId;

        // Trigger balance loading
        loadPropertyBalance();

        modal.show();
    }

    // COMPLETE: Show transfer history function with correct field mapping
function showTransferHistory(propertyId, propertyName) {
    const modal = new bootstrap.Modal(document.getElementById('transferHistoryModal'));
    const modalTitle = document.getElementById('historyModalTitle');
    const loadingSpinner = document.getElementById('historyLoadingSpinner');
    const historyContent = document.getElementById('historyContent');
    const downloadBtn = document.getElementById('downloadHistoryBtn');

    // Set modal title
    modalTitle.innerHTML = `<i class="fas fa-history"></i> Transfer History - ${propertyName}`;

    // Show loading spinner
    loadingSpinner.style.display = 'block';
    historyContent.innerHTML = '';

    // Reset download button
    downloadBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Download PDF Report';
    downloadBtn.disabled = false;

    // Set up download button click handler
    downloadBtn.onclick = function () {
        downloadTransferHistoryPDF(propertyId, propertyName);
    };

    // Show modal
    modal.show();

    // Fetch transfer history
    fetch('<?= site_url('landlord/get-transfer-history') ?>/' + propertyId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        loadingSpinner.style.display = 'none';

        if (data.success) {
            if (data.transfers && data.transfers.length > 0) {
                displayTransferHistory(data.transfers, data.property_info || { property_name: propertyName });
            } else {
                historyContent.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h5>No Transfer History</h5>
                        <p>No transfer receipts have been recorded for this property yet.</p>
                        <p class="mb-0">Use the "Add Transfer Receipt" button in the payments page to record transfers.</p>
                    </div>
                `;
            }
        } else {
            throw new Error(data.message || 'Failed to load transfer history');
        }
    })
    .catch(error => {
        loadingSpinner.style.display = 'none';
        console.error('Error loading transfer history:', error);
        historyContent.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h5>Error Loading History</h5>
                <p>Failed to load transfer history: ${error.message}</p>
                <button class="btn btn-primary btn-sm" onclick="showTransferHistory(${propertyId}, '${propertyName}')">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
    });
}

// COMPLETE: Display transfer history with correct field mapping for transfer_receipts table
function displayTransferHistory(transfers, propertyInfo) {
    const historyContent = document.getElementById('historyContent');

    let historyHtml = `
        <div class="mb-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-building"></i> ${propertyInfo.property_name}</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="card-text mb-1">
                                <strong>Total Transfer Records:</strong> ${transfers.length} record${transfers.length > 1 ? 's' : ''}<br>
                                <strong>Date Range:</strong> ${transfers.length > 0 ? 
                                    formatDate(transfers[transfers.length - 1].transfer_date) + ' to ' + formatDate(transfers[0].transfer_date) 
                                    : 'N/A'}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="card-text mb-1">
                                <strong>Total Transferred:</strong> <span class="text-danger">SAR ${numberFormat(transfers.reduce((sum, t) => sum + parseFloat(t.transfer_amount || 0), 0))}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Transfer Date</th>
                        <th style="width: 15%;">Amount (SAR)</th>
                        <th style="width: 30%;">Notes</th>
                        <th style="width: 15%;">Receipt</th>
                        <th style="width: 20%;">Created</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (transfers && transfers.length > 0) {
        transfers.forEach((transfer, index) => {
            const transferAmount = parseFloat(transfer.transfer_amount || 0);

            historyHtml += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${formatDate(transfer.transfer_date)}</td>
                    <td class="text-danger font-weight-bold">- SAR ${numberFormat(transferAmount)}</td>
                    <td>${transfer.notes || '<span class="text-muted">No notes</span>'}</td>
                    <td class="text-center">
                        ${transfer.receipt_file ?
    `<a href="https://www.tab3ni.online/landlord/landlord/download-transfer-receipt/${transfer.receipt_file}"
                               class="btn btn-sm btn-outline-primary" target="_blank" title="Download Receipt">
                                <i class="fas fa-download"></i> View
                             </a>` :
                            '<span class="text-muted">No receipt</span>'
                        }
                    </td>
                    <td>${formatDateTime(transfer.created_at)}</td>
                </tr>
            `;
        });

    } else {
        historyHtml += `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                    No transfer receipts found for this property
                </td>
            </tr>
        `;
    }

    historyHtml += `
                </tbody>
            </table>
        </div>
    `;

    // Add explanation
    if (transfers && transfers.length > 0) {
        historyHtml += `
            <div class="mt-3">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> About Transfer Receipts:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Transfer Receipts:</strong> Records of money transferred from property profits to shareholders</li>
                        <li><strong>Amount:</strong> Total amount distributed to all shareholders for the property</li>
                        <li><strong>Notes:</strong> Optional description of the transfer purpose or details</li>
                        <li><strong>Receipt:</strong> Optional uploaded receipt or proof of transfer</li>
                    </ul>
                </div>
            </div>
        `;
    }

    historyContent.innerHTML = historyHtml;
}

    // COMPLETE: Download transfer history PDF with proper error handling
    function downloadTransferHistoryPDF(propertyId, propertyName) {
        const downloadBtn = document.getElementById('downloadHistoryBtn');
        const originalText = downloadBtn.innerHTML;

        // Show loading state
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        downloadBtn.disabled = true;

        try {
            // Create a form and submit it to trigger PDF download
            const form = document.createElement('form');
            form.method = 'POST';
            // FIXED: Use the correct full URL for localhost
            form.action = 'https://www.tab3ni.online/landlord/landlord/download-transfer-history-pdf';
            form.target = '_blank'; // Open in new tab

            // Add CSRF token
            const csrfTokenElement = document.querySelector('input[name="csrf_test_name"]');
            if (csrfTokenElement) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_test_name';
                csrfInput.value = csrfTokenElement.value;
                form.appendChild(csrfInput);
            }

            // Add property ID
            const propertyInput = document.createElement('input');
            propertyInput.type = 'hidden';
            propertyInput.name = 'property_id';
            propertyInput.value = propertyId;
            form.appendChild(propertyInput);

            // Add property name
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'property_name';
            nameInput.value = propertyName;
            form.appendChild(nameInput);

            // Submit form
            document.body.appendChild(form);
            form.submit();

            // Clean up form
            setTimeout(() => {
                if (form.parentNode) {
                    document.body.removeChild(form);
                }
            }, 1000);

            // Show success message
            showAlert('PDF download started. Check your Downloads folder.', 'success', true);

        } catch (error) {
            console.error('PDF download error:', error);
            showAlert('Error generating PDF. Please try again.', 'danger', true);
        } finally {
            // Reset button after a delay
            setTimeout(() => {
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            }, 2000);
        }
    }

    // Helper function to show Transfer Receipt Modal
    function showTransferReceiptModal() {
        const modal = new bootstrap.Modal(document.getElementById('transferReceiptModal'));
        const form = document.getElementById('transferReceiptForm');

        // Reset form
        form.reset();
        document.getElementById('transfer_date').value = new Date().toISOString().split('T')[0];

        // Hide balance info initially
        document.getElementById('property_balance_info').style.display = 'none';

        modal.show();
    }

    // Helper functions for formatting
    function numberFormat(number, decimals = 2) {
        if (number === null || number === undefined || isNaN(number)) {
            return '0.00';
        }
        return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }

    // Enhanced alert function
    function showAlert(message, type = 'info', autoClose = true) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert.alert-floating');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
        alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

        const iconMap = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };

        alertDiv.innerHTML = `
        <i class="fas fa-${iconMap[type] || 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        document.body.appendChild(alertDiv);

        if (autoClose) {
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }

    // Receipt viewing function
    function viewReceipt(filename) {
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        const content = document.getElementById('receiptContent');

        if (!filename) {
            content.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h5>No Receipt Available</h5>
                <p>No receipt file was uploaded for this transaction.</p>
            </div>
        `;
            modal.show();
            return;
        }

        const receiptViewUrl = `<?= site_url('landlord/view-receipt-file/') ?>${filename}`;
        const receiptDownloadUrl = `<?= site_url('landlord/download-receipt/') ?>${filename}`;

        content.innerHTML = `
        <div class="text-center">
            <div class="mb-3">
                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                <h5>Receipt: ${filename}</h5>
            </div>
            
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
                    <i class="fas fa-download"></i> Download PDF
                </a>
                <a href="${receiptViewUrl}" 
                   class="btn btn-outline-primary" 
                   target="_blank">
                    <i class="fas fa-external-link-alt"></i> Open in New Window
                </a>
            </div>
        </div>
    `;

        modal.show();
    }

    function handlePdfLoad() {
        console.log('PDF loaded successfully');
    }

    function handlePdfError() {
        const container = document.getElementById('pdfContainer');
        if (container) {
            container.innerHTML = `
            <div class="alert alert-danger text-center p-4">
                <i class="fas fa-times-circle fa-3x mb-3"></i>
                <h5>Cannot Display PDF File</h5>
                <p>Unable to display the PDF file in the browser.</p>
                <p class="mb-0">Please use the download button to view the file.</p>
            </div>
        `;
        }
    }

    /**
     * Enhanced transfer receipt form submission
     */
    document.addEventListener('DOMContentLoaded', function () {
        const transferForm = document.getElementById('transferReceiptForm');

        if (transferForm) {
            transferForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Final validation
                if (!validateTransferAmount()) {
                    showAlert('Please correct the transfer amount before submitting.', 'danger');
                    return;
                }

                const propertyId = document.getElementById('transfer_property_id').value;
                if (!propertyId) {
                    showAlert('Please select a property.', 'danger');
                    return;
                }

                const transferAmount = parseFloat(document.getElementById('transfer_amount').value || 0);
                if (currentPropertyBalance > 0 && transferAmount > currentPropertyBalance) {
                    showAlert(`Transfer amount (SAR ${transferAmount.toFixed(2)}) cannot exceed available balance (SAR ${currentPropertyBalance.toFixed(2)})`, 'danger');
                    return;
                }

                const submitBtn = document.getElementById('transfer_submit_btn');
                const originalText = submitBtn.innerHTML;

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;

                const formData = new FormData(this);

                fetch('<?= site_url('landlord/process-transfer-receipt') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('transferReceiptModal'));
                            modal.hide();

                            // Show success message
                            showAlert(data.message, 'success', false);

                            // Reload page after short delay to show updated balances
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showAlert(`Error: ${data.message}`, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred while processing the transfer receipt.', 'danger');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }

        // FIXED: Add balance refresh for income form
        const incomeForm = document.getElementById('incomeForm');
        if (incomeForm) {
            incomeForm.addEventListener('submit', function () {
                const propertyId = document.getElementById('income_property').value;
                setTimeout(() => {
                    refreshRemainingBalances(propertyId);
                }, 1000);
            });
        }

        // FIXED: Add balance refresh for expense form
        const expenseForm = document.getElementById('expenseForm');
        if (expenseForm) {
            expenseForm.addEventListener('submit', function () {
                const propertyId = document.getElementById('expense_property').value;
                setTimeout(() => {
                    refreshRemainingBalances(propertyId);
                }, 1000);
            });
        }
    });

    // Validate transfer amount
    function validateTransferAmount() {
        const transferAmount = parseFloat(document.getElementById('transfer_amount').value || 0);

        if (transferAmount <= 0) {
            showAlert('Transfer amount must be greater than 0', 'danger');
            document.getElementById('transfer_amount').value = '';
            return false;
        }

        return true;
    }

    /**
     * Reset form when modal is closed
     */
    document.getElementById('transferReceiptModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('transferReceiptForm').reset();
        document.getElementById('property_balance_info').style.display = 'none';
        document.getElementById('transfer_amount').classList.remove('is-invalid');
        document.getElementById('transfer_amount_error').style.display = 'none';
        document.getElementById('transfer_submit_btn').disabled = false;
        currentPropertyBalance = 0;
        currentPropertyId = null;

        // Reset help text
        const helpText = document.getElementById('transfer_amount_help');
        helpText.textContent = 'Enter the total amount distributed to ALL shareholders for this property';
        helpText.className = 'form-text text-muted';
    });
</script>

<style>
    .alert-success .h6 {
        color: #155724 !important;
    }

    .input-group-text {
        background-color: #e9ecef;
        border-color: #ced4da;
    }

    #property_balance_info .alert {
        border: 1px solid #ffeaa7;
        background-color: #fffbf0;
    }

    #property_balance_info .alert-warning {
        border: 1px solid #ffeaa7;
        background-color: #fffbf0;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-warning .text-warning {
        color: #f6c23e !important;
    }

    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease;
    }

    .alert-floating {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<?= $this->endSection() ?>