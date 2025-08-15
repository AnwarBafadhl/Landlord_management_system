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
                        <i class="fas fa-building"></i> Ownership Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate a comprehensive report containing property information, owner details, and their ownership percentages.
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
                                            <?= esc($property['name'] ?? $property['property_name'] ?? 'Property') ?> 
                                            <?php if (isset($property['address'])): ?>
                                                - <?= esc($property['address']) ?>
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
                                    Property Information
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_owner_details" name="include_owner_details" value="1" checked>
                                <label class="form-check-label" for="include_owner_details">
                                    Owner Information
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_percentages" name="include_percentages" value="1" checked>
                                <label class="form-check-label" for="include_percentages">
                                    Ownership Percentages
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

        <!-- Owner Income Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-dollar-sign"></i> Owner Income Report
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Generate detailed income and expense reports with owner-specific profit calculations and distributions.
                    </p>
                    
                    <form method="POST" action="<?= site_url('landlord/reports/generate-income-pdf') ?>">
                        <?= csrf_field() ?>
                        
                        <!-- Property Selection -->
                        <div class="form-group">
                            <label for="income_property">Select Property</label>
                            <select class="form-control" id="income_property" name="property_id">
                                <option value="">All Properties</option>
                                <?php if (!empty($properties) && is_array($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?? '' ?>">
                                            <?= esc($property['name'] ?? $property['property_name'] ?? 'Property') ?>
                                            <?php if (isset($property['address'])): ?>
                                                - <?= esc($property['address']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Period Selection -->
                        <div class="form-group">
                            <label for="report_period">Report Period</label>
                            <select class="form-control" id="report_period" name="report_period" required>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Every 3 Months (Quarterly)</option>
                                <option value="semi_annual">Every 6 Months (Semi-Annual)</option>
                                <option value="annual">Annual (Yearly)</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>

                        <!-- Report Options -->
                        <div class="form-group">
                            <label>Include Details</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_income_breakdown" name="include_income_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_income_breakdown">
                                    Income Breakdown
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_expense_breakdown" name="include_expense_breakdown" value="1" checked>
                                <label class="form-check-label" for="include_expense_breakdown">
                                    Expense Breakdown
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_owner_distributions" name="include_owner_distributions" value="1" checked>
                                <label class="form-check-label" for="include_owner_distributions">
                                    Owner Profit Distributions
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

    <!-- Financial Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Income (YTD)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format(($financial_summary['total_income'] ?? 0), 2) ?>
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
                                Total Expenses (YTD)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format(($financial_summary['total_expenses'] ?? 0), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                Net Income (YTD)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format((($financial_summary['total_income'] ?? 0) - ($financial_summary['total_expenses'] ?? 0)), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Properties
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($properties ?? []) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Generated Reports -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Generated Reports
                    </h6>
                    <?php if (!empty($generated_reports)): ?>
                        <span class="badge badge-primary badge-pill"><?= count($generated_reports) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($generated_reports) && is_array($generated_reports)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="15%">Report Type</th>
                                        <th width="35%">Report Name</th>
                                        <th width="20%">Property</th>
                                        <th width="15%">Generated Date</th>
                                        <th width="15%">Generated By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($generated_reports as $report): ?>
                                        <tr>
                                            <td>
                                                <?php if (($report['report_kind'] ?? '') === 'Ownership Report'): ?>
                                                    <span class="badge badge-primary" style="color: black !important;">
    <i class="fas fa-building"></i> Ownership
</span>
                                                <?php elseif (($report['report_kind'] ?? '') === 'Income Report'): ?>
                                                    <span class="badge badge-success" style="color: black !important;">
    <i class="fas fa-dollar-sign"></i> Income
</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">
                                                        <?= esc($report['report_kind'] ?? 'Unknown') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-dark">
                                                    <?= esc($report['report_name'] ?? 'Unknown Report') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">
                                                    <i class="fas fa-home fa-sm"></i> 
                                                    <?= esc($report['property_name'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-dark">
                                                    <?= date('M d, Y', strtotime($report['generated_date'] ?? date('Y-m-d'))) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('g:i A', strtotime($report['generated_date'] ?? date('Y-m-d H:i:s'))) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="text-dark">
                                                    <i class="fas fa-user fa-sm text-muted"></i>
                                                    <?= esc($report['generated_by'] ?? 'Unknown') ?>
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
                                Use the forms above to generate your first ownership or income report. 
                                All generated reports will appear here for your reference.
                            </p>
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="alert alert-info">
                                        <strong><i class="fas fa-lightbulb"></i> Quick Start:</strong>
                                        <ul class="mb-0 mt-2 text-left">
                                            <li>Generate an <strong>Ownership Report</strong> to see property details and owner percentages</li>
                                            <li>Create an <strong>Income Report</strong> to track earnings and expenses over time</li>
                                            <li>All reports are saved as PDF files for easy sharing and record keeping</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Set default dates when page loads
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput) {
        startDateInput.value = firstDayOfMonth.toISOString().split('T')[0];
    }
    if (endDateInput) {
        endDateInput.value = lastDayOfMonth.toISOString().split('T')[0];
    }
});

// Show loading when generating reports
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            submitBtn.disabled = true;
            
            // Re-enable after 10 seconds as fallback
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        }
    });
});
</script>

<?= $this->endSection() ?>