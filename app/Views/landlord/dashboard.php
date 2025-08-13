<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Landlord Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome back, <?= session()->get('full_name') ?>!</span>
            <small class="text-muted">
                <i class="fas fa-clock"></i> Last updated: <?= date('M d, Y H:i') ?>
            </small>
        </div>
    </div>

    <!-- Enhanced Statistics Cards Row -->
    <div class="row">
        <!-- Total Properties Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Properties
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_properties'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <small class="text-success">
                                    <i class="fas fa-home"></i> 
                                    <?= $stats['occupied_properties'] ?? 0 ?> Occupied
                                </small>
                                <small class="text-warning">
                                    <i class="fas fa-door-open"></i> 
                                    <?= $stats['vacant_properties'] ?? 0 ?> Vacant
                                </small>
                            </div>
                            <div class="progress mt-1" style="height: 4px;">
                                <?php 
                                $occupancy_rate = $stats['total_properties'] > 0 ? 
                                    ($stats['occupied_properties'] / $stats['total_properties']) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-success" style="width: <?= $occupancy_rate ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Maintenance Requests Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Maintenance
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['pending_maintenance'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <?= $stats['urgent_maintenance'] ?? 0 ?> Urgent
                                </small>
                                <small class="text-info">
                                    <i class="fas fa-clipboard-list"></i> 
                                    <?= $stats['total_maintenance'] ?? 0 ?> Total
                                </small>
                            </div>
                            <?php if (($stats['pending_maintenance'] ?? 0) > 0): ?>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> Requires attention
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Income Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Income
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['monthly_income'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> 
                                    $<?= number_format($stats['collected_this_month'] ?? 0, 2) ?> Collected
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Rate Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Collection Rate
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $rate = ($stats['monthly_income'] ?? 0) > 0 ? 
                                    round((($stats['collected_this_month'] ?? 0) / $stats['monthly_income']) * 100, 1) : 0;
                                echo $rate . '%';
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <div class="progress progress-sm mb-1">
                                <div class="progress-bar <?= $rate >= 90 ? 'bg-success' : ($rate >= 70 ? 'bg-warning' : 'bg-danger') ?>" 
                                     role="progressbar" style="width: <?= $rate ?>%" 
                                     aria-valuenow="<?= $rate ?>" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if ($rate >= 90): ?>
                                    <i class="fas fa-check-circle text-success"></i> Excellent
                                <?php elseif ($rate >= 70): ?>
                                    <i class="fas fa-exclamation-circle text-warning"></i> Good
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i> Needs Attention
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Alerts Row -->
    <?php if (($stats['pending_maintenance'] ?? 0) > 0 || ($stats['overdue_payments'] ?? 0) > 0 || ($stats['vacant_properties'] ?? 0) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-left-warning shadow-sm">
                <h6 class="alert-heading">
                    <i class="fas fa-bell"></i> Attention Required
                </h6>
                <div class="row">
                    <?php if (($stats['pending_maintenance'] ?? 0) > 0): ?>
                        <div class="col-md-4">
                            <i class="fas fa-tools text-warning"></i>
                            <strong><?= $stats['pending_maintenance'] ?></strong> maintenance requests pending approval
                        </div>
                    <?php endif; ?>
                    <?php if (($stats['overdue_payments'] ?? 0) > 0): ?>
                        <div class="col-md-4">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <strong><?= $stats['overdue_payments'] ?></strong> overdue payments
                        </div>
                    <?php endif; ?>
                    <?php if (($stats['vacant_properties'] ?? 0) > 0): ?>
                        <div class="col-md-4">
                            <i class="fas fa-door-open text-info"></i>
                            <strong><?= $stats['vacant_properties'] ?></strong> vacant properties
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Properties and Payments Row -->
    <div class="row">
        <!-- My Properties -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> My Properties
                        <span class="badge bg-primary ms-2"><?= count($properties ?? []) ?></span>
                    </h6>
                    <div>
                        <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-sm btn-success me-1">
                            <i class="fas fa-plus"></i> Add Property
                        </a>
                        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($properties)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property</th>
                                        <th>Tenant</th>
                                        <th>Rent</th>
                                        <th>Ownership</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="property-icon me-2">
                                                        <i class="fas fa-home text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= esc($property['property_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= esc($property['address']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($property['tenant_first_name'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle text-success me-1"></i>
                                                        <div>
                                                            <?= esc(($property['tenant_first_name'] ?? '') . ' ' . ($property['tenant_last_name'] ?? '')) ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                Lease: <?= !empty($property['lease_start']) ? date('M Y', strtotime($property['lease_start'])) : 'N/A' ?> - 
                                                                <?= !empty($property['lease_end']) ? date('M Y', strtotime($property['lease_end'])) : 'N/A' ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-user-slash me-1"></i>
                                                        Vacant
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    $<?= number_format($property['rent_amount'] ?? $property['base_rent'] ?? 0, 2) ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted">per month</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white">
                                                    <?= $property['ownership_percentage'] ?? 100 ?>%
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    $<?= number_format(($property['rent_amount'] ?? $property['base_rent'] ?? 0) * (($property['ownership_percentage'] ?? 100) / 100), 2) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= ($property['lease_status'] ?? 'vacant') === 'active' ? 'success' : 'warning' ?>">
                                                    <i class="fas fa-<?= ($property['lease_status'] ?? 'vacant') === 'active' ? 'check' : 'clock' ?>"></i>
                                                    <?= ($property['lease_status'] ?? 'vacant') === 'active' ? 'Occupied' : 'Vacant' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('landlord/properties/view/' . ($property['id'] ?? '')) ?>" 
                                                       class="btn btn-outline-primary" title="View Property">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= site_url('landlord/properties/edit/' . ($property['id'] ?? '')) ?>" 
                                                       class="btn btn-outline-secondary" title="Edit Property">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-building fa-4x mb-3 text-light"></i>
                            <h5>No properties found</h5>
                            <p>Start by adding your first property</p>
                            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Property
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Payments & Quick Stats -->
        <div class="col-lg-4 mb-4">
            <!-- Recent Payments Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Recent Payments
                        <span class="badge bg-success ms-2"><?= count($recent_payments ?? []) ?></span>
                    </h6>
                    <a href="<?= site_url('landlord/payments') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                            <div class="payment-item mb-2 p-3 border-start border-<?= $payment['status'] === 'paid' ? 'success' : 'warning' ?> border-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 font-weight-bold text-<?= $payment['status'] === 'paid' ? 'success' : 'warning' ?>">
                                            $<?= number_format($payment['amount'] * (($payment['ownership_percentage'] ?? 100) / 100), 2) ?>
                                        </h6>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-user text-muted me-1" style="width: 12px;"></i>
                                            <small class="text-muted">
                                                <?= esc(($payment['tenant_first_name'] ?? '') . ' ' . ($payment['tenant_last_name'] ?? '')) ?>
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-muted me-1" style="width: 12px;"></i>
                                            <small class="text-muted"><?= esc($payment['property_name'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= ($payment['status'] ?? 'pending') === 'paid' ? 'success' : (($payment['status'] ?? 'pending') === 'overdue' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($payment['status'] ?? 'pending') ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= !empty($payment['payment_date']) ? date('M d', strtotime($payment['payment_date'])) : 'N/A' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-credit-card fa-3x mb-3"></i>
                            <p>No recent payments found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Monthly Summary Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> <?= date('F Y') ?> Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <div class="h5 font-weight-bold text-success">
                                    $<?= number_format($stats['collected_this_month'] ?? 0, 0) ?>
                                </div>
                                <small class="text-muted">Collected</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h5 font-weight-bold text-primary">
                                $<?= number_format($stats['monthly_income'] ?? 0, 0) ?>
                            </div>
                            <small class="text-muted">Expected</small>
                        </div>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" style="width: <?= $rate ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted"><?= $rate ?>% collected</small>
                        <small class="text-muted">
                            $<?= number_format(($stats['monthly_income'] ?? 0) - ($stats['collected_this_month'] ?? 0), 2) ?> remaining
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section for Dashboard -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="<?= site_url('landlord/payments') ?>" class="btn btn-primary quick-action-btn w-100">
                            <div class="quick-action-content">
                                <i class="fas fa-credit-card fa-2x mb-3"></i>
                                <h6 class="font-weight-bold mb-2">View Payments</h6>
                                <p class="small mb-0">Track rent payments</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="<?= site_url('landlord/tenants') ?>" class="btn btn-success quick-action-btn w-100">
                            <div class="quick-action-content">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h6 class="font-weight-bold mb-2">Manage Tenants</h6>
                                <p class="small mb-0">Tenant information</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-warning quick-action-btn w-100 position-relative">
                            <div class="quick-action-content">
                                <i class="fas fa-tools fa-2x mb-3"></i>
                                <h6 class="font-weight-bold mb-2">Maintenance</h6>
                                <p class="small mb-0">Handle requests</p>
                                <?php if (($stats['pending_maintenance'] ?? 0) > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $stats['pending_maintenance'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <a href="<?= site_url('landlord/reports') ?>" class="btn btn-info quick-action-btn w-100">
                            <div class="quick-action-content">
                                <i class="fas fa-chart-bar fa-2x mb-3"></i>
                                <h6 class="font-weight-bold mb-2">View Reports</h6>
                                <p class="small mb-0">Financial insights</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Quick Actions Styling */
.quick-action-btn {
    height: 140px;
    border: none;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    text-decoration: none;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.quick-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.quick-action-btn:hover::before {
    opacity: 1;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    color: white;
    text-decoration: none;
}

.quick-action-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 1rem;
    position: relative;
    z-index: 1;
}

.quick-action-content i {
    opacity: 0.9;
    transition: all 0.3s ease;
}

.quick-action-btn:hover .quick-action-content i {
    opacity: 1;
    transform: scale(1.1);
}

.quick-action-content h6 {
    margin-bottom: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
}

.quick-action-content p {
    margin-bottom: 0;
    opacity: 0.9;
    font-size: 0.85rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .quick-action-btn {
        height: 120px;
    }
    
    .quick-action-content h6 {
        font-size: 0.9rem;
    }
    
    .quick-action-content p {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .quick-action-btn {
        height: 100px;
        margin-bottom: 1rem;
    }
    
    .quick-action-content {
        padding: 0.75rem;
    }
    
    .quick-action-content i {
        font-size: 1.5rem !important;
    }
    
    .quick-action-content h6 {
        font-size: 0.85rem;
    }
    
    .quick-action-content p {
        font-size: 0.75rem;
    }
}
</style>
<?= $this->endSection() ?>