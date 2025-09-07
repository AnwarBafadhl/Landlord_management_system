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
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Status cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['pending_count'] ?? 0 ?></div>
                        <div class="text-xs text-muted">Awaiting acceptance</div>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Approved</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['approved_count'] ?? 0 ?></div>
                        <div class="text-xs text-muted">Accepted by maintenance</div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['in_progress_count'] ?? 0 ?>
                        </div>
                        <div class="text-xs text-muted">Work ongoing</div>
                    </div>
                    <i class="fas fa-spinner fa-2x text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['completed_count'] ?? 0 ?></div>
                        <div class="text-xs text-muted">Work finished</div>
                    </div>
                    <i class="fas fa-check-double fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter Requests</h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="pending" <?= ($current_status === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= ($current_status === 'approved') ? 'selected' : '' ?>>Approved
                        </option>
                        <option value="in_progress" <?= ($current_status === 'in_progress') ? 'selected' : '' ?>>In
                            Progress</option>
                        <option value="completed" <?= ($current_status === 'completed') ? 'selected' : '' ?>>Completed
                        </option>
                        <option value="rejected" <?= ($current_status === 'rejected') ? 'selected' : '' ?>>Rejected
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">All</option>
                        <option value="low" <?= ($current_priority === 'low') ? 'selected' : '' ?>>Low</option>
                        <option value="normal" <?= ($current_priority === 'normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="high" <?= ($current_priority === 'high') ? 'selected' : '' ?>>High</option>
                        <option value="urgent" <?= ($current_priority === 'urgent') ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="property" class="form-label">Property</label>
                    <select class="form-select" id="property" name="property">
                        <option value="">All</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['id'] ?>" <?= ($current_property == $property['id']) ? 'selected' : '' ?>>
                                <?= esc($property['property_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Maintenance Requests
                <span class="badge bg-secondary"><?= count($maintenance_requests) ?></span>
            </h6>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
        <div class="card-body">
            <?php if (!empty($maintenance_requests)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="maintenanceTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="11%">Date</th>
                                <th width="23%">Request</th>
                                <th width="16%">Property</th>
                                <th width="12%">Unit</th>
                                <th width="12%">Priority</th>
                                <th width="14%">Status</th>
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance_requests as $request): ?>
                                <?php
                                $priorityColor = match ($request['priority']) {
                                    'low' => 'secondary',
                                    'normal' => 'primary',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                    default => 'secondary'
                                };
                                $priorityIcon = match ($request['priority']) {
                                    'low' => 'arrow-down',
                                    'normal' => 'minus',
                                    'high' => 'arrow-up',
                                    'urgent' => 'exclamation-triangle',
                                    default => 'minus'
                                };
                                $statusColor = match ($request['status']) {
                                    'pending' => 'warning',
                                    'approved' => 'primary',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                                $statusIcon = match ($request['status']) {
                                    'pending' => 'clock',
                                    'approved' => 'check-circle',
                                    'in_progress' => 'spinner',
                                    'completed' => 'check-double',
                                    'rejected' => 'times-circle',
                                    default => 'question'
                                };

                                // helpful flags from controller if available
                                $approvedCost = $request['approved_cost'] ?? null;
                                $approvedByName = $request['approved_by_name'] ?? ($request['approved_by_staff_name'] ?? null);
                                $assignedDate = $request['assigned_date'] ?? null;
                                $completedDate = $request['completed_date'] ?? null;
                                $estCost = $request['estimated_cost'] ?? null;
                                $desiredStart = $request['desired_start_date'] ?? null;
                                $cancelCount = (int) ($request['cancel_count'] ?? 0);
                                ?>
                                <tr data-id="<?= (int) $request['id'] ?>" data-created="<?= esc($request['created_at']) ?>"
                                    data-title="<?= esc($request['title']) ?>"
                                    data-property="<?= esc($request['property_name']) ?>"
                                    data-unit="<?= esc($request['unit_name'] ?? 'N/A') ?>"
                                    data-priority="<?= esc(ucfirst($request['priority'])) ?>"
                                    data-status="<?= esc(ucfirst(str_replace('_', ' ', $request['status']))) ?>"
                                    data-estimated-cost="<?= $estCost !== null ? number_format((float) $estCost, 2) : '' ?>"
                                    data-approved-cost="<?= $approvedCost !== null ? number_format((float) $approvedCost, 2) : '' ?>"
                                    data-approved-by="<?= esc($approvedByName ?? '') ?>"
                                    data-assigned-date="<?= !empty($assignedDate) ? date('M d, Y', strtotime($assignedDate)) : '' ?>"
                                    data-completed-date="<?= !empty($completedDate) ? date('M d, Y', strtotime($completedDate)) : '' ?>"
                                    data-desired-start="<?= !empty($desiredStart) ? date('M d, Y', strtotime($desiredStart)) : '' ?>"
                                    data-cancel-count="<?= $cancelCount ?>">
                                    <td>
                                        <div class="small">
                                            <?= date('M d, Y', strtotime($request['created_at'])) ?><br>
                                            <span
                                                class="text-muted"><?= date('g:i A', strtotime($request['created_at'])) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= esc($request['title']) ?></strong>
                                        <?php if ($approvedCost !== null): ?>
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-handshake"></i>
                                                    Agreed: <strong><?= number_format((float) $approvedCost, 2) ?></strong> SAR
                                                </span>
                                                <?php if (!empty($approvedByName)): ?>
                                                    <span class="text-muted ms-1 small">by <?= esc($approvedByName) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($assignedDate)): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-calendar-check"></i>
                                                Scheduled: <?= date('M d, Y', strtotime($assignedDate)) ?>
                                            </div>
                                        <?php elseif (!empty($desiredStart)): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-calendar"></i>
                                                Desired start: <?= date('M d, Y', strtotime($desiredStart)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($cancelCount > 0 && $request['status'] === 'pending'): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-dark">
                                                    <i class="fas fa-undo-alt"></i> Previously cancelled (available again)
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <br>
                                        <span
                                            class="text-muted small preview"><?= esc(substr($request['description'], 0, 50)) ?>...</span>
                                        <span class="full-desc d-none"><?= nl2br(esc($request['description'])) ?></span>
                                    </td>
                                    <td><strong><?= esc($request['property_name']) ?></strong></td>
                                    <td>
                                        <?php if (!empty($request['unit_name'])): ?>
                                            <span class="badge bg-info"><?= esc($request['unit_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $priorityColor ?>">
                                            <i class="fas fa-<?= $priorityIcon ?>"></i>
                                            <?= ucfirst($request['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $statusColor ?> p-2">
                                            <i class="fas fa-<?= $statusIcon ?>"></i>
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                        <?php if (!empty($completedDate)): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-flag-checkered"></i>
                                                <?= date('M d, Y', strtotime($completedDate)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" onclick="viewRequest(<?= $request['id'] ?>)"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button class="btn btn-outline-danger"
                                                    onclick="deleteRequest(<?= $request['id'] ?>)" title="Delete Request">
                                                    <i class="fas fa-trash"></i>
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
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Maintenance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMaintenanceForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Property <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_property_id" name="property_id" required>
                                    <option value="">Select Property</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>"><?= esc($property['property_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($properties)): ?>
                                    <small class="text-danger">No properties available. Please contact admin.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_unit_id" name="unit_id" required>
                                    <option value="">Select Unit</option>
                                </select>
                                <small class="text-muted">Select a property first to load units</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Request Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_title" name="title" required
                                    maxlength="200" placeholder="Brief description of the issue">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
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
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_description" name="description" rows="4" required
                            placeholder="Detailed description of the maintenance issue"></textarea>
                    </div>

                    <!-- NEW: optional landlord-side fields to support the flow -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimated Cost (SAR, optional)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="modal_estimated_cost"
                                    name="estimated_cost" placeholder="0.00">
                                <small class="text-muted">If set, maintenance cannot approve more than estimate + 150
                                    SAR.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Desired Start Date (optional)</label>
                                <input type="date" class="form-control" id="modal_desired_start_date"
                                    name="desired_start_date">
                                <small class="text-muted">Maintenance will try to start on or before this date.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i>
                        Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitBtn"><i class="fas fa-save"></i> Create
                        Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Maintenance Request Details</h5>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentRequestId = null;

    function showAddMaintenanceModal() {
        const propertySelect = document.getElementById('modal_property_id');
        if (!propertySelect || propertySelect.options.length <= 1) {
            showAlert('warning', 'No properties available. Please contact admin to assign properties to your account.');
            return;
        }
        document.getElementById('addMaintenanceForm').reset();
        document.getElementById('modal_unit_id').innerHTML = '<option value="">Select Unit</option>';
        new bootstrap.Modal(document.getElementById('addMaintenanceModal')).show();
    }

    // Load units for a property
    document.getElementById('modal_property_id')?.addEventListener('change', function () {
        const propertyId = this.value;
        const unitSelect = document.getElementById('modal_unit_id');
        unitSelect.innerHTML = '<option value="">Loading units...</option>';
        unitSelect.disabled = true;

        if (!propertyId) {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            unitSelect.disabled = false;
            return;
        }

        fetch(`<?= site_url('landlord/maintenance/get-units') ?>/${propertyId}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                unitSelect.innerHTML = '<option value="">Select Unit</option>';
                if (data.success && Array.isArray(data.units) && data.units.length) {
                    data.units.forEach(u => {
                        const name = u.unit_name || u.name || ('Unit ' + u.id);
                        unitSelect.innerHTML += `<option value="${u.id}">${name}</option>`;
                    });
                } else if (data.success) {
                    unitSelect.innerHTML = '<option value="" disabled>No units found for this property</option>';
                } else {
                    unitSelect.innerHTML = '<option value="" disabled>Error loading units</option>';
                    showAlert('warning', data.message || 'Failed to load units');
                }
                unitSelect.disabled = false;
            })
            .catch(err => {
                unitSelect.innerHTML = '<option value="" disabled>Error loading units</option>';
                unitSelect.disabled = false;
                showAlert('danger', 'Failed to load units. Please try again.');
            });
    });

    // Submit add form
    document.getElementById('addMaintenanceForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const unitSelect = document.getElementById('modal_unit_id');
        if (!unitSelect.value) {
            showAlert('warning', 'Please select a unit.');
            unitSelect.focus();
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

        const formData = new FormData(this);
        fetch('<?= site_url('landlord/add-maintenance-request') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showAlert('success', 'Maintenance request created successfully with pending status.');
                    bootstrap.Modal.getInstance(document.getElementById('addMaintenanceModal')).hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showAlert('danger', d.message || 'Failed to create request.');
                }
            })
            .catch(err => showAlert('danger', err.message))
            .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = original; });
    });

    // Enhanced view request function with proper database field mapping
    function viewRequest(requestId) {
        currentRequestId = requestId;
        const modal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
        const content = document.getElementById('requestDetailsContent');

        // Show loading state
        content.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p>Loading request details...</p>
        </div>
    `;

        modal.show();

        // Fetch request details via AJAX
        fetch(`<?= site_url('landlord/maintenance/view-request') ?>/${requestId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRequestDetails(data.request, data.images || [], data.cancellations || []);
                } else {
                    content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error: ${data.message || 'Failed to load request details'}
                </div>
            `;
                }
            })
            .catch(error => {
                content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading request details. Please try again.
            </div>
        `;
                console.error('Error:', error);
            });
    }

    // Enhanced display function with cancellation history
    function displayRequestDetails(request, images, cancellations) {
        const content = document.getElementById('requestDetailsContent');

        // Format dates using your actual database field names
        const createdDate = request.requested_date ? new Date(request.requested_date).toLocaleString() : '—';
        const assignedDate = request.assigned_date ? new Date(request.assigned_date).toLocaleString() : '—';
        const approvedDate = request.approved_date ? new Date(request.approved_date).toLocaleString() : '—';
        const completedDate = request.completed_date ? new Date(request.completed_date).toLocaleString() : '—';
        const rejectedDate = request.rejected_date ? new Date(request.rejected_date).toLocaleString() : '—';

        // Format currency
        const formatCurrency = (amount) => amount ? `SAR ${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}` : '—';

        // Status badge
        const getStatusBadge = (status) => {
            const statusColors = {
                'pending': 'warning',
                'approved': 'primary',
                'in_progress': 'info',
                'completed': 'success',
                'rejected': 'danger',
                'cancelled': 'secondary'
            };
            return `<span class="badge bg-${statusColors[status] || 'secondary'}">${status.replace('_', ' ').toUpperCase()}</span>`;
        };

        // Priority badge
        const getPriorityBadge = (priority) => {
            const priorityColors = {
                'low': 'secondary',
                'normal': 'primary',
                'high': 'warning',
                'urgent': 'danger'
            };
            return `<span class="badge bg-${priorityColors[priority] || 'secondary'}">${priority.toUpperCase()}</span>`;
        };

        // Build images HTML
        let imagesHtml = '';
        if (images && images.length > 0) {
            imagesHtml = `
        <div class="col-12">
            <h6 class="mb-3"><i class="fas fa-images"></i> Completion Images</h6>
            <div class="row g-3">
                ${images.map(img => {
                // Extract just the filename from the full path
                const filename = img.image_path.split('/').pop();
                // Use the correct route for serving images
                const imageUrl = `https://www.tab3ni.online/landlord/landlord/maintenance/image/${filename}`;

                return `
                        <div class="col-md-4 col-lg-3">
                            <div class="card">
                                <img src="${imageUrl}" 
                                     class="card-img-top completion-image" 
                                     alt="Completion Image"
                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                     onclick="showImageModal('${imageUrl}', '${escapeHtml(img.description || 'Completion Image')}')"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIG5vdCBmb3VuZDwvdGV4dD48L3N2Zz4='">
                                <div class="card-body p-2">
                                    <small class="text-muted">${escapeHtml(img.description || 'Completion Image')}</small>
                                    <br><small class="text-muted">${new Date(img.created_at).toLocaleDateString()}</small>
                                </div>
                            </div>
                        </div>
                    `;
            }).join('')}
            </div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                Click on any image to view it in full size.
            </div>
        </div>
    `;
        } else if (request.status === 'completed') {
            imagesHtml = `
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No completion images available for this completed request.
            </div>
        </div>
    `;
        }

        // Build cancellation history HTML
        let cancellationHtml = '';
        if (cancellations && cancellations.length > 0) {
            cancellationHtml = `
            <div class="col-12">
                <h6 class="mb-3"><i class="fas fa-undo-alt"></i> Cancellation History 
                    <span class="badge bg-warning">${cancellations.length}</span>
                </h6>
                <div class="timeline">
                    ${cancellations.map(cancel => `
                        <div class="alert alert-warning">
                            <small class="text-muted float-end">${new Date(cancel.cancelled_at).toLocaleString()}</small>
                            <strong>Cancelled by:</strong> ${escapeHtml(cancel.first_name + ' ' + (cancel.last_name || ''))}
                            <br><strong>Reason:</strong> ${escapeHtml(cancel.notes)}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        }

        // Staff information using your actual field names
        const staffInfo = request.staff_first_name ?
            `${request.staff_first_name} ${request.staff_last_name || ''}` : '—';

        const staffContact = request.staff_phone || request.staff_email ?
            `<br><small class="text-muted">${request.staff_phone || ''} ${request.staff_email || ''}</small>` : '';

        content.innerHTML = `
        <div class="row g-4">
            <!-- Request Information -->
            <div class="col-md-6">
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> Request Information</h6>
                <table class="table table-sm table-borderless">
                    <tr><td><strong>ID:</strong></td><td>#${request.id}</td></tr>
                    <tr><td><strong>Created:</strong></td><td>${createdDate}</td></tr>
                    <tr><td><strong>Title:</strong></td><td>${escapeHtml(request.title)}</td></tr>
                    <tr><td><strong>Property:</strong></td><td>${escapeHtml(request.property_name)}</td></tr>
                    <tr><td><strong>Unit:</strong></td><td>${escapeHtml(request.unit_name || 'N/A')}</td></tr>
                    <tr><td><strong>Priority:</strong></td><td>${getPriorityBadge(request.priority)}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${getStatusBadge(request.status)}</td></tr>
                    ${request.cancel_count > 0 ? `<tr><td><strong>Cancel Count:</strong></td><td><span class="badge bg-warning">${request.cancel_count}</span></td></tr>` : ''}
                    ${request.created_by_landlord ? `<tr><td><strong>Created By:</strong></td><td><span class="badge bg-info">Landlord</span></td></tr>` : ''}
                </table>
            </div>
            
            <!-- Status & Cost Information -->  
            <div class="col-md-6">
                <h6 class="mb-3"><i class="fas fa-chart-line"></i> Progress & Costs</h6>
                <table class="table table-sm table-borderless">
                    ${request.estimated_cost ? `<tr><td><strong>Estimated Cost:</strong></td><td>${formatCurrency(request.estimated_cost)}</td></tr>` : ''}
                    ${request.approved_cost ? `<tr><td><strong>Approved Cost:</strong></td><td>${formatCurrency(request.approved_cost)}</td></tr>` : ''}
                    ${request.actual_cost ? `<tr><td><strong>Actual Cost:</strong></td><td>${formatCurrency(request.actual_cost)}</td></tr>` : ''}
                    ${request.assigned_date ? `<tr><td><strong>Assigned:</strong></td><td>${assignedDate}</td></tr>` : ''}
                    ${request.approved_date ? `<tr><td><strong>Approved:</strong></td><td>${approvedDate}</td></tr>` : ''}
                    ${request.completed_date ? `<tr><td><strong>Completed:</strong></td><td>${completedDate}</td></tr>` : ''}
                    ${request.rejected_date ? `<tr><td><strong>Rejected:</strong></td><td>${rejectedDate}</td></tr>` : ''}
                    ${staffInfo !== '—' ? `<tr><td><strong>Assigned Staff:</strong></td><td>${staffInfo}${staffContact}</td></tr>` : ''}
                    ${request.approved_by_name ? `<tr><td><strong>Approved By:</strong></td><td>${escapeHtml(request.approved_by_name)}</td></tr>` : ''}
                </table>
            </div>
            
            <!-- Description -->
            <div class="col-12">
                <h6 class="mb-3"><i class="fas fa-file-text"></i> Description</h6>
                <div class="bg-light p-3 rounded">
                    ${escapeHtml(request.description).replace(/\n/g, '<br>')}
                </div>
            </div>
            
            <!-- Work Notes -->
            ${request.work_notes ? `
            <div class="col-12">
                <h6 class="mb-3"><i class="fas fa-sticky-note"></i> Work Notes</h6>
                <div class="bg-info bg-opacity-10 p-3 rounded">
                    ${escapeHtml(request.work_notes).replace(/\n/g, '<br>')}
                </div>
            </div>
            ` : ''}
            
            <!-- Materials Used -->
            ${request.materials_used ? `
            <div class="col-12">
                <h6 class="mb-3"><i class="fas fa-tools"></i> Materials Used</h6>
                <div class="bg-success bg-opacity-10 p-3 rounded">
                    ${escapeHtml(request.materials_used).replace(/\n/g, '<br>')}
                </div>
            </div>
            ` : ''}
            
            <!-- Rejection Reason -->
            ${request.rejection_reason ? `
            <div class="col-12">
                <h6 class="mb-3"><i class="fas fa-times-circle"></i> Rejection Reason</h6>
                <div class="bg-danger bg-opacity-10 p-3 rounded">
                    ${escapeHtml(request.rejection_reason).replace(/\n/g, '<br>')}
                </div>
            </div>
            ` : ''}
            
            <!-- Cancellation History -->
            ${cancellationHtml}
            
            <!-- Images Section -->
            ${imagesHtml}
        </div>
    `;
    }

    function deleteRequest(requestId) {
        console.log('Attempting to delete request ID:', requestId);

        // Get request status from table row
        const row = document.querySelector(`tr[data-id="${requestId}"]`);
        if (!row) {
            showAlert('danger', 'Request not found');
            return;
        }

        // FIXED: Get status specifically from the Status column badge
        const statusCell = row.cells[5]; // Status column is the 6th column (index 5)
        const statusBadge = statusCell.querySelector('.badge');
        let status = '';

        if (statusBadge) {
            status = statusBadge.textContent.toLowerCase().trim();
            console.log('Status badge text:', statusBadge.textContent);
            console.log('Cleaned status:', status);
        } else {
            console.log('No status badge found in status cell');
            showAlert('danger', 'Could not determine request status');
            return;
        }

        // Check if status is pending
        if (!status.includes('pending')) {
            showAlert('warning', `Only pending requests can be deleted. Current status: "${status}"`);
            return;
        }

        if (!confirm('Delete this maintenance request? This cannot be undone.')) {
            return;
        }

        // Build URL manually for localhost
        const fullUrl = `https://www.tab3ni.online/landlord/landlord/delete-maintenance-request/${requestId}`;
        console.log('DELETE URL:', fullUrl);

        // Get CSRF token
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="csrf_test_name"]')?.value;

        fetch(fullUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                csrf_test_name: csrf
            })
        })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response URL:', response.url);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showAlert('success', 'Maintenance request deleted successfully.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', data.message || 'Failed to delete request.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlert('danger', 'An error occurred while deleting the request: ' + error.message);
            });
    }

    // Helper functions remain the same
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function showImageModal(imageSrc, description) {
        const imageModal = `
        <div class="modal fade" id="imageViewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-image"></i> ${escapeHtml(description)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageSrc}" class="img-fluid" alt="Completion Image">
                    </div>
                    <div class="modal-footer">
                        <a href="${imageSrc}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Remove existing image modal if any
        const existingModal = document.getElementById('imageViewModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', imageModal);

        // Show the modal
        new bootstrap.Modal(document.getElementById('imageViewModal')).show();
    }

    function showAlert(type, message) {
        // Remove existing alerts
        document.querySelectorAll('.alert-dynamic').forEach(el => el.remove());

        const div = document.createElement('div');
        div.className = `alert alert-${type} alert-dismissible fade show alert-dynamic mb-4`;
        div.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> 
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        const container = document.querySelector('.container-fluid');
        const header = container.querySelector('.d-sm-flex.align-items-center.justify-content-between.mb-4');

        if (header) {
            header.insertAdjacentElement('afterend', div);
        } else {
            container.insertBefore(div, container.firstChild);
        }

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (div && div.parentNode) {
                div.remove();
            }
        }, 5000);

        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

<style>
    .completion-image {
        transition: transform 0.2s ease;
    }

    .completion-image:hover {
        transform: scale(1.05);
    }

    .modal-xl {
        max-width: 90%;
    }

    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 1140px;
        }
    }

    /* Status indicators */
    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
    }

    /* Enhanced table styling */
    .table-borderless td {
        border: none;
        padding: 0.25rem 0.75rem 0.25rem 0;
    }

    .table-borderless td:first-child {
        width: 140px;
        color: #6c757d;
    }

    /* Image gallery styling */
    .card img {
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>

<?= $this->endSection() ?>