<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Reports & Analytics<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Reports & Analytics
        </h1>
        <div class="d-flex gap-2">
            <select class="form-select" id="report_year" onchange="updateReports()">
                <?php for ($year = date('Y'); $year >= date('Y') - 5; $year--): ?>
                    <option value="<?= $year ?>" <?= $year == date('Y') ? 'selected' : '' ?>><?= $year ?></option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-success" onclick="exportReports()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Income (YTD)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($financial_summary['total_income'] ?? 0, 2) ?>
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Expenses (YTD)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($financial_summary['total_expenses'] ?? 0, 2) ?>
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
                                $<?= number_format(($financial_summary['total_income'] ?? 0) - ($financial_summary['total_expenses'] ?? 0), 2) ?>
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
                                Occupancy Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($report_data['occupancy_rate'] ?? 0, 1) ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Income Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Monthly Income Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Property Status Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Property Status
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="propertyChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Property Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Property Performance
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($property_performance)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property</th>
                                        <th>Income</th>
                                        <th>Occupancy</th>
                                        <th>ROI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($property_performance as $property): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($property['property_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($property['address']) ?></small>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    $<?= number_format($property['total_income'] ?? 0, 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= ($property['occupancy_rate'] ?? 0) >= 90 ? 'success' : (($property['occupancy_rate'] ?? 0) >= 70 ? 'warning' : 'danger') ?>">
                                                    <?= number_format($property['occupancy_rate'] ?? 0, 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-<?= ($property['roi'] ?? 0) >= 8 ? 'success' : 'warning' ?>">
                                                    <?= number_format($property['roi'] ?? 0, 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>No property performance data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Maintenance Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Maintenance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($maintenance_summary)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Requests</th>
                                        <th>Total Cost</th>
                                        <th>Avg Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance_summary as $category): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-<?= $category['category'] === 'Plumbing' ? 'faucet' : ($category['category'] === 'Electrical' ? 'bolt' : ($category['category'] === 'HVAC' ? 'snowflake' : 'tools')) ?> text-primary me-2"></i>
                                                <?= esc($category['category']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $category['count'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-danger">$<?= number_format($category['total_cost'], 2) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted">$<?= number_format($category['avg_cost'], 2) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <p>No maintenance data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Payment Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($payment_analysis)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th>Expected</th>
                                        <th>Collected</th>
                                        <th>Collection Rate</th>
                                        <th>Outstanding</th>
                                        <th>On-Time Payments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_analysis as $month_data): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($month_data['month']) ?></strong>
                                            </td>
                                            <td>
                                                $<?= number_format($month_data['expected'], 2) ?>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    $<?= number_format($month_data['collected'], 2) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php $rate = $month_data['expected'] > 0 ? ($month_data['collected'] / $month_data['expected']) * 100 : 0; ?>
                                                <span class="badge bg-<?= $rate >= 95 ? 'success' : ($rate >= 85 ? 'warning' : 'danger') ?>">
                                                    <?= number_format($rate, 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php $outstanding = $month_data['expected'] - $month_data['collected']; ?>
                                                <span class="text-<?= $outstanding > 0 ? 'danger' : 'success' ?>">
                                                    $<?= number_format($outstanding, 2) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= $month_data['on_time_payments'] ?>/<?= $month_data['total_payments'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-credit-card fa-3x mb-3"></i>
                            <p>No payment data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Income Chart
const incomeCtx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(incomeCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_data['income']['labels'] ?? []) ?>,
        datasets: [{
            label: 'Monthly Income',
            data: <?= json_encode($chart_data['income']['data'] ?? []) ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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

// Property Status Chart
const propertyCtx = document.getElementById('propertyChart').getContext('2d');
const propertyChart = new Chart(propertyCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chart_data['property']['labels'] ?? ['Occupied', 'Vacant']) ?>,
        datasets: [{
            data: <?= json_encode($chart_data['property']['data'] ?? [0, 0]) ?>,
            backgroundColor: [
                '#1cc88a',
                '#f6c23e'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Update reports function
function updateReports() {
    const year = document.getElementById('report_year').value;
    window.location.href = `<?= site_url('landlord/reports') ?>?year=${year}`;
}

// Export reports function
function exportReports() {
    const year = document.getElementById('report_year').value;
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    btn.disabled = true;
    
    // Create export URL
    const exportUrl = `<?= site_url('landlord/reports/export') ?>?year=${year}&format=pdf`;
    
    // Create temporary link and download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `financial_report_${year}.pdf`;
    link.click();
    
    // Restore button
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

// Auto-refresh charts when window resizes
window.addEventListener('resize', function() {
    incomeChart.resize();
    propertyChart.resize();
});
</script>

<style>
/* Reports Page Styling */
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.no-gutters {
    margin-right: 0;
    margin-left: 0;
}

.no-gutters > .col,
.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

/* Chart containers */
#incomeChart {
    height: 300px !important;
}

#propertyChart {
    height: 250px !important;
}

/* Table enhancements */
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}

/* Card hover effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-sm-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .form-select,
    .btn {
        width: 100%;
    }
    
    #incomeChart {
        height: 250px !important;
    }
    
    #propertyChart {
        height: 200px !important;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
}
</style>

<?= $this->endSection() ?>