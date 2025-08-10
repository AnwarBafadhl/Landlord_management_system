<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Payment History<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Payment History
        </h1>
        <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Make Payment
        </a>
    </div>

    <!-- Filter Options -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="paid" <?= $current_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="pending" <?= $current_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="overdue" <?= $current_status === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">All Years</option>
                                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="<?= site_url('tenant/payments') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Payment Records
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($payments)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Due Date</th>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Method</th>
                                        <th>Transaction ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($payment['due_date'])) ?></td>
                                            <td>
                                                <?= $payment['payment_date'] ? date('M d, Y', strtotime($payment['payment_date'])) : '-' ?>
                                            </td>
                                            <td>
                                                <strong>$<?= number_format($payment['amount'], 2) ?></strong>
                                                <?php if ($payment['late_fee'] > 0): ?>
                                                    <br><small class="text-danger">+ $<?= number_format($payment['late_fee'], 2) ?> late fee</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= $payment['payment_method'] ? ucfirst(str_replace('_', ' ', $payment['payment_method'])) : '-' ?></td>
                                            <td><?= $payment['transaction_id'] ?? '-' ?></td>
                                            <td>
                                                <?php if ($payment['status'] === 'paid'): ?>
                                                    <a href="<?= site_url('tenant/payments/receipt/' . $payment['id']) ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Receipt
                                                    </a>
                                                <?php elseif (in_array($payment['status'], ['pending', 'overdue'])): ?>
                                                    <a href="<?= site_url('tenant/payments/make?payment_id=' . $payment['id']) ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card"></i> Pay Now
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>$<?= number_format(array_sum(array_column(array_filter($payments, function($p) { return $p['status'] === 'paid'; }), 'amount')), 2) ?></h4>
                                        <p class="mb-0">Total Paid</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>$<?= number_format(array_sum(array_column(array_filter($payments, function($p) { return $p['status'] === 'pending'; }), 'amount')), 2) ?></h4>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h4>$<?= number_format(array_sum(array_column(array_filter($payments, function($p) { return $p['status'] === 'overdue'; }), 'amount')), 2) ?></h4>
                                        <p class="mb-0">Overdue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                            <h4>No Payment Records Found</h4>
                            <p class="text-muted">You don't have any payment records yet.</p>
                            <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Make a Payment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>