<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Content Row -->
    <div class="row justify-content-center">

        <!-- Total Properties Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Properties</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($properties_count) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Remaining Balance Card -->
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

        <!-- Completed Maintenance Requests Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed Maintenance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ($maintenance_stats['completed_count'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted">Completed requests</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Remaining Balance Breakdown -->
    <?php if (!empty($balance_breakdown) && count($balance_breakdown) > 0 && $total_remaining_balance > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Remaining Balance Breakdown by Property
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Property Name</th>
                                    <th>Your Ownership</th>
                                    <th>Property Remaining Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($balance_breakdown as $balance): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($balance['property_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= number_format($balance['ownership_percentage'], 2) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-success font-weight-bold">
                                            SAR <?= number_format($balance['property_remaining_balance'], 2) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="2">Total Remaining Balance</th>
                                    <th>
                                        <span class="text-success font-weight-bold">
                                            SAR <?= number_format($total_remaining_balance, 2) ?>
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>How Remaining Balance is Calculated:</strong><br>
                            Net Profit (Income - Management Fees - Expenses) - Total Transfer Amounts = Remaining Balance
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Content Row -->
    <div class="row">

        <!-- Properties Overview -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Properties Overview</h6>
                    <a href="<?= site_url('landlord/properties') ?>" class="btn btn-primary btn-sm">
                        View All Properties
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($properties)): ?>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Value</th>
                                        <th>Your Shares</th>
                                        <th>Remaining Balance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($properties, 0, 5) as $property): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-primary">
                                                    <?= esc($property['property_name']) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= esc(substr($property['address'] ?? '', 0, 50)) ?>...
                                                </small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">
                                                    SAR <?= number_format($property['property_value'] ?? 0, 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= number_format($property['my_shares'] ?? $property['shares'] ?? 0) ?> shares
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?= number_format($property['ownership_percentage'] ?? 0, 2) ?>%
                                                </small>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    SAR <?= number_format($property['remaining_balance'] ?? 0, 2) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (count($properties) > 5): ?>
                            <div class="text-center">
                                <a href="<?= site_url('landlord/properties') ?>" class="btn btn-link">
                                    View All <?= count($properties) ?> Properties
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Properties Yet</h5>
                            <p class="text-muted">Start by adding your first property to track remaining balances</p>
                            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Property
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush"> 
                        <a href="<?= site_url('landlord/properties') ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-building text-info mr-2"></i>
                                Properties
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>                     
                        <a href="<?= site_url('landlord/payments') ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-credit-card text-info mr-2"></i>
                                Income & Expenses
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        
                        <a href="<?= site_url('landlord/maintenance') ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tools text-warning mr-2"></i>
                                Maintenance
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        
                        <a href="<?= site_url('landlord/reports') ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-bar text-primary mr-2"></i>
                                Reports
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Remaining Balance Summary Card - Only show if there's balance -->
            <?php if (($total_remaining_balance ?? 0) > 0): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-wallet"></i> Balance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-success font-weight-bold">
                            SAR <?= number_format($total_remaining_balance, 2) ?>
                        </h3>
                        <small class="text-muted">Available across all properties</small>
                    </div>
                    
                    <div class="text-center">
                        <a href="<?= site_url('landlord/payments') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add Transfer Receipt
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.list-group-item-action:hover {
    background-color: #f8f9fc;
}

.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease;
}
</style>

<?= $this->endSection() ?>