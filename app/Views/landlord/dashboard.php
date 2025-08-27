<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Dashboard') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome back, <?= esc(session()->get('full_name') ?? 'User') ?>!</span>
            <small class="text-muted">
                <i class="fas fa-clock"></i> Last updated: <?= date('M d, Y H:i') ?>
            </small>
        </div>
    </div>

    <!-- Stats Cards (match controller variables) -->
    <div class="row">
        <!-- Total Properties -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Properties
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= (int)($properties_count ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <small class="text-muted">Properties you have shares in</small>
                </div>
            </div>
        </div>

        <!-- Total Investment -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Investment (Your Share)
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format((float)($total_investment ?? 0), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <small class="text-muted">Sum of property value × your ownership %</small>
                </div>
            </div>
        </div>

        <!-- Total Shares -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Total Shares
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= (float)($total_shares ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <small class="text-muted">Aggregate shares you own</small>
                </div>
            </div>
        </div>

        <!-- Monthly Income (Expected) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Income (Expected)
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format((float)($monthly_income ?? 0), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <small class="text-muted">From occupied units × your %</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Properties (uses $recent_properties from controller) -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Recent Properties
                        <span class="badge bg-primary ms-2"><?= count($recent_properties ?? []) ?></span>
                    </h6>
                    <div>
                        <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-sm btn-success me-1">
                            <i class="fas fa-plus"></i> Add Property
                        </a>
                        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_properties)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property</th>
                                        <th>Your Ownership %</th>
                                        <th>Your Value Share</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_properties as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($p['property_name'] ?? 'N/A') ?></strong><br>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white">
                                                    <?= (float)($p['ownership_percentage'] ?? 0) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $value = (float)($p['property_value'] ?? 0);
                                                    $own  = (float)($p['ownership_percentage'] ?? 0);
                                                    $share = $value * ($own / 100);
                                                ?>
                                                $<?= number_format($share, 2) ?>
                                                <br><small class="text-muted">of $<?= number_format($value, 2) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('landlord/properties/view/' . (int)($p['id'] ?? 0)) ?>" 
                                                       class="btn btn-outline-primary" title="View Property">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= site_url('landlord/properties/edit/' . (int)($p['id'] ?? 0)) ?>" 
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
                            <h5>No recent properties</h5>
                            <p>Start by adding your first property</p>
                            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Property
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (kept, but no $stats badges) -->
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
                            <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-warning quick-action-btn w-100">
                                <div class="quick-action-content">
                                    <i class="fas fa-tools fa-2x mb-3"></i>
                                    <h6 class="font-weight-bold mb-2">Maintenance</h6>
                                    <p class="small mb-0">Handle requests</p>
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

</div>

<!-- Keep your existing styles for quick actions -->
<style>
.quick-action-btn {
    height: 140px; border: none; border-radius: 0.75rem; transition: all 0.3s ease;
    text-decoration: none; color: white; position: relative; overflow: hidden;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.1);
}
.quick-action-btn::before { content:''; position:absolute; inset:0;
    background:linear-gradient(135deg, rgba(255,255,255,.1) 0%, rgba(255,255,255,0) 100%);
    opacity:0; transition:opacity .3s ease;
}
.quick-action-btn:hover::before { opacity:1; }
.quick-action-btn:hover { transform: translateY(-3px); box-shadow:0 .5rem 1rem rgba(0,0,0,.2); color:white; text-decoration:none; }
.quick-action-content { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:1rem; position:relative; z-index:1; }
.quick-action-content i { opacity:.9; transition: all .3s ease; }
.quick-action-btn:hover .quick-action-content i { opacity:1; transform: scale(1.1); }
.quick-action-content h6 { margin-bottom:.5rem; font-size:1rem; font-weight:600; }
.quick-action-content p { margin-bottom:0; opacity:.9; font-size:.85rem; }
@media (max-width: 992px){ .quick-action-btn{ height:120px; } .quick-action-content h6{ font-size:.9rem; } .quick-action-content p{ font-size:.8rem; } }
@media (max-width: 576px){ .quick-action-btn{ height:100px; margin-bottom:1rem; } .quick-action-content{ padding:.75rem; } .quick-action-content i{ font-size:1.5rem !important; } .quick-action-content h6{ font-size:.85rem; } .quick-action-content p{ font-size:.75rem; } }
</style>
<?= $this->endSection() ?>
