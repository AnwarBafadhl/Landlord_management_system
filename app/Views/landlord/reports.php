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
                        <i class="fas fa-building"></i> Property Ownership Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate a comprehensive report containing property information, share structure, owner details, and their ownership percentages based on shares.
                    </p>
                    
                    <form method="POST" action="<?= site_url('landlord/reports/generate-ownership-pdf') ?>">
                        <?= csrf_field() ?>
                        
                        <!-- Property Selection -->
                        <div class="form-group">
                            <label for="ownership_property">Select Property</label>
                            <select class="form-control" id="ownership_property" name="property_id">
                                <option value="">All Properties</option>
                                <?php if (!empty($properties) && is_array($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?? '' ?>">
                                            <?= esc($property['property_name'] ?? 'Property') ?> 
                                            <?php if (isset($property['address'])): ?>
                                                - <?= esc(substr($property['address'], 0, 30)) ?><?= strlen($property['address']) > 30 ? '...' : '' ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Report Options -->
                        <div class="form-group">
                            <label>Include Details</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_property_details" name="include_property_details" value="1" checked>
                                <label class="form-check-label" for="include_property_details">
                                    Property Information (Name, Value, Address, Share Structure)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_owner_details" name="include_owner_details" value="1" checked>
                                <label class="form-check-label" for="include_owner_details">
                                    Owner Information (Names, Emails, Share Counts)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_percentages" name="include_percentages" value="1" checked>
                                <label class="form-check-label" for="include_percentages">
                                    Ownership Percentages and Calculations
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_management" name="include_management" value="1" checked>
                                <label class="form-check-label" for="include_management">
                                    Management Company and Fee Structure
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_conditions" name="include_conditions" value="1" checked>
                                <label class="form-check-label" for="include_conditions">
                                    Shareholders Agreement Conditions
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-file-pdf"></i> Generate Ownership Report
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
                        Generate detailed income and expense reports with shareholder-specific profit calculations and distributions based on their ownership percentage.
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
                            <input type="number" class="form-control" id="total_income" name="total_income" 
                                   min="0" step="0.01" placeholder="Enter total income for the period">
                        </div>

                        <!-- Expenses Details -->
                        <div class="form-group">
                            <label for="total_expenses">Total Expenses (SAR)</label>
                            <input type="number" class="form-control" id="total_expenses" name="total_expenses" 
                                   min="0" step="0.01" placeholder="Enter total expenses for the period">
                        </div>

                        <!-- Report Options -->
                        <div class="form-group">
                            <label>Include in Report</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_income_breakdown" name="include_income_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_income_breakdown">
                                    Income Breakdown by Source
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_expense_breakdown" name="include_expense_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_expense_breakdown">
                                    Expense Breakdown by Category
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_management_fees" name="include_management_fees" value="1" checked>
                                <label class="form-check-label" for="include_management_fees">
                                    Management Fees Calculation
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_shareholder_distributions" name="include_shareholder_distributions" value="1" checked>
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
                        Generate a comprehensive analysis of share performance, contribution tracking, and projected returns for shareholders.
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
                                    <input type="date" class="form-control" id="custom_start_date" name="custom_start_date">
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
                                            <strong>Ownership Reports:</strong> Property details, share structure, and ownership percentages
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-dollar-sign text-success"></i>
                                            <strong>Income Reports:</strong> Financial performance and shareholder distributions
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
document.getElementById('analysis_period').addEventListener('change', function() {
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
    form.addEventListener('submit', function(e) {
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
setTimeout(function() {
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

<?= $this->endSection() ?>