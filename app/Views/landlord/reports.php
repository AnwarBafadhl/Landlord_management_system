<?= $this->extend('layouts/landlord') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line"></i> Reports & Analytics
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button class="btn btn-primary" onclick="showOwnershipReportModal()">
                    <i class="fas fa-users"></i> Ownership
                </button>
                <button class="btn btn-success" onclick="showIncomeExpenseReportModal()">
                    <i class="fas fa-chart-bar"></i> Income & Expenses
                </button>
                <button class="btn btn-warning" onclick="showMaintenanceReportModal()">
                    <i class="fas fa-tools"></i> Maintenance
                </button>
                <button class="btn btn-info" onclick="showMonthlyReportModal()">
                    <i class="fas fa-calendar-alt"></i> Monthly
                </button>
            </div>
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

    <!-- Report Type Cards -->
    <div class="row mb-4">
        <!-- Ownership -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ownership Reports
                            </div>
                            <div class="h6 mb-1 font-weight-bold text-gray-800">Property Ownership</div>
                            <div class="small text-muted">Comprehensive shareholder & property details</div>
                        </div>
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="showOwnershipReportModal()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Income/Expense -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Financial Reports
                            </div>
                            <div class="h6 mb-1 font-weight-bold text-gray-800">Income & Expenses</div>
                            <div class="small text-muted">Transactions + profit distribution by ownership</div>
                        </div>
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="showIncomeExpenseReportModal()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Maintenance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Maintenance Reports
                            </div>
                            <div class="h6 mb-1 font-weight-bold text-gray-800">Service Requests</div>
                            <div class="small text-muted">All requests, statuses, assigned staff & costs</div>
                        </div>
                        <i class="fas fa-tools fa-2x text-gray-300"></i>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-warning btn-sm" onclick="showMaintenanceReportModal()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Monthly -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monthly Reports</div>
                            <div class="h6 mb-1 font-weight-bold text-gray-800">Automated Monthly</div>
                            <div class="small text-muted">Incomes, expenses, management fee, transfers, balance</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-info btn-sm" onclick="showMonthlyReportModal()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports - ALWAYS SHOW TABLE -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i> Recent Generated Reports
                <span class="badge badge-secondary ml-2"><?= count($recent_reports ?? []) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_reports)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Report Type</th>
                                <th>Report Name</th>
                                <th>Property</th>
                                <th>Generated Date</th>
                                <th>Download PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reports as $report): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $icons = [
                                            'ownership' => 'fas fa-users text-primary',
                                            'income_expense' => 'fas fa-chart-bar text-success',
                                            'maintenance' => 'fas fa-tools text-warning',
                                            'monthly' => 'fas fa-calendar-alt text-info'
                                        ];
                                        $icon = $icons[$report['report_kind']] ?? 'fas fa-file';
                                        ?>
                                        <i class="<?= $icon ?>"></i>
                                        <?= ucfirst(str_replace('_', ' ', $report['report_kind'])) ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($report['report_name']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= esc($report['property_name']) ?></small>
                                    </td>
                                    <td>
                                        <small><?= date('M j, Y g:i A', strtotime($report['generated_date'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($report['pdf_filename'])): ?>
                                            <a href="<?= site_url('landlord/download-report/' . $report['id']) ?>"
                                                class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                <i class="fas fa-download"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">
                                                <i class="fas fa-times-circle"></i> Not Available
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-file-alt fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Reports Generated Yet</h5>
                    <p class="text-muted mb-4">
                        Use the report generation buttons above to create your first report.
                        All generated reports will appear in this table for easy access and reference.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-3">
                                        <i class="fas fa-lightbulb"></i> Available Report Types:
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2">
                                                    <i class="fas fa-users text-primary"></i>
                                                    <strong>Ownership Reports</strong><br>
                                                    <small class="text-muted">Property details & shareholder info</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-chart-bar text-success"></i>
                                                    <strong>Income & Expense Reports</strong><br>
                                                    <small class="text-muted">Financial transactions & profit
                                                        distribution</small>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2">
                                                    <i class="fas fa-tools text-warning"></i>
                                                    <strong>Maintenance Reports</strong><br>
                                                    <small class="text-muted">Service requests & maintenance costs</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-calendar-alt text-info"></i>
                                                    <strong>Monthly Reports</strong><br>
                                                    <small class="text-muted">Automated monthly summaries</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Summary Reports - ALWAYS SHOW TABLE -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-calendar-check"></i> Latest Monthly Reports
                <span class="badge badge-secondary ml-2"><?= count($monthly_reports_summary ?? []) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($monthly_reports_summary)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="monthlyReportsTable">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Property</th>
                                <th>Total Income</th>
                                <th>Total Expenses</th>
                                <th>Management Fee</th>
                                <th>Net Profit</th>
                                <th>Remaining Balance</th>
                                <th>Auto Generated</th>
                                <th>Email Sent</th>
                                <th>Generated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_reports_summary as $m): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= date('M Y', strtotime($m['report_month'] . '-01')) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($m['property_name']) ?></td>
                                    <td class="text-success font-weight-bold">SAR
                                        <?= number_format((float) $m['total_income'], 2) ?>
                                    </td>
                                    <td class="text-danger">SAR <?= number_format((float) $m['total_expenses'], 2) ?></td>
                                    <td class="text-warning">SAR <?= number_format((float) ($m['management_fee'] ?? 0), 2) ?>
                                    </td>
                                    <td
                                        class="<?= ((float) $m['net_profit']) >= 0 ? 'text-success' : 'text-danger' ?> font-weight-bold">
                                        SAR <?= number_format((float) $m['net_profit'], 2) ?>
                                    </td>
                                    <td
                                        class="<?= ((float) $m['remaining_balance']) >= 0 ? 'text-success' : 'text-danger' ?> font-weight-bold">
                                        SAR <?= number_format((float) $m['remaining_balance'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if ($m['is_automatic']): ?>
                                            <span class="badge badge-success"><i class="fas fa-robot"></i> Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><i class="fas fa-user"></i> Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['email_sent']): ?>
                                            <span class="badge badge-success"><i class="fas fa-envelope-check"></i> Sent</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fas fa-envelope"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= date('M j, Y g:i A', strtotime($m['generated_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Monthly Reports Generated Yet</h5>
                    <p class="text-muted mb-4">
                        Monthly reports are automatically generated on the 1st of each month for all active properties.
                        You can also generate them manually using the Monthly Report button above.
                    </p>

                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="card bg-info text-white border-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-info-circle"></i> About Monthly Reports
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-robot"></i> Automatic Generation:</h6>
                                            <ul class="text-left small">
                                                <li>Generated automatically on the 1st of each month</li>
                                                <li>Covers the previous month's data</li>
                                                <li>Sent via email to property owners</li>
                                                <li>Includes all financial transactions</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-file-alt"></i> Report Contents:</h6>
                                            <ul class="text-left small">
                                                <li>Total income and expenses</li>
                                                <li>Management fee calculations</li>
                                                <li>Net profit and remaining balance</li>
                                                <li>Detailed transaction listings</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-light btn-sm"
                                            onclick="showMonthlyReportModal()">
                                            <i class="fas fa-plus"></i> Generate Manual Monthly Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 1) Ownership Report Modal -->
<div class="modal fade" id="ownershipReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= site_url('landlord/generate-ownership-report') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users"></i> Generate Ownership Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="property_id" class="form-select" required>
                        <option value="all">All Properties</option>
                        <?php foreach (($properties ?? []) as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['property_name']) ?> — <?= esc($p['address']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">You can generate for a specific property or all properties.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Report Name (optional)</label>
                    <input type="text" name="report_name" class="form-control" placeholder="Ownership Report">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="include_units" id="ownUnits">
                    <label class="form-check-label" for="ownUnits">Include Units</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="include_financials" id="ownFinancials">
                    <label class="form-check-label" for="ownFinancials">Include Financial Summary</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Generate PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- 2) Income & Expense Report Modal -->
<div class="modal fade" id="incomeExpenseReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= site_url('landlord/generate-income-expense-report') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-bar"></i> Generate Income & Expense Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="property_id" class="form-select" required>
                        <option value="all">All Properties</option>
                        <?php foreach (($properties ?? []) as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['property_name']) ?> — <?= esc($p['address']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="include_distribution" id="incDist"
                            checked>
                        <label class="form-check-label" for="incDist">Include Profit Distribution by Ownership %</label>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Report Name (optional)</label>
                    <input type="text" name="report_name" class="form-control" placeholder="Income & Expense Report">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-file-pdf"></i> Generate PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- 3) Maintenance Report Modal -->
<div class="modal fade" id="maintenanceReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= site_url('landlord/generate-maintenance-report') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tools"></i> Generate Maintenance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="property_id" class="form-select" required>
                        <option value="all">All Properties</option>
                        <?php foreach (($properties ?? []) as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['property_name']) ?> — <?= esc($p['address']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="mt-3">
                    <label class="form-label">Report Name (optional)</label>
                    <input type="text" name="report_name" class="form-control" placeholder="Maintenance Report">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-file-pdf"></i> Generate PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- 4) Monthly Report Modal (manual generation) -->
<div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= site_url('landlord/generate-monthly-report') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Generate Monthly Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Automatic Generation:</strong> Monthly reports are automatically generated on the
                    <strong>1st of each month</strong> for each property and emailed to the property creator (primary
                    owner).
                    You can also generate them manually for any month.
                </div>
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <select name="property_id" class="form-select" required>
                        <option value="all">All Properties</option>
                        <?php foreach (($properties ?? []) as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['property_name']) ?> — <?= esc($p['address']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Note: Automatic generation creates separate reports for each property.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="<?= date('Y-m') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Report Name (optional)</label>
                    <input type="text" name="report_name" class="form-control"
                        placeholder="Monthly Report - <?= date('Y-m') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info"><i class="fas fa-file-pdf"></i> Generate PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showOwnershipReportModal() {
        new bootstrap.Modal(document.getElementById('ownershipReportModal')).show();
    }

    function showIncomeExpenseReportModal() {
        new bootstrap.Modal(document.getElementById('incomeExpenseReportModal')).show();
    }

    function showMaintenanceReportModal() {
        new bootstrap.Modal(document.getElementById('maintenanceReportModal')).show();
    }

    function showMonthlyReportModal() {
        new bootstrap.Modal(document.getElementById('monthlyReportModal')).show();
    }

    // Enhanced modal auto-close functionality
    function setupModalAutoClose() {
        // Get all forms in modals
        const modalForms = document.querySelectorAll('.modal form');

        modalForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                // Find the submit button and show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

                    // Reset button after delay
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }

                // Close modal after download starts
                const modal = form.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }, 1500); // 1.5 second delay to allow download to start
                }
            });
        });
    }

    // Initialize DataTable when jQuery + DataTables are ready
    (function initMonthlyTableWhenReady() {
        function ready() {
            try {
                if (window.jQuery && $.fn && $.fn.DataTable) {
                    if (document.getElementById('monthlyReportsTable')) {
                        $('#monthlyReportsTable').DataTable({
                            order: [[9, 'desc']],   // "Generated At" column
                            pageLength: 10,
                            responsive: true
                        });
                    }
                } else {
                    // retry shortly in case scripts are still loading
                    setTimeout(ready, 100);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // after DOM is ready, start checking for jQuery/DataTables
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                ready();
                setupModalAutoClose(); // Setup modal auto-close
            });
        } else {
            ready();
            setupModalAutoClose(); // Setup modal auto-close
        }
    })();

    function handleReportFormSubmission(formId, modalId) {
        const form = document.getElementById(formId);
        const modal = document.getElementById(modalId);

        if (form) {
            form.addEventListener('submit', function (e) {
                // Allow the form to submit normally (file download)
                // Close the modal after a short delay to allow download to start
                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }, 1000); // 1 second delay
            });
        }
    }

    // Initialize all report form handlers when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        // Handle each report modal
        handleReportFormSubmission('ownershipReportForm', 'ownershipReportModal');
        handleReportFormSubmission('incomeExpenseReportForm', 'incomeExpenseReportModal');
        handleReportFormSubmission('maintenanceReportForm', 'maintenanceReportModal');
        handleReportFormSubmission('monthlyReportForm', 'monthlyReportModal');
    });

    // Alternative method: Add IDs to your forms and use this approach
    function autoCloseModalOnSubmit() {
        // Get all forms in modals
        const modalForms = document.querySelectorAll('.modal form');

        modalForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                const modal = form.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }, 1000);
                }
            });
        });
    }

    // Call this function when page loads
    document.addEventListener('DOMContentLoaded', autoCloseModalOnSubmit);
</script>

<?= $this->endSection() ?>