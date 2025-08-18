<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>My Properties<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> My Properties
        </h1>
        <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-success">
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

    <!-- Properties Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Properties
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Vacant Properties
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $activeCount = 0;
                                if (!empty($properties)) {
                                    foreach ($properties as $property) {
                                        if (($property['status'] ?? '') === 'vacant')
                                            $activeCount++;
                                    }
                                }
                                echo $activeCount;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Total Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?php
                                $totalValue = 0;
                                if (!empty($properties)) {
                                    foreach ($properties as $property) {
                                        $value = ($property['property_value'] ?? 0);
                                        $ownership = ($property['ownership_percentage'] ?? 100) / 100;
                                        $totalValue += $value * $ownership;
                                    }
                                }
                                echo number_format($totalValue, 0);
                                ?>
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
                                My Shares
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $totalShares = 0;
                                if (!empty($properties)) {
                                    foreach ($properties as $property) {
                                        $totalShares += ($property['my_shares'] ?? 0);
                                    }
                                }
                                echo number_format($totalShares);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Properties List
            </h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Filter Options:</div>
                    <a class="dropdown-item" href="#" onclick="filterProperties(this, 'all')">Show All</a>
                    <a class="dropdown-item" href="#" onclick="filterProperties(this, 'vacant')">Vacant</a>
                    <a class="dropdown-item" href="#" onclick="filterProperties(this, 'occupied')">Occupied</a>
                    <a class="dropdown-item" href="#" onclick="filterProperties(this, 'maintenance')">Maintenance</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($properties) && is_array($properties)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="propertiesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Property Details</th>
                                <th>Value & Shares</th>
                                <th>My Ownership</th>
                                <th>Management</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr class="property-row" data-status="<?= esc($property['status'] ?? 'pending') ?>">
                                    <td>
                                        <div class="property-info">
                                            <h6 class="mb-1 font-weight-bold">
                                                <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>"
                                                    class="text-primary text-decoration-none">
                                                    <?= esc($property['property_name']) ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small mb-1">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= esc(substr($property['address'] ?? '', 0, 50)) ?>
                                                <?= strlen($property['address'] ?? '') > 50 ? '...' : '' ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                Added <?= date('M d, Y', strtotime($property['created_at'] ?? 'now')) ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="value-info">
                                            <div class="mb-1">
                                                <strong class="text-success">SAR
                                                    <?= number_format($property['property_value'] ?? 0, 0) ?></strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-chart-pie"></i>
                                                <?= number_format($property['total_shares'] ?? 0) ?> total shares
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-coins"></i>
                                                SAR <?= number_format($property['share_value'] ?? 0, 2) ?> per share
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="ownership-info">
                                            <div class="mb-1">
                                                <span class="badge badge-pill badge-primary">
                                                    <?= number_format($property['my_shares'] ?? 0) ?> shares
                                                </span>
                                            </div>
                                            <div class="mb-1">
                                                <span class="badge badge-pill badge-success">
                                                    <?= number_format($property['ownership_percentage'] ?? 0, 2) ?>%
                                                </span>
                                            </div>
                                            <div class="small text-muted">
                                                Value: SAR
                                                <?= number_format(($property['property_value'] ?? 0) * (($property['ownership_percentage'] ?? 0) / 100), 0) ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="management-info">
                                            <div class="small mb-1">
                                                <strong><?= esc($property['management_company'] ?? 'Self-Management') ?></strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-percentage"></i>
                                                <?= $property['management_percentage'] ?? 0 ?>% fee
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-clock"></i>
                                                <?= $property['contribution_duration'] ?? 0 ?> months
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?php
                                        $status = strtolower(trim($property['status'] ?? 'vacant'));
                                        $badgeMap = [
                                            'vacant' => 'badge-info',
                                            'occupied' => 'badge-success',
                                            'maintenance' => 'badge-warning',
                                        ];
                                        $badgeClass = $badgeMap[$status] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge badge-pill <?= $badgeClass ?>">
                                            <?= ucfirst($status) ?>
                                        </span>

                                        <?php if (isset($property['total_owners']) && $property['total_owners'] > 1): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-users"></i>
                                                <?= $property['total_owners'] ?> owners
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>"
                                                class="btn btn-primary btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>"
                                                class="btn btn-outline-primary btn-sm" title="Edit Property">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <style>
                        /* Always show badge colors clearly */
                        .badge-info {
                            background-color: #4e73df !important;
                            color: #fff !important;
                        }

                        .badge-success {
                            background-color: #1cc88a !important;
                            color: #fff !important;
                        }

                        .badge-primary {
                            background-color: #36b9cc !important;
                            color: #fff !important;
                        }

                        .badge-warning {
                            background-color: #f6c23e !important;
                            color: #212529 !important;
                        }

                        .badge-secondary {
                            background-color: #858796 !important;
                            color: #fff !important;
                        }

                        .badge-light {
                            background-color: #e9ecef !important;
                            color: #212529 !important;
                        }
                    </style>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-building fa-5x text-gray-300"></i>
                    </div>
                    <h4 class="text-gray-600 mb-3">No Properties Found</h4>
                    <p class="text-muted mb-4">
                        You haven't added any properties yet. Start building your portfolio by adding your first property.
                    </p>
                    <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-success btn-lg">
                        <i class="fas fa-plus-circle"></i> Add Your First Property
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<style>
    .property-info h6 {
        line-height: 1.2;
    }

    .property-info p {
        line-height: 1.3;
    }

    .value-info,
    .ownership-info,
    .management-info {
        min-height: 60px;
    }

    .badge {
        font-size: 0.75em;
    }

    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }

    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #5a5c69;
        background-color: #f8f9fc;
    }

    .text-decoration-none:hover {
        text-decoration: underline !important;
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
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

    .text-xs {
        font-size: 0.7rem;
    }

    .fa-2x {
        font-size: 2em;
    }

    .fa-5x {
        font-size: 5em;
    }

    /* DataTables custom styling */
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_length {
        float: left;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_info {
        clear: both;
        float: left;
        padding-top: 0.755em;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: 0.25em;
    }

    .page-link {
        color: #4e73df;
        border-color: #4e73df;
    }

    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
</style>

<?= $this->endSection() ?>