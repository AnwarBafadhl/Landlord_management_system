<?= $this->extend('layouts/maintenance') ?>

<?= $this->section('title') ?>Maintenance Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Work Dashboard
        </h1>
        <div>
            <span class="text-muted">Welcome back, <?= session()->get('full_name') ?>!</span>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Total Assigned Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Assigned
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_assigned'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-success">
                                <i class="fas fa-check"></i>
                                <?= $stats['completed'] ?? 0 ?> Completed
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Work Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Work
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['today_work'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-info">
                                <i class="fas fa-play"></i>
                                <?= $stats['in_progress'] ?? 0 ?> In Progress
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Requests Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Urgent Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['urgent'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-warning">
                                <i class="fas fa-clock"></i>
                                Immediate Attention
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Completion Time Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Completion
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['avg_completion_time'] ?? 0 ?> days
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stopwatch fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-success">
                                <i class="fas fa-chart-line"></i>
                                Performance Metric
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Work and Urgent Requests Row -->
    <div class="row">
        <!-- Today's Work -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-day"></i> Today's Work Schedule
                    </h6>
                    <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($today_requests)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Request</th>
                                        <th>Property</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_requests as $request): ?>
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($request['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= esc($request['title']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= character_limiter(esc($request['description']), 40) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= esc($request['property_name']) ?>
                                                <br>
                                                <small class="text-muted"><?= esc($request['property_address']) ?></small>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('maintenance/requests/view/' . $request['id']) ?>"
                                                        class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($request['status'] === 'approved'): ?>
                                                        <button class="btn btn-outline-success"
                                                            onclick="updateStatus(<?= $request['id'] ?>, 'in_progress')">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($request['status'] === 'in_progress'): ?>
                                                        <button class="btn btn-outline-warning"
                                                            onclick="completeRequest(<?= $request['id'] ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check fa-3x mb-3"></i>
                            <p>No work scheduled for today</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Urgent Requests -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Urgent Requests
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($urgent_requests)): ?>
                        <?php foreach ($urgent_requests as $request): ?>
                            <div class="mb-3 p-3 border-left-danger bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 text-danger"><?= esc($request['title']) ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-building"></i> <?= esc($request['property_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="badge badge-<?= $request['status'] === 'completed' ? 'success' : 'danger' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d', strtotime($request['requested_date'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?= site_url('maintenance/requests/view/' . $request['id']) ?>"
                                        class="btn btn-sm btn-danger">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p>No urgent requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                    <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-sm btn-primary">View All
                        Requests</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($assigned_requests)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Property</th>
                                        <th>Priority</th>
                                        <th>Assigned</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recentRequests = array_slice($assigned_requests, 0, 10); // Show last 10
                                    foreach ($recentRequests as $request):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($request['title']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= character_limiter(esc($request['description']), 30) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= esc($request['property_name']) ?>
                                                <br>
                                                <small
                                                    class="text-muted"><?= character_limiter(esc($request['property_address']), 30) ?></small>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($request['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $request['assigned_date'] ? date('M d, Y', strtotime($request['assigned_date'])) : 'Not assigned' ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('maintenance/requests/view/' . $request['id']) ?>"
                                                        class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($request['status'] === 'approved'): ?>
                                                        <button class="btn btn-outline-success"
                                                            onclick="updateStatus(<?= $request['id'] ?>, 'in_progress')"
                                                            title="Start Work">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php elseif ($request['status'] === 'in_progress'): ?>
                                                        <button class="btn btn-outline-warning"
                                                            onclick="completeRequest(<?= $request['id'] ?>)" title="Mark Complete">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p>No requests assigned yet</p>
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
                            <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-list"></i><br>
                                All Requests
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('maintenance/requests?status=in_progress') ?>"
                                class="btn btn-warning btn-block">
                                <i class="fas fa-play"></i><br>
                                In Progress
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('maintenance/schedule') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-calendar-alt"></i><br>
                                My Schedule
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= site_url('maintenance/profile') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-user-cog"></i><br>
                                My Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Request Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <?= csrf_field() ?>
                    <input type="hidden" id="requestId" name="request_id">
                    <input type="hidden" id="newStatus" name="status">

                    <div class="mb-3">
                        <label for="work_notes" class="form-label">Work Notes</label>
                        <textarea class="form-control" id="work_notes" name="work_notes" rows="3"
                            placeholder="Add notes about the work performed..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Request Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="completeForm">
                    <?= csrf_field() ?>
                    <input type="hidden" id="completeRequestId" name="request_id">

                    <div class="mb-3">
                        <label for="actual_cost" class="form-label">Actual Cost</label>
                        <input type="number" class="form-control" id="actual_cost" name="actual_cost" step="0.01"
                            min="0" placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label for="materials_used" class="form-label">Materials Used</label>
                        <textarea class="form-control" id="materials_used" name="materials_used" rows="2"
                            placeholder="List materials and parts used..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">Completion Notes *</label>
                        <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3"
                            placeholder="Describe the work completed..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmComplete">Mark Complete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toast(msg, type = 'info') {
        const cls = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const div = document.createElement('div');
        div.className = `alert ${cls} alert-dismissible fade show notification-alert position-fixed`;
        div.style.top = '20px'; div.style.right = '20px'; div.style.zIndex = '9999'; div.style.minWidth = '300px';
        div.innerHTML = `<i class="fas ${icon}"></i> ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div); 
        setTimeout(() => div.remove(), 4000);
    }

    function updateStatus(requestId, status) {
        document.getElementById('requestId').value = requestId;
        document.getElementById('newStatus').value = status;
        document.getElementById('work_notes').value = '';
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function completeRequest(requestId) {
        document.getElementById('completeRequestId').value = requestId;
        document.getElementById('actual_cost').value = '';
        document.getElementById('materials_used').value = '';
        document.getElementById('completion_notes').value = '';
        new bootstrap.Modal(document.getElementById('completeModal')).show();
    }

    document.getElementById('confirmStatusUpdate').addEventListener('click', function () {
        const formData = new FormData(document.getElementById('statusForm'));
        const requestId = document.getElementById('requestId').value;

        fetch('<?= site_url('maintenance/requests/update-status') ?>/' + requestId, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json()).then(data => {
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            if (data.success) {
                toast('Status updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast('Error: ' + (data.message || 'Failed to update status'), 'error');
            }
        }).catch(error => {
            toast('Error: ' + error.message, 'error');
        });
    });

    document.getElementById('confirmComplete').addEventListener('click', function () {
        const completionNotes = document.getElementById('completion_notes').value;
        if (!completionNotes.trim()) {
            toast('Completion notes are required', 'error');
            return;
        }

        const formData = new FormData(document.getElementById('completeForm'));
        const requestId = document.getElementById('completeRequestId').value;

        fetch('<?= site_url('maintenance/requests/complete') ?>/' + requestId, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json()).then(data => {
            bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
            if (data.success) {
                toast('Request completed successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast('Error: ' + (data.message || 'Failed to complete request'), 'error');
            }
        }).catch(error => {
            toast('Error: ' + error.message, 'error');
        });
    });

    // Auto-refresh dashboard every 5 minutes for real-time updates
    setInterval(function () {
        if (document.hasFocus()) {
            location.reload();
        }
    }, 300000); // 5 minutes
</script>

<style>
    .notification-alert {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        border: none;
    }

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .btn-block {
        display: block;
        width: 100%;
    }
</style>

<?= $this->endSection() ?>