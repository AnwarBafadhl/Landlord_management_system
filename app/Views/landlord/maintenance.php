<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Maintenance Requests<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Maintenance Requests
        </h1>
        <div>
            <button class="btn btn-primary" onclick="showCreateRequestModal()">
                <i class="fas fa-plus"></i> Create Request
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approval
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($maintenance_requests) ? array_reduce($maintenance_requests, function($carry, $req) { return $carry + ($req['status'] === 'pending' ? 1 : 0); }, 0) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                In Progress
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($maintenance_requests) ? array_reduce($maintenance_requests, function($carry, $req) { return $carry + ($req['status'] === 'approved' || $req['status'] === 'in_progress' ? 1 : 0); }, 0) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
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
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= array_reduce($maintenance_requests, function($carry, $req) { return $carry + ($req['status'] === 'completed' ? 1 : 0); }, 0) ?>
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Urgent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= array_reduce($maintenance_requests, function($carry, $req) { return $carry + ($req['priority'] === 'urgent' && $req['status'] !== 'completed' ? 1 : 0); }, 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="property_filter" class="form-label">Property</label>
                    <select class="form-control" id="property_filter" name="property_id">
                        <option value="">All Properties</option>
                        <?php if (!empty($properties)): ?>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= $property['id'] ?>">
                                    <?= esc($property['property_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-control" id="status_filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority_filter" class="form-label">Priority</label>
                    <select class="form-control" id="priority_filter" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category_filter" class="form-label">Category</label>
                    <select class="form-control" id="category_filter" name="category">
                        <option value="">All Categories</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="electrical">Electrical</option>
                        <option value="hvac">HVAC</option>
                        <option value="appliances">Appliances</option>
                        <option value="flooring">Flooring</option>
                        <option value="painting">Painting</option>
                        <option value="pest_control">Pest Control</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Maintenance Requests</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportMaintenance('pdf')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportMaintenance('excel')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($maintenance_requests)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="maintenanceTable">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance_requests as $request): ?>
                                <tr class="request-row" data-priority="<?= $request['priority'] ?>">
                                    <td>
                                        <strong><?= esc($request['title']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= character_limiter(esc($request['description']), 60) ?>
                                        </small>
                                        <?php if (!empty($request['attachments'])): ?>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-paperclip"></i> <?= count(explode(',', $request['attachments'])) ?> attachments
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($request['property_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($request['property_address']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= esc($request['tenant_first_name'] . ' ' . $request['tenant_last_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($request['tenant_phone'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= ucwords(str_replace('_', ' ', $request['category'] ?? 'general')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : ($request['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= getStatusBadgeClass($request['status']) ?>">
                                            <?= getStatusLabel($request['status']) ?>
                                        </span>
                                        <?php if ($request['status'] === 'completed' && !empty($request['completed_date'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                Completed: <?= date('M d', strtotime($request['completed_date'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($request['requested_date'])) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php 
                                            $days = floor((time() - strtotime($request['requested_date'])) / (60 * 60 * 24));
                                            echo $days . ' days ago';
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!empty($request['estimated_cost'])): ?>
                                            <strong class="text-success">$<?= number_format($request['estimated_cost'], 2) ?></strong>
                                            <br>
                                            <small class="text-muted">Estimated</small>
                                        <?php endif; ?>
                                        <?php if (!empty($request['actual_cost'])): ?>
                                            <br>
                                            <strong class="text-primary">$<?= number_format($request['actual_cost'], 2) ?></strong>
                                            <br>
                                            <small class="text-muted">Actual</small>
                                        <?php endif; ?>
                                        <?php if (empty($request['estimated_cost']) && empty($request['actual_cost'])): ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewMaintenanceDetails(<?= $request['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="approveMaintenance(<?= $request['id'] ?>)" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="rejectMaintenance(<?= $request['id'] ?>)" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($request['status'] === 'approved' || $request['status'] === 'in_progress'): ?>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="updateProgress(<?= $request['id'] ?>)" title="Update Progress">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="completeMaintenance(<?= $request['id'] ?>)" title="Mark Complete">
                                                    <i class="fas fa-flag-checkered"></i>
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
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-4x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-500">No Maintenance Requests Found</h4>
                    <p class="text-muted">No maintenance requests match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Maintenance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRequestForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="property_id" class="form-label">Property *</label>
                            <select class="form-control" id="property_id" name="property_id" required>
                                <option value="">Select Property</option>
                                <?php if (!empty($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>">
                                            <?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="hvac">HVAC</option>
                                <option value="appliances">Appliances</option>
                                <option value="flooring">Flooring</option>
                                <option value="painting">Painting</option>
                                <option value="pest_control">Pest Control</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority *</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estimated_cost" class="form-label">Estimated Cost</label>
                            <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               placeholder="Brief description of the issue">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required
                                  placeholder="Detailed description of the maintenance issue..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="attachments" class="form-label">Attachments</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" 
                               multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">You can upload images, PDFs, or documents</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Maintenance Details Modal -->
<div class="modal fade" id="maintenanceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Maintenance Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="maintenanceDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printMaintenanceDetails()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">Approve Maintenance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm">
                <div class="modal-body">
                    <input type="hidden" id="approval_request_id" name="request_id">
                    <input type="hidden" id="approval_action" name="action">
                    
                    <div class="mb-3" id="estimatedCostGroup">
                        <label for="approval_estimated_cost" class="form-label">Estimated Cost</label>
                        <input type="number" class="form-control" id="approval_estimated_cost" 
                               name="estimated_cost" step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="approval_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="approval_notes" name="notes" rows="3"
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="contractorGroup" style="display: none;">
                        <label for="contractor_name" class="form-label">Contractor</label>
                        <input type="text" class="form-control" id="contractor_name" name="contractor_name"
                               placeholder="Contractor name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="approvalActionBtn">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Progress Update Modal -->
<div class="modal fade" id="progressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="progressForm">
                <div class="modal-body">
                    <input type="hidden" id="progress_request_id" name="request_id">
                    
                    <div class="mb-3">
                        <label for="progress_status" class="form-label">Status</label>
                        <select class="form-control" id="progress_status" name="status" required>
                            <option value="approved">Approved</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="progress_notes" class="form-label">Progress Notes</label>
                        <textarea class="form-control" id="progress_notes" name="progress_notes" rows="3"
                                  placeholder="Update on the maintenance progress..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="actualCostGroup" style="display: none;">
                        <label for="actual_cost" class="form-label">Actual Cost</label>
                        <input type="number" class="form-control" id="actual_cost" name="actual_cost" 
                               step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3" id="completionDateGroup" style="display: none;">
                        <label for="completion_date" class="form-label">Completion Date</label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<?php 
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending': return 'warning';
        case 'approved': return 'info';
        case 'in_progress': return 'primary';
        case 'completed': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function getStatusLabel($status) {
    switch($status) {
        case 'pending': return 'Pending';
        case 'approved': return 'Approved';
        case 'in_progress': return 'In Progress';
        case 'completed': return 'Completed';
        case 'rejected': return 'Rejected';
        default: return ucfirst($status);
    }
}
?>

function showCreateRequestModal() {
    new bootstrap.Modal(document.getElementById('createRequestModal')).show();
}

function viewMaintenanceDetails(requestId) {
    fetch('<?= site_url('landlord/maintenance/details') ?>/' + requestId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('maintenanceDetailsContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('maintenanceDetailsModal')).show();
    })
    .catch(error => {
        alert('Error loading maintenance details: ' + error.message);
    });
}

function approveMaintenance(requestId) {
    document.getElementById('approval_request_id').value = requestId;
    document.getElementById('approval_action').value = 'approve';
    document.getElementById('approvalModalTitle').textContent = 'Approve Maintenance Request';
    document.getElementById('estimatedCostGroup').style.display = 'block';
    document.getElementById('contractorGroup').style.display = 'block';
    document.getElementById('approvalActionBtn').textContent = 'Approve';
    document.getElementById('approvalActionBtn').className = 'btn btn-success';
    
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}

function rejectMaintenance(requestId) {
    document.getElementById('approval_request_id').value = requestId;
    document.getElementById('approval_action').value = 'reject';
    document.getElementById('approvalModalTitle').textContent = 'Reject Maintenance Request';
    document.getElementById('estimatedCostGroup').style.display = 'none';
    document.getElementById('contractorGroup').style.display = 'none';
    document.getElementById('approvalActionBtn').textContent = 'Reject';
    document.getElementById('approvalActionBtn').className = 'btn btn-danger';
    
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}

function updateProgress(requestId) {
    document.getElementById('progress_request_id').value = requestId;
    new bootstrap.Modal(document.getElementById('progressModal')).show();
}

function completeMaintenance(requestId) {
    document.getElementById('progress_request_id').value = requestId;
    document.getElementById('progress_status').value = 'completed';
    document.getElementById('actualCostGroup').style.display = 'block';
    document.getElementById('completionDateGroup').style.display = 'block';
    
    new bootstrap.Modal(document.getElementById('progressModal')).show();
}

function exportMaintenance(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    formData.append('format', format);
    const params = new URLSearchParams(formData).toString();
    window.open('<?= site_url('landlord/maintenance/export') ?>?' + params, '_blank');
}

function printMaintenanceDetails() {
    const content = document.getElementById('maintenanceDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Maintenance Request Details</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="p-4">
                ${content}
                <script>window.print(); window.close();</script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Form submissions
document.getElementById('createRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('landlord/maintenance/create') ?>', {
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

document.getElementById('approvalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const action = formData.get('action');
    const requestId = formData.get('request_id');
    
    const url = action === 'approve' 
        ? '<?= site_url('landlord/maintenance/approve') ?>/' + requestId
        : '<?= site_url('landlord/maintenance/reject') ?>/' + requestId;
    
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

document.getElementById('progressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const requestId = formData.get('request_id');
    
    fetch('<?= site_url('landlord/maintenance/update-progress') ?>/' + requestId, {
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

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData).toString();
    window.location.href = '<?= site_url('landlord/maintenance') ?>?' + params;
});

// Show/hide cost fields based on status selection
document.getElementById('progress_status').addEventListener('change', function() {
    const status = this.value;
    document.getElementById('actualCostGroup').style.display = status === 'completed' ? 'block' : 'none';
    document.getElementById('completionDateGroup').style.display = status === 'completed' ? 'block' : 'none';
});
</script>

<?= $this->endSection() ?>