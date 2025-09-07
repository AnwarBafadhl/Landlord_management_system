<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>My Properties<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> My Properties
        </h1>
        <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-success shadow">
            <i class="fas fa-plus"></i> Add New Property
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- FIXED: Properties Summary Cards with correct colors matching dashboard -->
    <div class="row mb-4">
        <!-- Total Properties Card - FIXED: Use info color like dashboard -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Properties
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($summary['total_properties'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-info"></i>
                        </div>
                    </div>
                    <small class="text-muted">Properties in your portfolio</small>
                </div>
            </div>
        </div>

        <!-- Total Remaining Balance Card - FIXED: Use warning color like dashboard -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Remaining Balance
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format((float) ($summary['total_remaining_balance'] ?? 0), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-warning"></i>
                        </div>
                    </div>
                    <small class="text-muted">Undistributed profits across all properties ready for transfer</small>
                </div>
            </div>
        </div>

        <!-- Total Units Card - FIXED: Use primary color like dashboard -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-normal text-uppercase mb-1">
                                Total Units
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($summary['total_units'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-normal"></i>
                        </div>
                    </div>
                    <small class="text-muted">Units across all properties</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties List -->
    <?php if (!empty($properties)): ?>
        <div class="row">
            <?php foreach ($properties as $property): ?>
                <div class="col-xl-6 col-lg-12 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-home"></i> <?= esc($property['property_name']) ?>
                            </h6>
                        </div>

                        <div class="card-body">
                            <!-- Property Info -->
                            <div class="col-12">
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= esc($property['address'] ?? 'Address not specified') ?>
                                </p>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-3">
                                        <div class="text-xs text-gray-500">My Shares</div>
                                        <div class="font-weight-bold text-primary">
                                            <?= number_format($property['my_shares'] ?? $property['shares'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-xs text-gray-500">Ownership</div>
                                        <div class="font-weight-bold text-success">
                                            <?= number_format($property['ownership_percentage'] ?? 0, 1) ?>%
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-xs text-gray-500">Units</div>
                                        <div class="font-weight-bold text-normal">
                                            <?= number_format($property['total_units'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-xs text-gray-500">Available Balance</div>
                                        <div class="font-weight-bold text-warning">
                                            SAR <?= number_format($property['remaining_balance'] ?? 0, 2) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-gray-50">
                            <div class="row text-center">
                                <div class="col-6">
                                    <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" 
                                       class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>" 
                                       class="btn btn-outline-secondary btn-sm btn-block">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?= $property['total_owners'] ?? 1 ?> owner(s) •
                                    <i class="fas fa-calendar"></i> Added <?= date('M Y', strtotime($property['created_at'] ?? 'now')) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- No Properties State -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-building fa-5x text-gray-300"></i>
            </div>
            <h4 class="text-gray-500 mb-3">No Properties Yet</h4>
            <p class="text-gray-400 mb-4">
                Start building your real estate portfolio by adding your first property.
            </p>
            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-success btn-lg">
                <i class="fas fa-plus"></i> Add Your First Property
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.card-header .dropdown-toggle::after {
    display: none;
}

.bg-gray-50 {
    background-color: #f8f9fc !important;
}

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

.text-gray-500 {
    color: #6c757d !important;
}

.text-gray-600 {
    color: #6c757d !important;
}
</style>
<?= $this->endSection() ?>