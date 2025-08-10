<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Tenant Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-home"></i> My Dashboard
        </h1>
        <div>
            <span class="text-muted">Welcome back, <?= session()->get('full_name') ?>!</span>
        </div>
    </div>

    <!-- Current Lease Information -->
    <?php if ($lease): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-left-primary shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-file-contract"></i> Current Lease Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary"><?= esc($lease['property_name']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt"></i> <?= esc($lease['property_address']) ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Property Type:</strong> <?= ucfirst($lease['property_type']) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Lease Period:</strong> 
                                    <?= date('M d, Y', strtotime($lease['lease_start'])) ?> - 
                                    <?= date('M d, Y', strtotime($lease['lease_end'])) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h3 class="text-success">$<?= number_format($lease['rent_amount'], 2) ?></h3>
                                    <p class="text-muted">Monthly Rent</p>
                                    
                                    <?php if ($next_payment): ?>
                                        <div class="alert alert-<?= $next_payment['status'] === 'overdue' ? 'danger' : 'warning' ?> mb-2">
                                            <strong>Next Payment Due:</strong><br>
                                            <?= date('M d, Y', strtotime($next_payment['due_date'])) ?><br>
                                            <span class="h5">$<?= number_format($next_payment['amount'], 2) ?></span>
                                        </div>
                                        <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-success">
                                            <i class="fas fa-credit-card"></i> Pay Now
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-success mb-2">
                                            <i class="fas fa-check-circle"></i> All payments up to date!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No Active Lease Found</strong><br>
                    Please contact your property manager if you believe this is an error.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Payment Statistics -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Payments Made
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['paid_payments'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-success">
                                $<?= number_format($stats['total_paid_amount'] ?? 0, 2) ?> Total Paid
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['pending_payments'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <?php if (($stats['overdue_payments'] ?? 0) > 0): ?>
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= $stats['overdue_payments'] ?> Overdue
                                </small>
                            <?php else: ?>
                                <small class="text-success">
                                    <i class="fas fa-check"></i> No Overdue
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Maintenance Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_maintenance'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-warning">
                                <i class="fas fa-clock"></i>
                                <?= $stats['pending_maintenance'] ?? 0 ?> Pending
                            </small>
                            <small class="text-success ml-2">
                                <i class="fas fa-check"></i>
                                <?= $stats['completed_maintenance'] ?? 0 ?> Completed
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lease Days Remaining -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Lease Days Remaining
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                if ($lease) {
                                    $daysRemaining = max(0, ceil((strtotime($lease['lease_end']) - time()) / (60 * 60 * 24)));
                                    echo $daysRemaining;
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <?php if ($lease): ?>
                                <small class="<?= isset($daysRemaining) && $daysRemaining < 30 ? 'text-warning' : 'text-info' ?>">
                                    <i class="fas fa-calendar"></i>
                                    Expires <?= date('M d, Y', strtotime($lease['lease_end'])) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments and Maintenance Row -->
    <div class="row">
        <!-- Recent Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Recent Payments
                    </h6>
                    <a href="<?= site_url('tenant/payments') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_payments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_payments as $payment): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                            <td><strong>$<?= number_format($payment['amount'], 2) ?></strong></td>
                                            <td>
                                                <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : ($payment['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payment['status'] === 'paid'): ?>
                                                    <a href="<?= site_url('tenant/payments/receipt/' . $payment['id']) ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-credit-card fa-3x mb-3"></i>
                            <p>No payment history found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Maintenance Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-wrench"></i> Recent Maintenance
                    </h6>
                    <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($maintenance_requests)): ?>
                        <?php foreach ($maintenance_requests as $request): ?>
                            <div class="mb-3 p-3 border-left-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?> bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= esc($request['title']) ?></h6>
                                        <small class="text-muted">
                                            <?= character_limiter(esc($request['description']), 50) ?>
                                        </small>
                                        <br>
                                        <?php if (!empty($request['staff_first_name'])): ?>
                                            <small class="text-info">
                                                <i class="fas fa-user-hard-hat"></i> 
                                                Assigned to: <?= esc($request['staff_first_name'] . ' ' . $request['staff_last_name']) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Awaiting assignment
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                        <br>
                                        <span class="badge badge-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d', strtotime($request['requested_date'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?= site_url('tenant/maintenance/view/' . $request['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <p>No maintenance requests found</p>
                            <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-primary">
                                Submit Request
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

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
                            <?php if ($next_payment): ?>
                                <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-credit-card"></i><br>
                                    Pay Rent
                                </a>
                            <?php else: ?>
                                <a href="<?= site_url('tenant/payments') ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-credit-card"></i><br>
                                    View Payments
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-tools"></i><br>
                                Submit Maintenance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('tenant/lease') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-file-contract"></i><br>
                                View Lease
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Notices -->
    <?php if ($lease && isset($daysRemaining) && $daysRemaining < 60): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-<?= $daysRemaining < 30 ? 'danger' : 'warning' ?> shadow">
                    <h4 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Lease Expiration Notice
                    </h4>
                    <p>
                        Your lease is set to expire in <strong><?= $daysRemaining ?> days</strong> 
                        on <?= date('F d, Y', strtotime($lease['lease_end'])) ?>.
                    </p>
                    <hr>
                    <p class="mb-0">
                        Please contact your landlord if you wish to renew your lease or discuss moving arrangements.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Overdue Payment Notice -->
    <?php if (isset($stats['overdue_payments']) && $stats['overdue_payments'] > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger shadow">
                    <h4 class="alert-heading">
                        <i class="fas fa-exclamation-circle"></i> 
                        Overdue Payment Notice
                    </h4>
                    <p>
                        You have <strong><?= $stats['overdue_payments'] ?></strong> overdue payment(s). 
                        Late fees may apply.
                    </p>
                    <hr>
                    <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-danger">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </a>
                    <a href="<?= site_url('tenant/payments') ?>" class="btn btn-outline-danger">
                        View Payment History
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-refresh dashboard every 5 minutes for real-time updates
setInterval(function() {
    // Only refresh if user is active (to save bandwidth)
    if (document.hasFocus()) {
        location.reload();
    }
}, 300000); // 5 minutes

// Add click tracking for quick actions
document.querySelectorAll('.btn-block').forEach(function(button) {
    button.addEventListener('click', function() {
        // Add loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><br>Loading...';
        this.disabled = true;
        
        // Restore button after navigation (in case back button is used)
        setTimeout(function() {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 3000);
    });
});

// Highlight urgent maintenance requests
document.querySelectorAll('.border-left-danger').forEach(function(element) {
    element.style.animation = 'pulse 2s infinite';
});

// Add CSS animation for urgent items
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.8; }
        100% { opacity: 1; }
    }
`;
document.head.appendChild(style);
</script>

<?= $this->endSection() ?>