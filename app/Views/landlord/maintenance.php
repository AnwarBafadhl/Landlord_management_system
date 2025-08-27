<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Maintenance Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Maintenance Management
        </h1>
        <div class="btn-group">
            <button class="btn btn-success" onclick="showAddMaintenanceModal()">
                <i class="fas fa-plus"></i> Add Request
            </button>
            <button class="btn btn-outline-danger" onclick="exportReport()">
                <i class="fas fa-file-pdf"></i> Export PDF Report
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Status Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approval
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['pending_count'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Awaiting your review
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Approved
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['approved_count'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Ready for work
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                In Progress
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['in_progress_count'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Work ongoing
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['completed_count'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Work finished
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter Requests
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($current_status === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= ($current_status === 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="in_progress" <?= ($current_status === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= ($current_status === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="rejected" <?= ($current_status === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low" <?= ($current_priority === 'low') ? 'selected' : '' ?>>Low</option>
                        <option value="normal" <?= ($current_priority === 'normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="high" <?= ($current_priority === 'high') ? 'selected' : '' ?>>High</option>
                        <option value="urgent" <?= ($current_priority === 'urgent') ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="property" class="form-label">Property</label>
                    <select class="form-select" id="property" name="property">
                        <option value="">All Properties</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['id'] ?>" <?= ($current_property == $property['id']) ? 'selected' : '' ?>>
                                <?= esc($property['property_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Debug Properties Info (Development Only) -->
    <?php if (ENVIRONMENT === 'development' && empty($properties)): ?>
        <div class="alert alert-warning">
            <strong>Debug Info:</strong> No properties found for user ID <?= session()->get('user_id') ?>
            <br><small>Check if property_shareholders table exists and has entries for this user</small>
        </div>
    <?php endif; ?>

    <!-- Maintenance Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Maintenance Requests 
                <span class="badge bg-secondary"><?= count($maintenance_requests) ?></span>
            </h6>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($maintenance_requests)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="maintenanceTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Property/Unit</th>
                                <th width="20%">Title</th>
                                <th width="10%">Priority</th>
                                <th width="10%">Status</th>
                                <th width="10%">Cost</th>
                                <th width="10%">Date</th>
                                <th width="5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance_requests as $request): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $request['id'] ?></strong>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong><?= esc($request['property_name']) ?></strong>
                                            <?php if (!empty($request['unit_name'])): ?>
                                                <br><span class="text-muted">Unit: <?= esc($request['unit_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= esc($request['title']) ?></strong>
                                        <br>
                                        <span class="text-muted small"><?= esc(substr($request['description'], 0, 50)) ?>...</span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityColor = match($request['priority']) {
                                            'low' => 'secondary',
                                            'normal' => 'primary', 
                                            'high' => 'warning',
                                            'urgent' => 'danger',
                                            default => 'secondary'
                                        };
                                        $priorityIcon = match($request['priority']) {
                                            'low' => 'arrow-down',
                                            'normal' => 'minus',
                                            'high' => 'arrow-up', 
                                            'urgent' => 'exclamation-triangle',
                                            default => 'minus'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $priorityColor ?>">
                                            <i class="fas fa-<?= $priorityIcon ?>"></i>
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColor = match($request['status']) {
                                            'pending' => 'warning',
                                            'approved' => 'primary',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusIcon = match($request['status']) {
                                            'pending' => 'clock',
                                            'approved' => 'check-circle',
                                            'in_progress' => 'spinner',
                                            'completed' => 'check-double',
                                            'rejected' => 'times-circle',
                                            default => 'question'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?> p-2">
                                            <i class="fas fa-<?= $statusIcon ?>"></i>
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($request['first_name']) || !empty($request['last_name'])): ?>
                                            <div class="small">
                                                <strong><?= esc(trim($request['first_name'] . ' ' . $request['last_name'])) ?></strong>
                                                <?php if (!empty($request['tenant_email'])): ?>
                                                    <br><span class="text-muted"><?= esc($request['tenant_email']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-info">Landlord Request</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php if (!empty($request['estimated_cost'])): ?>
                                                <span class="text-muted">Est:</span> $<?= number_format($request['estimated_cost'], 2) ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($request['actual_cost'])): ?>
                                                <span class="text-success">Act:</span> $<?= number_format($request['actual_cost'], 2) ?>
                                            <?php endif; ?>
                                            <?php if (empty($request['estimated_cost']) && empty($request['actual_cost'])): ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= date('M d, Y', strtotime($request['created_at'])) ?>
                                            <br><span class="text-muted"><?= date('g:i A', strtotime($request['created_at'])) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <button class="btn btn-outline-info btn-sm" onclick="viewRequest(<?= $request['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button class="btn btn-outline-success btn-sm" onclick="approveRequest(<?= $request['id'] ?>)" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="rejectRequest(<?= $request['id'] ?>)" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif (in_array($request['status'], ['approved', 'in_progress'])): ?>
                                                <button class="btn btn-outline-primary btn-sm" onclick="updateStatus(<?= $request['id'] ?>)" title="Update Status">
                                                    <i class="fas fa-edit"></i>
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
                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                    <h4>No Maintenance Requests</h4>
                    <p class="text-muted">
                        <?php if (empty($properties)): ?>
                            You don't have access to any properties. Please contact admin to assign properties to your account.
                        <?php else: ?>
                            No maintenance requests found with the current filters.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($properties)): ?>
                        <button class="btn btn-primary" onclick="showAddMaintenanceModal()">
                            <i class="fas fa-plus"></i> Create New Request
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Maintenance Request Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Maintenance Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMaintenanceForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_property_id" class="form-label">Property <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_property_id" name="property_id" required>
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($properties)): ?>
                                    <small class="text-danger">No properties available. Please contact admin.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_unit_id" name="unit_id" required>
                                    <option value="">Select Unit</option>
                                </select>
                                <small class="text-muted">Please select a property first to load units</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="modal_title" class="form-label">Request Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_title" name="title" required maxlength="200"
                                       placeholder="Brief description of the issue">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_priority" name="priority" required>
                                    <option value="normal" selected>Normal</option>
                                    <option value="low">Low</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_description" name="description" rows="4" required
                                  placeholder="Detailed description of the maintenance issue"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_estimated_cost" class="form-label">Estimated Cost (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="modal_estimated_cost" name="estimated_cost" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-save"></i> Create Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Maintenance Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading request details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Update Request Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_status" class="form-label">Status</label>
                        <select class="form-select" id="update_status" name="status" required>
                            <option value="approved">Approved</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="cost_section" style="display: none;">
                        <label for="actual_cost" class="form-label">Actual Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="actual_cost" name="actual_cost" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="mb-3" id="rejection_section" style="display: none;">
                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                                  placeholder="Please provide a reason for rejection"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="3"
                                  placeholder="Additional notes about the status update"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentRequestId = null;

// Show Add Maintenance Modal
function showAddMaintenanceModal() {
    // Check if properties are available
    const propertySelect = document.getElementById('modal_property_id');
    if (propertySelect.options.length <= 1) { // Only has "Select Property" option
        showAlert('warning', 'No properties available. Please contact admin to assign properties to your account.');
        return;
    }
    
    document.getElementById('addMaintenanceForm').reset();
    document.getElementById('modal_unit_id').innerHTML = '<option value="">Select Unit</option>';
    new bootstrap.Modal(document.getElementById('addMaintenanceModal')).show();
}

// Handle property selection change - IMPROVED VERSION
document.getElementById('modal_property_id').addEventListener('change', function() {
    const propertyId = this.value;
    const unitSelect = document.getElementById('modal_unit_id');
    
    // Reset and disable units dropdown
    unitSelect.innerHTML = '<option value="">Loading units...</option>';
    unitSelect.disabled = true;
    
    if (propertyId) {
        // Load units for selected property
        console.log('Loading units for property:', propertyId);
        
        fetch(`<?= site_url('landlord/get-units-by-property') ?>/${propertyId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Units data received:', data);
            
            unitSelect.innerHTML = '<option value="">Select Unit *</option>';
            
            if (data.success && data.units && data.units.length > 0) {
                data.units.forEach(unit => {
                    unitSelect.innerHTML += `<option value="${unit.id}">${unit.unit_name}</option>`;
                });
                console.log(`Loaded ${data.units.length} units`);
            } else {
                unitSelect.innerHTML += '<option value="" disabled>No units found for this property</option>';
                showAlert('warning', 'No units found for the selected property');
            }
            
            unitSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading units:', error);
            unitSelect.innerHTML = '<option value="">Error loading units</option>';
            unitSelect.disabled = false;
            showAlert('danger', 'Failed to load units: ' + error.message);
        });
    } else {
        unitSelect.innerHTML = '<option value="">Select Unit *</option>';
        unitSelect.disabled = false;
    }
});

// Handle form submission - IMPROVED VERSION WITH UNIT VALIDATION
document.getElementById('addMaintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate unit selection
    const unitSelect = document.getElementById('modal_unit_id');
    if (!unitSelect.value) {
        showAlert('warning', 'Please select a unit. Unit selection is required for maintenance requests.');
        unitSelect.focus();
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    const formData = new FormData(this);
    
    // Debug form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    fetch('<?= site_url('landlord/add-maintenance-request') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addMaintenanceModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
            if (data.errors) {
                let errorMsg = 'Validation errors:\n';
                for (let field in data.errors) {
                    errorMsg += `- ${field}: ${data.errors[field]}\n`;
                }
                console.error('Validation errors:', data.errors);
                alert(errorMsg); // Show detailed errors
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while creating the request: ' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// View Request Details
function viewRequest(requestId) {
    currentRequestId = requestId;
    
    // Show modal with loading state
    const modal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
    const content = document.getElementById('requestDetailsContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p>Loading request details...</p>
        </div>
    `;
    
    modal.show();
    
    // For now, show basic info from the table
    // In a full implementation, you'd fetch detailed info from the server
    const row = document.querySelector(`button[onclick="viewRequest(${requestId})"]`).closest('tr');
    if (row) {
        const cells = row.querySelectorAll('td');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Request Information</h6>
                    <p><strong>ID:</strong> ${cells[0].textContent.trim()}</p>
                    <p><strong>Property:</strong> ${cells[1].textContent.trim()}</p>
                    <p><strong>Title:</strong> ${cells[2].querySelector('strong').textContent}</p>
                    <p><strong>Priority:</strong> ${cells[3].textContent.trim()}</p>
                    <p><strong>Status:</strong> ${cells[4].textContent.trim()}</p>
                </div>
                <div class="col-md-6">
                    <h6>Additional Details</h6>
                    <p><strong>Cost:</strong> ${cells[6].textContent.trim()}</p>
                    <p><strong>Date:</strong> ${cells[7].textContent.trim()}</p>
                </div>
            </div>
            <div class="mt-3">
                <h6>Description</h6>
                <div class="bg-light p-3 rounded">
                    ${cells[2].querySelector('.text-muted').textContent}
                </div>
            </div>
        `;
    }
}

// Approve Request
function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this maintenance request?')) {
        updateRequestStatus(requestId, 'approved', {}, 'Request approved successfully');
    }
}

// Reject Request
function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason !== null) { // User didn't cancel
        const data = reason.trim() ? { rejection_reason: reason.trim() } : {};
        updateRequestStatus(requestId, 'rejected', data, 'Request rejected successfully');
    }
}

// Update Status Modal
function updateStatus(requestId) {
    currentRequestId = requestId;
    
    // Reset form
    document.getElementById('statusUpdateForm').reset();
    document.getElementById('cost_section').style.display = 'none';
    document.getElementById('rejection_section').style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
}

// Handle status change in update modal
document.getElementById('update_status').addEventListener('change', function() {
    const costSection = document.getElementById('cost_section');
    const rejectionSection = document.getElementById('rejection_section');
    
    // Hide all sections first
    costSection.style.display = 'none';
    rejectionSection.style.display = 'none';
    
    if (this.value === 'completed') {
        costSection.style.display = 'block';
    } else if (this.value === 'rejected') {
        rejectionSection.style.display = 'block';
    }
});

// Handle status update form submission
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentRequestId) return;
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    updateRequestStatus(currentRequestId, data.status, data, 'Status updated successfully');
});

// Update Request Status Function
function updateRequestStatus(requestId, status, additionalData = {}, successMessage = 'Status updated successfully') {
    const data = { status, ...additionalData };
    
    fetch(`<?= site_url('landlord/update-maintenance-status') ?>/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', successMessage);
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'))?.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Clear Filters
function clearFilters() {
    window.location.href = '<?= site_url('landlord/maintenance') ?>';
}

// Export Report - PDF ONLY
function exportReport() {
    const params = new URLSearchParams(window.location.search);
    window.open(`<?= site_url('landlord/export-maintenance-report') ?>?${params.toString()}`, '_blank');
}

// Refresh Table
function refreshTable() {
    location.reload();
}

// Show Alert Function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    const firstChild = container.querySelector('.d-sm-flex');
    container.insertBefore(alertDiv, firstChild.nextSibling);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Debug function for development
function debugPropertyLoad() {
    console.log('Available properties:', <?= json_encode($properties) ?>);
    console.log('Current user ID:', <?= session()->get('user_id') ?>);
    console.log('User role:', '<?= session()->get('role') ?>');
}

// Call debug function in development
<?php if (ENVIRONMENT === 'development'): ?>
debugPropertyLoad();
<?php endif; ?>
</script>

<?= $this->endSection() ?>