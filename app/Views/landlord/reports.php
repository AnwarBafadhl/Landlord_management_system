<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>
Reports & Analytics
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Reports & Analytics
        </h1>
        <div class="d-none d-lg-inline-block">
            <a href="<?= site_url('landlord/help') ?>" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-question-circle fa-sm text-white-50"></i> Help Guide
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Report Generation Cards -->
    <div class="row">
        <!-- Ownership Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-users"></i> Property Ownership Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate comprehensive ownership reports with detailed shareholder information,
                        investment calculations, and portfolio analysis based on your share-based property system.
                    </p>

                    <form method="post" action="<?= site_url('landlord/reports/generate-ownership-pdf') ?>"
                        target="_blank">
                        <?= csrf_field() ?>

                        <!-- Report Name -->
                        <div class="mb-3">
                            <label for="report_name" class="form-label">Report Name</label>
                            <input type="text" class="form-control" id="report_name" name="report_name"
                                value="Property Ownership Report - <?= date('M Y') ?>" required>
                        </div>

                        <!-- Property Selection -->
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Property Selection</label>
                            <select class="form-control" id="property_id" name="property_id" required>
                                <option value="all">📊 All My Properties (Complete Report for Each Property)</option>
                                <?php if (!empty($properties)): ?>
                                    <optgroup label="Individual Properties:">
                                        <?php foreach ($properties as $property): ?>
                                            <option value="<?= $property['id'] ?>">
                                                🏢 <?= esc($property['property_name']) ?>
                                                (<?= number_format($property['total_shares']) ?> shares)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">
                                Select "All My Properties" to generate a comprehensive report with complete information
                                for each property listed separately.
                            </small>
                        </div>

                        <?php
                        // Optional: Add preview text to show what will be included
                        ?>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> What's Included in Your Report:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>For Each Property:</strong>
                                    <ul class="mb-2">
                                        <li>Property Information (Value, Address, Shares)</li>
                                        <li>Management Details</li>
                                        <li>Complete Shareholders List</li>
                                        <li>Investment Calculations</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <strong>Additional Features:</strong>
                                    <ul class="mb-2">
                                        <li>Portfolio Summary (for multiple properties)</li>
                                        <li>Agreement Conditions</li>
                                        <li>Professional PDF formatting</li>
                                        <li>Audit trail and timestamps</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Report Options -->
                        <div class="mb-3">
                            <label class="form-label">Report Sections to Include</label>
                            <div class="card bg-light p-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="include_owner_details"
                                        name="include_owner_details" value="1" checked>
                                    <label class="form-check-label" for="include_owner_details">
                                        <strong>Shareholder Information</strong>
                                        <small class="d-block text-muted">Names, emails, contact details, and
                                            status</small>
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="include_percentages"
                                        name="include_percentages" value="1" checked>
                                    <label class="form-check-label" for="include_percentages">
                                        <strong>Financial Analysis</strong>
                                        <small class="d-block text-muted">Ownership percentages, investment values, and
                                            calculations</small>
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="include_management"
                                        name="include_management" value="1" checked>
                                    <label class="form-check-label" for="include_management">
                                        <strong>Management Details</strong>
                                        <small class="d-block text-muted">Management company information and fee
                                            structure</small>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_conditions"
                                        name="include_conditions" value="1" checked>
                                    <label class="form-check-label" for="include_conditions">
                                        <strong>Agreement Conditions</strong>
                                        <small class="d-block text-muted">Standard shareholding agreement terms and
                                            conditions</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="report_notes" class="form-label">Additional Notes <small
                                    class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="report_notes" name="report_notes" rows="3"
                                placeholder="Add any specific notes or context for this report..."></textarea>
                        </div>

                        <!-- Report Preview Info -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Report Features:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Professional PDF format with enhanced styling</li>
                                <li>Portfolio summary for multi-property reports</li>
                                <li>Share allocation charts and visual indicators</li>
                                <li>Investment calculations and ROI analysis</li>
                                <li>Compliance-ready documentation</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-file-pdf"></i> Generate Enhanced Ownership Report
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Shareholder Income Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-dollar-sign"></i> Shareholder Income Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate detailed income and expense reports with shareholder-specific profit calculations and
                        distributions based on their ownership percentage.
                    </p>

                    <form method="POST" action="<?= site_url('landlord/reports/generate-income-pdf') ?>">
                        <?= csrf_field() ?>

                        <!-- Property Selection -->
                        <div class="form-group">
                            <label for="income_property">Select Property</label>
                            <select class="form-control" id="income_property" name="property_id" required>
                                <option value="">Choose Property</option>
                                <?php if (!empty($properties) && is_array($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?? '' ?>">
                                            <?= esc($property['property_name'] ?? 'Property') ?>
                                            - SAR <?= number_format($property['property_value'] ?? 0, 0) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="income_start_date">Start Date</label>
                                    <input type="date" class="form-control" id="income_start_date" name="start_date"
                                        value="<?= date('Y-m-01') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="income_end_date">End Date</label>
                                    <input type="date" class="form-control" id="income_end_date" name="end_date"
                                        value="<?= date('Y-m-t') ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Income Details -->
                        <div class="form-group">
                            <label for="total_income">Total Property Income (SAR)</label>
                            <input type="number" class="form-control" id="total_income" name="total_income" min="0"
                                step="0.01" placeholder="Enter total income for the period">
                        </div>

                        <!-- Expenses Details -->
                        <div class="form-group">
                            <label for="total_expenses">Total Expenses (SAR)</label>
                            <input type="number" class="form-control" id="total_expenses" name="total_expenses" min="0"
                                step="0.01" placeholder="Enter total expenses for the period">
                        </div>

                        <!-- Report Options -->
                        <div class="form-group">
                            <label>Include in Report</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_income_breakdown"
                                    name="include_income_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_income_breakdown">
                                    Income Breakdown by Source
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_expense_breakdown"
                                    name="include_expense_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_expense_breakdown">
                                    Expense Breakdown by Category
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_management_fees"
                                    name="include_management_fees" value="1" checked>
                                <label class="form-check-label" for="include_management_fees">
                                    Management Fees Calculation
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_shareholder_distributions"
                                    name="include_shareholder_distributions" value="1" checked>
                                <label class="form-check-label" for="include_shareholder_distributions">
                                    Individual Shareholder Distributions
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-file-pdf"></i> Generate Income Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Performance Report Card -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-info">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-chart-line"></i> Share Performance Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate a comprehensive analysis of share performance, contribution tracking, and projected
                        returns for shareholders.
                    </p>

                    <form method="POST" action="<?= site_url('landlord/reports/generate-performance-pdf') ?>">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="performance_property">Select Property</label>
                                    <select class="form-control" id="performance_property" name="property_id" required>
                                        <option value="">Choose Property</option>
                                        <?php if (!empty($properties) && is_array($properties)): ?>
                                            <?php foreach ($properties as $property): ?>
                                                <option value="<?= $property['id'] ?? '' ?>">
                                                    <?= esc($property['property_name'] ?? 'Property') ?>
                                                    (<?= number_format($property['total_shares'] ?? 0) ?> shares)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="analysis_period">Analysis Period</label>
                                    <select class="form-control" id="analysis_period" name="analysis_period" required>
                                        <option value="3">Last 3 Months</option>
                                        <option value="6" selected>Last 6 Months</option>
                                        <option value="12">Last 12 Months</option>
                                        <option value="custom">Custom Period</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="report_type">Report Type</label>
                                    <select class="form-control" id="report_type" name="report_type" required>
                                        <option value="summary">Summary Report</option>
                                        <option value="detailed">Detailed Analysis</option>
                                        <option value="projections">Future Projections</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="customDateRange" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_start_date">Custom Start Date</label>
                                    <input type="date" class="form-control" id="custom_start_date"
                                        name="custom_start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_end_date">Custom End Date</label>
                                    <input type="date" class="form-control" id="custom_end_date" name="custom_end_date">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-chart-line"></i> Generate Performance Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Reports History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-history"></i> Recent Reports
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($generated_reports) && is_array($generated_reports)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Report Details</th>
                                <th>Property</th>
                                <th>Generated</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($generated_reports as $report): ?>
                                <tr>
                                    <td>
                                        <div class="report-info">
                                            <h6 class="mb-1">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                <?= esc($report['name'] ?? 'Report') ?>
                                            </h6>
                                            <span class="badge badge-<?=
                                                ($report['type'] ?? '') === 'ownership' ? 'primary' :
                                                (($report['type'] ?? '') === 'income' ? 'success' : 'info')
                                                ?>">
                                                <?= ucfirst($report['type'] ?? 'Unknown') ?> Report
                                            </span>
                                            <?php if (isset($report['period'])): ?>
                                                <small class="text-muted d-block">
                                                    Period: <?= esc($report['period']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (isset($report['property_name'])): ?>
                                            <strong><?= esc($report['property_name']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">All Properties</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-dark">
                                            <?= date('M d, Y', strtotime($report['generated_at'] ?? date('Y-m-d H:i:s'))) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($report['generated_at'] ?? date('Y-m-d H:i:s'))) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="text-dark">
                                            <i class="fas fa-user fa-sm text-muted"></i>
                                            <?= esc($report['generated_by'] ?? 'Unknown') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= site_url('landlord/reports/download/' . ($report['id'] ?? 0)) ?>"
                                                class="btn btn-primary btn-sm" title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-info btn-sm"
                                                onclick="viewReportDetails(<?= $report['id'] ?? 0 ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="deleteReport(<?= $report['id'] ?? 0 ?>)" title="Delete Report">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (count($generated_reports) >= 10): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Showing last 10 reports. Older reports are automatically archived.
                        </small>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-file-pdf fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Reports Generated Yet</h5>
                    <p class="text-muted mb-4">
                        Use the forms above to generate your first ownership, income, or performance report.
                        All generated reports will appear here for your reference.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-lightbulb"></i> Report Types Available:
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-building text-primary"></i>
                                            <strong>Ownership Reports:</strong> Property details, share structure, and
                                            ownership percentages
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-dollar-sign text-success"></i>
                                            <strong>Income Reports:</strong> Financial performance and shareholder
                                            distributions
                                        </li>
                                        <li>
                                            <i class="fas fa-chart-line text-info"></i>
                                            <strong>Performance Reports:</strong> Share performance analysis and projections
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-pdf"></i> Report Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportDetailsContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Loading report details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide custom date range
    document.getElementById('analysis_period').addEventListener('change', function () {
        const customDateRange = document.getElementById('customDateRange');
        if (this.value === 'custom') {
            customDateRange.style.display = 'block';
            document.getElementById('custom_start_date').required = true;
            document.getElementById('custom_end_date').required = true;
        } else {
            customDateRange.style.display = 'none';
            document.getElementById('custom_start_date').required = false;
            document.getElementById('custom_end_date').required = false;
        }
    });

    // View report details
    function viewReportDetails(reportId) {
        const modal = $('#reportDetailsModal');
        const content = document.getElementById('reportDetailsContent');

        // Show loading state
        content.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="text-muted mt-2">Loading report details...</p>
        </div>
    `;

        modal.modal('show');

        // Fetch report details (you would implement this endpoint)
        fetch(`<?= site_url('landlord/reports/details') ?>/${reportId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = `
                    <div class="report-details">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-file-alt"></i> Report Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td><strong>Name:</strong></td><td>${data.report.name}</td></tr>
                                    <tr><td><strong>Type:</strong></td><td><span class="badge badge-primary">${data.report.type}</span></td></tr>
                                    <tr><td><strong>Generated:</strong></td><td>${data.report.generated_at}</td></tr>
                                    <tr><td><strong>File Size:</strong></td><td>${data.report.file_size || 'N/A'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-building"></i> Property Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td><strong>Property:</strong></td><td>${data.report.property_name || 'All Properties'}</td></tr>
                                    <tr><td><strong>Period:</strong></td><td>${data.report.period || 'N/A'}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="badge badge-success">${data.report.status}</span></td></tr>
                                </table>
                            </div>
                        </div>
                        ${data.report.description ? `<div class="mt-3"><h6>Description:</h6><p class="text-muted">${data.report.description}</p></div>` : ''}
                    </div>
                `;
                } else {
                    content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        <p class="text-muted mt-2">Failed to load report details.</p>
                    </div>
                `;
                }
            })
            .catch(error => {
                content.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    <p class="text-muted mt-2">Error loading report details.</p>
                </div>
            `;
            });
    }

    // Delete report
    function deleteReport(reportId) {
        if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= site_url('landlord/reports/delete') ?>/${reportId}`;

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '<?= csrf_token() ?>';
            csrfField.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfField);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // Form submission handling
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                submitBtn.disabled = true;

                // Re-enable after 10 seconds in case of issues
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });

    // Auto-dismiss alerts
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);
</script>

<style>
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .card-header.bg-primary {
        background-color: #4e73df !important;
    }

    .card-header.bg-success {
        background-color: #1cc88a !important;
    }

    .card-header.bg-info {
        background-color: #36b9cc !important;
    }

    .form-check {
        margin-bottom: 0.5rem;
    }

    .form-check-label {
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .report-info h6 {
        line-height: 1.2;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.775rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #5a5c69;
        background-color: #f8f9fc;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.75em;
    }

    .modal-lg {
        max-width: 800px;
    }

    .text-decoration-none:hover {
        text-decoration: underline !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .fa-4x {
        font-size: 4em;
    }

    .fa-2x {
        font-size: 2em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const propertySelect = document.getElementById('property_id');
        const reportNameInput = document.getElementById('report_name');

        // Update report name when property selection changes
        if (propertySelect && reportNameInput) {
            propertySelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const currentDate = new Date().toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                });

                if (this.value === 'all') {
                    reportNameInput.value = `Portfolio Ownership Report - ${currentDate}`;
                } else {
                    const propertyName = selectedOption.text.split(' (')[0]; // Remove share count
                    reportNameInput.value = `${propertyName} Ownership Report - ${currentDate}`;
                }
            });
        }

        // Form validation
        const ownershipForm = document.querySelector('form[action*="generate-ownership-pdf"]');
        if (ownershipForm) {
            ownershipForm.addEventListener('submit', function (e) {
                const checkboxes = this.querySelectorAll('input[type="checkbox"]');
                const checkedBoxes = this.querySelectorAll('input[type="checkbox"]:checked');

                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one report section to include.');
                    return false;
                }

                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Report...';
                submitBtn.disabled = true;

                // Re-enable button after 10 seconds (in case of errors)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        }

        // Checkbox dependencies
        const includeOwnerDetails = document.getElementById('include_owner_details');
        const includePercentages = document.getElementById('include_percentages');

        if (includeOwnerDetails && includePercentages) {
            includeOwnerDetails.addEventListener('change', function () {
                if (!this.checked) {
                    includePercentages.checked = false;
                }
            });

            includePercentages.addEventListener('change', function () {
                if (this.checked && !includeOwnerDetails.checked) {
                    includeOwnerDetails.checked = true;
                }
            });
        }
    });

    // Preview report features
    function showReportPreview() {
        const modal = `
        <div class="modal fade" id="reportPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Report Preview Features</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-pie text-primary"></i> Portfolio Overview</h6>
                                <ul class="list-unstyled">
                                    <li>• Total investment summary</li>
                                    <li>• Properties count and distribution</li>
                                    <li>• Share allocation charts</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-users text-success"></i> Shareholder Details</h6>
                                <ul class="list-unstyled">
                                    <li>• Complete contact information</li>
                                    <li>• Investment amounts and percentages</li>
                                    <li>• Status and role indicators</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-calculator text-info"></i> Financial Analysis</h6>
                                <ul class="list-unstyled">
                                    <li>• Investment value calculations</li>
                                    <li>• Share distribution analysis</li>
                                    <li>• Available opportunities</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-file-contract text-warning"></i> Legal Compliance</h6>
                                <ul class="list-unstyled">
                                    <li>• Agreement terms and conditions</li>
                                    <li>• Audit trail and timestamps</li>
                                    <li>• Professional formatting</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', modal);
        new bootstrap.Modal(document.getElementById('reportPreviewModal')).show();
    }
</script>

<style>
    /* Enhanced styling for the reports form */
    .form-check .form-check-label {
        cursor: pointer;
        padding-left: 5px;
    }

    .form-check .form-check-label small {
        font-size: 0.8rem;
        line-height: 1.3;
    }

    .card.bg-light {
        border: 1px solid #e3e6f0;
    }

    .alert-info {
        border-left: 4px solid #36b9cc;
    }

    .alert-info ul {
        font-size: 0.9rem;
    }

    .btn-block {
        width: 100%;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Loading animation */
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .fa-spinner.fa-spin {
        animation: spin 1s linear infinite;
    }
</style>

<?php
// 6. Add this CSS to your main stylesheet for better PDF styling when viewed in browser

?>

<style>
    /* Print-specific styles for better PDF generation */
    @media print {
        .property-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
        }

        .portfolio-summary {
            page-break-after: auto;
        }

        .footer {
            page-break-inside: avoid;
        }

        .header {
            page-break-after: avoid;
        }
    }

    /* Responsive table for better mobile viewing */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?= $this->endSection() ?>