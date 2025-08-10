<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Reports & Analytics<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Reports & Analytics
        </h1>
        <div>
            <button class="btn btn-primary" onclick="generateCustomReport()">
                <i class="fas fa-file-export"></i> Custom Report
            </button>
        </div>
    </div>

    <!-- Report Options -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 report-card" onclick="generateReport('income')">
                <div class="card-body text-center">
                    <div class="text-primary">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mt-2">
                        Income Report
                    </div>
                    <div class="text-sm text-muted">
                        Monthly & yearly income analysis
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 report-card" onclick="generateReport('occupancy')">
                <div class="card-body text-center">
                    <div class="text-success">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mt-2">
                        Occupancy Report
                    </div>
                    <div class="text-sm text-muted">
                        Property utilization & vacancy analysis
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 report-card" onclick="generateReport('maintenance')">
                <div class="card-body text-center">
                    <div class="text-warning">
                        <i class="fas fa-tools fa-2x"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-warning text-uppercase mt-2">
                        Maintenance Report
                    </div>
                    <div class="text-sm text-muted">
                        Maintenance costs & trends
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 report-card" onclick="generateReport('tenant')">
                <div class="card-body text-center">
                    <div class="text-info">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-info text-uppercase mt-2">
                        Tenant Report
                    </div>
                    <div class="text-sm text-muted">
                        Tenant demographics & lease analysis
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Income Trends (Last 12 Months)</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-line"></i> View
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="changeChartView('income', 'monthly')">Monthly</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeChartView('income', 'quarterly')">Quarterly</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeChartView('income', 'yearly')">Yearly</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Property Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="propertyChart" width="100%" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Performance Indicators</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="metric-item">
                                <h4 class="text-success">
                                    <?php 
                                    $expected = $report_data['expected_income'] ?? 0;
                                    $collected = $report_data['collected_income'] ?? 0;
                                    $rate = $expected > 0 ? round(($collected / $expected) * 100, 1) : 0;
                                    echo $rate . '%';
                                    ?>
                                </h4>
                                <span class="text-muted">Collection Rate</span>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-success" style="width: <?= $rate ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="metric-item">
                                <h4 class="text-info">
                                    <?php 
                                    $occupancy = $report_data['occupancy_rate'] ?? 0;
                                    echo round($occupancy, 1) . '%';
                                    ?>
                                </h4>
                                <span class="text-muted">Occupancy Rate</span>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-info" style="width: <?= $occupancy ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="metric-item">
                                <h4 class="text-warning">$<?= number_format($report_data['avg_maintenance_cost'] ?? 0, 0) ?></h4>
                                <span class="text-muted">Avg Monthly Maintenance</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="metric-item">
                                <h4 class="text-primary"><?= round($report_data['avg_lease_duration'] ?? 0, 1) ?></h4>
                                <span class="text-muted">Avg Lease Duration (years)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Alerts & Notifications</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($report_data['alerts'])): ?>
                            <?php foreach (array_slice($report_data['alerts'], 0, 5) as $alert): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            <i class="fas fa-<?= $alert['icon'] ?> text-<?= $alert['type'] ?>"></i>
                                            <?= esc($alert['title']) ?>
                                        </div>
                                        <small class="text-muted"><?= esc($alert['message']) ?></small>
                                    </div>
                                    <span class="badge bg-<?= $alert['type'] ?> rounded-pill">
                                        <?= date('M d', strtotime($alert['date'])) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                <p>No recent alerts</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Summary - Current Month</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h3 class="text-success">$<?= number_format($financial_summary['total_income'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Total Income</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 
                                <?= number_format(($financial_summary['income_growth'] ?? 0), 1) ?>% vs last month
                            </small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-danger">$<?= number_format($financial_summary['total_expenses'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Total Expenses</p>
                            <small class="text-danger">
                                <i class="fas fa-arrow-up"></i> 
                                <?= number_format(($financial_summary['expense_growth'] ?? 0), 1) ?>% vs last month
                            </small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-primary">$<?= number_format($financial_summary['net_income'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Net Income</p>
                            <small class="text-primary">
                                <i class="fas fa-<?= ($financial_summary['net_growth'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i> 
                                <?= number_format(abs($financial_summary['net_growth'] ?? 0), 1) ?>% vs last month
                            </small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-info"><?= number_format($financial_summary['profit_margin'] ?? 0, 1) ?>%</h3>
                            <p class="text-muted mb-0">Profit Margin</p>
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i> 
                                Industry avg: 15-25%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Costs Analysis -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Maintenance Costs by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="maintenanceChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Maintenance Issues</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($maintenance_summary)): ?>
                        <?php foreach ($maintenance_summary as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong><?= esc($item['category']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= $item['count'] ?> requests</small>
                                </div>
                                <div class="text-right">
                                    <strong class="text-danger">$<?= number_format($item['total_cost'], 0) ?></strong>
                                    <br>
                                    <small class="text-muted">Avg: $<?= number_format($item['avg_cost'], 0) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-tools fa-2x mb-2"></i>
                            <p>No maintenance data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Generated Reports</h6>
            <button class="btn btn-sm btn-success" onclick="showScheduleReportModal()">
                <i class="fas fa-calendar-plus"></i> Schedule Report
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th>Generated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($generated_reports)): ?>
                            <?php foreach ($generated_reports as $report): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($report['name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($report['description'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= ucfirst($report['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M Y', strtotime($report['period_start'])) ?> - 
                                        <?= date('M Y', strtotime($report['period_end'])) ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y H:i', strtotime($report['generated_at'])) ?>
                                        <br>
                                        <small class="text-muted">by <?= esc($report['generated_by']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $report['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($report['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($report['status'] === 'completed'): ?>
                                                <a href="<?= site_url('landlord/reports/download/' . $report['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewReport(<?= $report['id'] ?>)" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="regenerateReport(<?= $report['id'] ?>)" title="Regenerate">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReport(<?= $report['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                                    <p>No reports generated yet</p>
                                    <button class="btn btn-primary" onclick="generateCustomReport()">
                                        Generate Your First Report
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scheduled Reports -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Reports</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($scheduled_reports)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Frequency</th>
                                <th>Next Run</th>
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scheduled_reports as $schedule): ?>
                                <tr>
                                    <td><?= esc($schedule['name']) ?></td>
                                    <td><span class="badge badge-info"><?= ucfirst($schedule['type']) ?></span></td>
                                    <td><?= ucfirst($schedule['frequency']) ?></td>
                                    <td><?= date('M d, Y', strtotime($schedule['next_run'])) ?></td>
                                    <td><?= esc($schedule['recipients']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $schedule['active'] ? 'success' : 'secondary' ?>">
                                            <?= $schedule['active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editSchedule(<?= $schedule['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-<?= $schedule['active'] ? 'warning' : 'success' ?>" 
                                                    onclick="toggleSchedule(<?= $schedule['id'] ?>)" 
                                                    title="<?= $schedule['active'] ? 'Pause' : 'Resume' ?>">
                                                <i class="fas fa-<?= $schedule['active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteSchedule(<?= $schedule['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <p>No scheduled reports</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Custom Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customReportForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="report_name" class="form-label">Report Name *</label>
                            <input type="text" class="form-control" id="report_name" name="report_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="report_type" class="form-label">Report Type *</label>
                            <select class="form-control" id="report_type" name="report_type" required>
                                <option value="">Select Type</option>
                                <option value="income">Income Analysis</option>
                                <option value="occupancy">Occupancy Analysis</option>
                                <option value="maintenance">Maintenance Analysis</option>
                                <option value="tenant">Tenant Analysis</option>
                                <option value="financial">Financial Summary</option>
                                <option value="comprehensive">Comprehensive Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_start" class="form-label">Period Start *</label>
                            <input type="date" class="form-control" id="period_start" name="period_start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="period_end" class="form-label">Period End *</label>
                            <input type="date" class="form-control" id="period_end" name="period_end" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="properties" class="form-label">Properties</label>
                        <select class="form-control" id="properties" name="properties[]" multiple>
                            <option value="">All Properties</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>">
                                        <?= esc($property['property_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl to select multiple properties</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Include Sections</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_summary" name="sections[]" value="summary" checked>
                                    <label class="form-check-label" for="include_summary">Executive Summary</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_income" name="sections[]" value="income" checked>
                                    <label class="form-check-label" for="include_income">Income Details</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_expenses" name="sections[]" value="expenses">
                                    <label class="form-check-label" for="include_expenses">Expense Breakdown</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_occupancy" name="sections[]" value="occupancy">
                                    <label class="form-check-label" for="include_occupancy">Occupancy Analysis</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_maintenance" name="sections[]" value="maintenance">
                                    <label class="form-check-label" for="include_maintenance">Maintenance Summary</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_charts" name="sections[]" value="charts" checked>
                                    <label class="form-check-label" for="include_charts">Charts & Graphs</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="output_format" class="form-label">Output Format</label>
                            <select class="form-control" id="output_format" name="output_format">
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="html">HTML Report</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="delivery_method" class="form-label">Delivery Method</label>
                            <select class="form-control" id="delivery_method" name="delivery_method">
                                <option value="download">Download Now</option>
                                <option value="email">Email to Me</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="report_notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="report_notes" name="notes" rows="3" 
                                  placeholder="Any specific requirements or notes for this report..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-export"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Report Modal -->
<div class="modal fade" id="scheduleReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Recurring Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleReportForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="schedule_name" class="form-label">Schedule Name *</label>
                        <input type="text" class="form-control" id="schedule_name" name="schedule_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="schedule_type" class="form-label">Report Type *</label>
                        <select class="form-control" id="schedule_type" name="report_type" required>
                            <option value="">Select Type</option>
                            <option value="financial">Monthly Financial Summary</option>
                            <option value="occupancy">Occupancy Report</option>
                            <option value="maintenance">Maintenance Summary</option>
                            <option value="comprehensive">Comprehensive Report</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="frequency" class="form-label">Frequency *</label>
                            <select class="form-control" id="frequency" name="frequency" required>
                                <option value="">Select Frequency</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email_recipients" class="form-label">Email Recipients</label>
                        <input type="email" class="form-control" id="email_recipients" name="email_recipients" 
                               placeholder="Enter email addresses separated by commas">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active_schedule" name="active" checked>
                            <label class="form-check-label" for="active_schedule">
                                Activate schedule immediately
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-calendar-plus"></i> Schedule Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Preview Modal -->
<div class="modal fade" id="reportPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportPreviewContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReport()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-success" onclick="downloadCurrentReport()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
let currentReportId = null;

// Initialize charts
const incomeCtx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(incomeCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_data['income']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) ?>,
        datasets: [{
            label: 'Monthly Income',
            data: <?= json_encode($chart_data['income']['data'] ?? [2500, 2800, 2650, 2900, 3100, 2950, 3200, 3050, 2850, 3300, 3150, 3400]) ?>,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            fill: true
        }, {
            label: 'Expected Income',
            data: <?= json_encode($chart_data['expected']['data'] ?? [3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000]) ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            borderDash: [5, 5],
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                    }
                }
            },
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

const propertyCtx = document.getElementById('propertyChart').getContext('2d');
const propertyChart = new Chart(propertyCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chart_data['property']['labels'] ?? ['Occupied', 'Vacant']) ?>,
        datasets: [{
            data: <?= json_encode($chart_data['property']['data'] ?? [85, 15]) ?>,
            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + '%';
                    }
                }
            }
        }
    }
});

const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
const maintenanceChart = new Chart(maintenanceCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_data['maintenance']['labels'] ?? ['Plumbing', 'Electrical', 'HVAC', 'Appliances', 'Other']) ?>,
        datasets: [{
            label: 'Cost ($)',
            data: <?= json_encode($chart_data['maintenance']['data'] ?? [1200, 800, 1500, 600, 400]) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Cost: ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

function generateReport(type) {
    const reportTypes = {
        'income': 'Income Analysis Report',
        'occupancy': 'Occupancy Analysis Report',
        'maintenance': 'Maintenance Analysis Report',
        'tenant': 'Tenant Analysis Report'
    };
    
    document.getElementById('report_name').value = reportTypes[type];
    document.getElementById('report_type').value = type;
    
    // Set default date range (last 3 months)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setMonth(startDate.getMonth() - 3);
    
    document.getElementById('period_start').value = startDate.toISOString().split('T')[0];
    document.getElementById('period_end').value = endDate.toISOString().split('T')[0];
    
    // Auto-select relevant sections based on report type
    document.querySelectorAll('input[name="sections[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    document.getElementById('include_summary').checked = true;
    document.getElementById('include_charts').checked = true;
    
    switch(type) {
        case 'income':
            document.getElementById('include_income').checked = true;
            document.getElementById('include_expenses').checked = true;
            break;
        case 'occupancy':
            document.getElementById('include_occupancy').checked = true;
            break;
        case 'maintenance':
            document.getElementById('include_maintenance').checked = true;
            document.getElementById('include_expenses').checked = true;
            break;
        case 'tenant':
            document.getElementById('include_occupancy').checked = true;
            break;
    }
    
    new bootstrap.Modal(document.getElementById('customReportModal')).show();
}

function generateCustomReport() {
    // Set default date range (last month)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setMonth(startDate.getMonth() - 1);
    
    document.getElementById('period_start').value = startDate.toISOString().split('T')[0];
    document.getElementById('period_end').value = endDate.toISOString().split('T')[0];
    
    new bootstrap.Modal(document.getElementById('customReportModal')).show();
}

function showScheduleReportModal() {
    // Set default start date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('start_date').value = tomorrow.toISOString().split('T')[0];
    
    new bootstrap.Modal(document.getElementById('scheduleReportModal')).show();
}

function changeChartView(chartType, period) {
    // Show loading indicator
    const chartContainer = document.getElementById(chartType + 'Chart').parentElement;
    chartContainer.style.opacity = '0.5';
    
    fetch('<?= site_url('landlord/reports/chart-data') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            chart_type: chartType,
            period: period
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update chart data
            if (chartType === 'income') {
                incomeChart.data.labels = data.labels;
                incomeChart.data.datasets[0].data = data.data;
                incomeChart.update();
            }
        }
        chartContainer.style.opacity = '1';
    })
    .catch(error => {
        console.error('Error updating chart:', error);
        chartContainer.style.opacity = '1';
    });
}

function viewReport(reportId) {
    currentReportId = reportId;
    
    fetch('<?= site_url('landlord/reports/preview') ?>/' + reportId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('reportPreviewContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('reportPreviewModal')).show();
    })
    .catch(error => {
        alert('Error loading report preview: ' + error.message);
    });
}

function regenerateReport(reportId) {
    if (confirm('Are you sure you want to regenerate this report? This may take a few minutes.')) {
        // Show loading indicator
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch('<?= site_url('landlord/reports/regenerate') ?>/' + reportId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report regeneration started. You will be notified when it\'s complete.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        fetch('<?= site_url('landlord/reports/delete') ?>/' + reportId, {
            method: 'DELETE',
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
    }
}

function editSchedule(scheduleId) {
    fetch('<?= site_url('landlord/reports/schedule-details') ?>/' + scheduleId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate the schedule form with existing data
            document.getElementById('schedule_name').value = data.schedule.name;
            document.getElementById('schedule_type').value = data.schedule.type;
            document.getElementById('frequency').value = data.schedule.frequency;
            document.getElementById('start_date').value = data.schedule.start_date;
            document.getElementById('email_recipients').value = data.schedule.recipients;
            document.getElementById('active_schedule').checked = data.schedule.active;
            
            // Change form action to update
            document.getElementById('scheduleReportForm').dataset.scheduleId = scheduleId;
            
            new bootstrap.Modal(document.getElementById('scheduleReportModal')).show();
        }
    })
    .catch(error => {
        alert('Error loading schedule details: ' + error.message);
    });
}

function toggleSchedule(scheduleId) {
    fetch('<?= site_url('landlord/reports/toggle-schedule') ?>/' + scheduleId, {
        method: 'POST',
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
}

function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this scheduled report?')) {
        fetch('<?= site_url('landlord/reports/delete-schedule') ?>/' + scheduleId, {
            method: 'DELETE',
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
    }
}

function printReport() {
    const content = document.getElementById('reportPreviewContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Property Management Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { font-size: 12px; }
                        .card { border: 1px solid #ddd !important; page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body class="p-4">
                ${content}
                <script>window.print(); window.close();</script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

function downloadCurrentReport() {
    if (currentReportId) {
        window.open('<?= site_url('landlord/reports/download') ?>/' + currentReportId, '_blank');
    }
}

// Form submissions
document.getElementById('customReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('landlord/reports/generate') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('customReportModal')).hide();
            
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            }
            
            if (data.message) {
                alert(data.message);
            }
            
            // Refresh the page to show the new report
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('scheduleReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const scheduleId = this.dataset.scheduleId;
    
    const url = scheduleId 
        ? '<?= site_url('landlord/reports/update-schedule') ?>/' + scheduleId
        : '<?= site_url('landlord/reports/schedule') ?>';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('scheduleReportModal')).hide();
            alert(scheduleId ? 'Schedule updated successfully!' : 'Report scheduled successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

// Add hover effects for report cards
document.querySelectorAll('.report-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.transition = 'transform 0.2s';
        this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '';
    });
});

// Auto-refresh charts every 5 minutes
setInterval(() => {
    changeChartView('income', 'monthly');
}, 300000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.report-card {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.metric-item h4 {
    margin-bottom: 5px;
    font-weight: bold;
}

.progress-sm {
    height: 8px;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.card-header .dropdown-toggle::after {
    margin-left: 0.5em;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    margin-right: 0;
}

.modal-xl {
    max-width: 1200px;
}

@media (max-width: 768px) {
    .report-card {
        margin-bottom: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
}

/* Chart container styling */
.card-body canvas {
    max-height: 400px;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?= $this->endSection() ?>