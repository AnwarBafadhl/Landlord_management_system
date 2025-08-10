<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

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
                                Total Properties
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

        <!-- Total Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_users'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-info">
                                <i class="fas fa-user-tie"></i> 
                                <?= $stats['total_landlords'] ?? 0 ?> Landlords
                            </small>
                            <small class="text-primary ml-2">
                                <i class="fas fa-user"></i> 
                                <?= $stats['total_tenants'] ?? 0 ?> Tenants
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['total_monthly_rent'] ?? 0, 2) ?>
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
                                $<?= number_format($stats['collected_rent'] ?? 0, 2) ?> Collected
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Issues Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Issues
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
                            <small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?= $stats['overdue_payments'] ?? 0 ?> Overdue Payments
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row">
        <!-- Recent Payments -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Recent Payments
                    </h6>
                    <a href="<?= site_url('admin/financials') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_payments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <div class="small">
                                                    <?= esc($payment['tenant_first_name'] . ' ' . $payment['tenant_last_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small text-muted">
                                                    <?= esc($payment['property_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>$<?= number_format($payment['amount'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <small><?= date('M d, Y', strtotime($payment['payment_date'])) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($payment['status']) ?>
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
                            <p>No recent payments found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests -->
        <div class="col-xl-6 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-wrench"></i> Pending Maintenance
                    </h6>
                    <a href="<?= site_url('admin/maintenance') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pending_maintenance)): ?>
                        <?php foreach ($pending_maintenance as $request): ?>
                            <div class="mb-3 p-3 border-left-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?> bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= esc($request['title']) ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-building"></i> <?= esc($request['property_name']) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> 
                                            <?= esc($request['tenant_first_name'] . ' ' . $request['tenant_last_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d', strtotime($request['requested_date'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <p>No pending maintenance requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Payments -->
    <?php if (!empty($overdue_payments)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card border-left-danger shadow mb-4">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle"></i> Overdue Payments - Action Required
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount Due</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($overdue_payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($payment['tenant_first_name'] . ' ' . $payment['tenant_last_name']) ?></strong>
                                            </td>
                                            <td>
                                                <?= esc($payment['property_name']) ?>
                                                <br>
                                                <small class="text-muted"><?= esc($payment['property_address']) ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-danger">$<?= number_format($payment['amount'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($payment['due_date'])) ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $days_overdue = (strtotime('now') - strtotime($payment['due_date'])) / (60 * 60 * 24);
                                                ?>
                                                <span class="badge badge-danger">
                                                    <?= floor($days_overdue) ?> days
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope"></i> <?= esc($payment['tenant_email']) ?><br>
                                                    <i class="fas fa-phone"></i> <?= esc($payment['tenant_phone'] ?? 'N/A') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="sendReminder(<?= $payment['id'] ?>)">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" onclick="markAsPaid(<?= $payment['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
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
                            <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i><br>
                                Add New User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('admin/properties/create') ?>" class="btn btn-success btn-block">
                                <i class="fas fa-building"></i><br>
                                Add Property
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('admin/reports') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-chart-bar"></i><br>
                                Generate Report
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('admin/settings') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-cog"></i><br>
                                System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Quick Actions -->
<script>
function sendReminder(paymentId) {
    if (confirm('Send payment reminder to tenant?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        // AJAX call to send reminder
        fetch('<?= site_url('admin/send-payment-reminder') ?>/' + paymentId, {
            method: 'POST',
            body: JSON.stringify({}),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reminder sent successfully!');
            } else {
                alert('Failed to send reminder: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Restore button
            button.innerHTML = originalHtml;
            button.disabled = false;
        });
    }
}

function markAsPaid(paymentId) {
    if (confirm('Mark this payment as paid?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        // AJAX call to mark as paid
        fetch('<?= site_url('admin/mark-payment-paid') ?>/' + paymentId, {
            method: 'POST',
            body: JSON.stringify({}),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update payment: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Restore button if not reloading
            button.innerHTML = originalHtml;
            button.disabled = false;
        });
    }
}
</script>

<?= $this->endSection() ?>