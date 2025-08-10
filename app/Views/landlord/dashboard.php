<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Landlord Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
        <div>
            <span class="text-muted">Welcome back, <?= session()->get('full_name') ?>!</span>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Total Properties Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                My Properties
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_properties'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-success">
                                <i class="fas fa-home"></i> 
                                <?= $stats['occupied_properties'] ?? 0 ?> Occupied
                            </small>
                            <small class="text-warning ml-2">
                                <i class="fas fa-door-open"></i> 
                                <?= $stats['vacant_properties'] ?? 0 ?> Vacant
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Income Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['monthly_income'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-success">
                                <i class="fas fa-check"></i> 
                                $<?= number_format($stats['collected_this_month'] ?? 0, 2) ?> Collected
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Maintenance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['pending_maintenance'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-info">
                                <i class="fas fa-clipboard-list"></i> 
                                Awaiting Approval
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Rate Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Collection Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $rate = $stats['monthly_income'] > 0 ? 
                                    round(($stats['collected_this_month'] / $stats['monthly_income']) * 100, 1) : 0;
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
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= $rate ?>%" 
                                     aria-valuenow="<?= $rate ?>" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties and Payments Row -->
    <div class="row">
        <!-- My Properties -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> My Properties
                    </h6>
                    <a href="<?= site_url('landlord/properties') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($properties)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
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
                                                <strong><?= esc($property['property_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($property['address']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($property['tenant_first_name']): ?>
                                                    <?= esc($property['tenant_first_name'] . ' ' . $property['tenant_last_name']) ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        Lease: <?= date('M Y', strtotime($property['lease_start'])) ?> - 
                                                        <?= date('M Y', strtotime($property['lease_end'])) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Vacant</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong>$<?= number_format($property['rent_amount'] ?? $property['base_rent'], 2) ?></strong>
                                                <br>
                                                <small class="text-muted">per month</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= $property['ownership_percentage'] ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $property['lease_status'] === 'active' ? 'success' : 'warning' ?>">
                                                    <?= $property['lease_status'] === 'active' ? 'Occupied' : 'Vacant' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-building fa-3x mb-3"></i>
                            <p>No properties found</p>
                            <a href="<?= site_url('admin/properties') ?>" class="btn btn-primary">
                                Contact Admin to Add Properties
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Recent Payments
                    </h6>
                    <a href="<?= site_url('landlord/payments') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                            <div class="mb-3 p-3 border-left-<?= $payment['status'] === 'paid' ? 'success' : 'warning' ?> bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            $<?= number_format($payment['amount'] * $payment['ownership_percentage'] / 100, 2) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> 
                                            <?= esc($payment['tenant_first_name'] . ' ' . $payment['tenant_last_name']) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-building"></i> <?= esc($payment['property_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d', strtotime($payment['payment_date'])) ?>
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
        </div>
    </div>

    <!-- Maintenance Requests -->
    <?php if (!empty($maintenance_requests)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-wrench"></i> Pending Maintenance Requests
                        </h6>
                        <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Property</th>
                                        <th>Tenant</th>
                                        <th>Priority</th>
                                        <th>Requested</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance_requests as $request): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($request['title']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= character_limiter(esc($request['description']), 50) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= esc($request['property_name']) ?>
                                                <br>
                                                <small class="text-muted"><?= esc($request['property_address']) ?></small>
                                            </td>
                                            <td>
                                                <?= esc($request['tenant_first_name'] . ' ' . $request['tenant_last_name']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($request['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($request['requested_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $request['status'] === 'completed' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($request['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($request['status'] === 'pending'): ?>
                                                        <button class="btn btn-success" onclick="approveMaintenance(<?= $request['id'] ?>)">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button class="btn btn-danger" onclick="rejectMaintenance(<?= $request['id'] ?>)">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-primary" onclick="viewMaintenance(<?= $request['id'] ?>)">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('landlord/payments') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-credit-card"></i><br>
                                View Payments
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('landlord/tenants') ?>" class="btn btn-success btn-block">
                                <i class="fas fa-users"></i><br>
                                Manage Tenants
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-tools"></i><br>
                                Maintenance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('landlord/reports') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Approval Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maintenanceModalTitle">Maintenance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="maintenanceForm">
                    <div class="mb-3" id="estimatedCostGroup" style="display: none;">
                        <label for="estimated_cost" class="form-label">Estimated Cost</label>
                        <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" step="0.01" min="0">
                    </div>
                    <div class="mb-3" id="notesGroup" style="display: none;">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="maintenanceActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let maintenanceAction = '';
let maintenanceRequestId = 0;

function approveMaintenance(requestId) {
    maintenanceAction = 'approve';
    maintenanceRequestId = requestId;
    
    document.getElementById('maintenanceModalTitle').textContent = 'Approve Maintenance Request';
    document.getElementById('estimatedCostGroup').style.display = 'block';
    document.getElementById('notesGroup').style.display = 'none';
    document.getElementById('maintenanceActionBtn').textContent = 'Approve';
    document.getElementById('maintenanceActionBtn').className = 'btn btn-success';
    
    new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
}

function rejectMaintenance(requestId) {
    maintenanceAction = 'reject';
    maintenanceRequestId = requestId;
    
    document.getElementById('maintenanceModalTitle').textContent = 'Reject Maintenance Request';
    document.getElementById('estimatedCostGroup').style.display = 'none';
    document.getElementById('notesGroup').style.display = 'block';
    document.getElementById('maintenanceActionBtn').textContent = 'Reject';
    document.getElementById('maintenanceActionBtn').className = 'btn btn-danger';
    
    new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
}

function viewMaintenance(requestId) {
    window.location.href = '<?= site_url('landlord/maintenance/view') ?>/' + requestId;
}

document.getElementById('maintenanceActionBtn').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('maintenanceForm'));
    
    const url = maintenanceAction === 'approve' 
        ? '<?= site_url('landlord/maintenance/approve') ?>/' + maintenanceRequestId
        : '<?= site_url('landlord/maintenance/reject') ?>/' + maintenanceRequestId;
    
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
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>

<?= $this->endSection() ?>