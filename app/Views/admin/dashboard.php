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
            <span class="text-muted">Welcome back, <?= esc(session()->get('full_name')) ?>!</span>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row g-3 align-items-stretch stats-row">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Properties
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= (int) ($stats['total_properties'] ?? 0) ?>
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
                                <?= (int) ($stats['occupied_properties'] ?? 0) ?> Occupied
                            </small>
                            <small class="text-warning ms-2">
                                <i class="fas fa-door-open"></i>
                                <?= (int) ($stats['vacant_properties'] ?? 0) ?> Vacant
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= (int) ($stats['total_users'] ?? 0) ?>
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
                                <?= (int) ($stats['total_landlords'] ?? 0) ?> Landlords
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow py-2 stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Net This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                SAR <?= number_format($stats['net_monthly'] ?? 0, 2) ?>
                            </div>
                            <div class="row no-gutters align-items-center mt-2">
                                <div class="col">
                                    <small class="text-success d-block">
                                        <i class="fas fa-arrow-up"></i>
                                        Income: SAR <?= number_format($stats['monthly_income'] ?? 0, 2) ?>
                                    </small>
                                    <small class="text-danger d-block">
                                        <i class="fas fa-arrow-down"></i>
                                        Expenses: SAR <?= number_format($stats['monthly_expense'] ?? 0, 2) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Issues Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-primary shadow py-2 stat-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Issues
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= (int) ($stats['pending_maintenance'] ?? 0) ?>
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
                                        <?= (int) ($stats['overdue_payments'] ?? 0) ?> Overdue Payments
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Row -->
            <div class="row g-3 row-stretch">
                <!-- Recent Payments -->
                <div class="col-xl-6 col-lg-7">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-credit-card"></i> Recent Income & Expenses
                            </h6>
                            <a href="<?= site_url('admin/financials') ?>" class="btn btn-sm btn-primary">View
                                All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_entries)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Property</th>
                                                <th>Unit</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_entries as $e): ?>
                                                <?php
                                                $isIncome = ($e['type'] === 'income');
                                                $badge = $isIncome ? 'success' : 'danger';
                                                $sign = $isIncome ? '+' : '-';
                                                ?>
                                                <tr>
                                                    <td class="small text-muted"><?= esc($e['property_name'] ?? '—') ?></td>
                                                    <td class="small"><?= esc($e['unit_name'] ?? '—') ?></td>
                                                    <td><span
                                                            class="badge badge-<?= $badge ?>"><?= ucfirst($e['type']) ?></span>
                                                    </td>
                                                    <td><strong><?= $sign ?> SAR
                                                            <?= number_format((float) $e['amount'], 2) ?></strong></td>
                                                    <td><small><?= date('M d, Y', strtotime($e['date'])) ?></small></td>
                                                    <td><small><?= esc($e['method'] ?? '—') ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-receipt fa-3x mb-3"></i>
                                    <p>No recent income/expense entries</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Requests (Pending) -->
                <div class="col-xl-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-wrench"></i> Pending Maintenance
                            </h6>
                            <a href="<?= site_url('admin/maintenance') ?>" class="btn btn-sm btn-primary">View
                                All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pending_maintenance)): ?>
                                <?php foreach ($pending_maintenance as $request): ?>
                                    <?php
                                    $p = strtolower($request['priority'] ?? 'normal');
                                    $border = $p === 'urgent' ? 'danger' : ($p === 'high' ? 'warning' : 'info');
                                    $title = $request['title'] ?? 'Untitled';
                                    $prop = $request['property_name'] ?? '—';
                                    $reqAt = !empty($request['requested_date']) ? date('M d', strtotime($request['requested_date'])) : '—';
                                    ?>
                                    <div class="mb-3 p-3 border-left-<?= $border ?> bg-light">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= esc($title) ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-building"></i> <?= esc($prop) ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= $border ?>"><?= ucfirst($p) ?></span>
                                                <br>
                                                <small class="text-muted"><?= $reqAt ?></small>
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
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-exchange-alt"></i> Recent Transfers
                            </h6>
                            <a href="<?= site_url('admin/financials') ?>" class="btn btn-sm btn-primary">View
                                All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_transfers)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Property</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Receipt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_transfers as $t): ?>
                                                <tr>
                                                    <td class="small text-muted"><?= esc($t['property_name'] ?? '—') ?></td>
                                                    <td><strong>SAR
                                                            <?= number_format((float) $t['transfer_amount'], 2) ?></strong>
                                                    </td>
                                                    <td><small><?= date('M d, Y', strtotime($t['transfer_date'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($t['receipt_file'])): ?>
                                                            <a href="<?= base_url(esc($t['receipt_file'])) ?>" target="_blank"
                                                                class="btn btn-outline-secondary btn-sm">
                                                                <i class="fas fa-file"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                    <p>No recent transfers</p>
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
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus"></i><br>
                                        Add New User
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= site_url('admin/properties/create') ?>" class="btn btn-success w-100">
                                        <i class="fas fa-building"></i><br>
                                        Add Property
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= site_url('admin/reports') ?>" class="btn btn-info w-100">
                                        <i class="fas fa-chart-bar"></i><br>
                                        Generate Report
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= site_url('admin/settings') ?>" class="btn btn-secondary w-100">
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
            function getCsrf() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="csrf_test_name"]')?.value
                    || '';
            }

            function setBusy(btn, busy = true) {
                if (!btn) return;
                if (busy) {
                    btn.dataset._html = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled = true;
                } else {
                    btn.innerHTML = btn.dataset._html || btn.innerHTML;
                    btn.disabled = false;
                }
            }

            function markAsPaid(paymentId, btn) {
                if (!confirm('Mark this payment as paid?')) return;
                setBusy(btn, true);

                fetch('<?= site_url('admin/mark-payment-paid') ?>/' + paymentId, {
                    method: 'POST',
                    body: JSON.stringify({ csrf_test_name: getCsrf() }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrf()
                    }
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                        else alert('Failed to update payment: ' + (d.message || 'Unknown error'));
                    })
                    .catch(err => alert('Error: ' + err.message))
                    .finally(() => setBusy(btn, false));
            }

            function sendReminder(paymentId, btn) {
                setBusy(btn, true);

                fetch('<?= site_url('admin/send-payment-reminder') ?>/' + paymentId, {
                    method: 'POST',
                    body: JSON.stringify({ csrf_test_name: getCsrf() }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrf()
                    }
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            btn.classList.remove('btn-outline-primary');
                            btn.classList.add('btn-success');
                            btn.title = 'Reminder sent';
                        } else {
                            alert('Failed to send reminder: ' + (d.message || 'Unknown error'));
                        }
                    })
                    .catch(err => alert('Error: ' + err.message))
                    .finally(() => setBusy(btn, false));
            }
        </script>

        <?= $this->endSection() ?>