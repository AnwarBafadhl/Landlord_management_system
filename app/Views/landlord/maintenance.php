<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Maintenance Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Maintenance Management
        </h1>
        <div>
            <button class="btn btn-success" onclick="showAddMaintenanceModal()">
                <i class="fas fa-plus"></i> Add Request
            </button>
            <button class="btn btn-outline-primary" onclick="exportMaintenanceReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Enhanced Status Overview Cards -->
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
                                Ready to start
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-thumbs-up fa-2x text-primary"></i>
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
                                Currently working
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-spin fa-2x text-info"></i>
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
                                Completed This Month
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['completed_count'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                $<?= number_format($stats['total_cost'] ?? 0, 0) ?> total cost
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter Maintenance Requests
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= current_url() ?>" id="filterForm">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>
                                <i class="fas fa-clock"></i> Pending
                            </option>
                            <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>
                                <i class="fas fa-thumbs-up"></i> Approved
                            </option>
                            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>
                                <i class="fas fa-cog"></i> In Progress
                            </option>
                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                                <i class="fas fa-check"></i> Completed
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="">All Priorities</option>
                            <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="property" class="form-label">Property</label>
                        <select class="form-control" id="property" name="property">
                            <option value="">All Properties</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>" <?= ($filters['property'] ?? '') == $property['id'] ? 'selected' : '' ?>>
                                        <?= esc($property['property_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select class="form-control" id="date_range" name="date_range">
                            <option value="">All Time</option>
                            <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= ($filters['date_range'] ?? '') === 'week' ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= ($filters['date_range'] ?? '') === 'month' ? 'selected' : '' ?>>This Month</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Maintenance Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Maintenance Requests
            </h6>
            <div>
                <span class="badge badge-primary"><?= count($maintenance_requests ?? []) ?> requests</span>
                <button class="btn btn-sm btn-outline-primary ms-2" onclick="bulkActions()">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($maintenance_requests)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0" id="maintenanceTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Request #</th>
                                <th>Property & Location</th>
                                <th>Issue Details</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Timeline</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance_requests as $request): ?>
                                <tr id="request-<?= $request['id'] ?>" class="<?= ($request['priority'] ?? '') === 'urgent' ? 'table-danger' : '' ?>">
                                    <td>
                                        <input type="checkbox" class="request-checkbox" value="<?= $request['id'] ?>">
                                    </td>
                                    <td>
                                        <strong class="text-primary">#<?= str_pad($request['id'] ?? 0, 4, '0', STR_PAD_LEFT) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= !empty($request['created_at']) ? date('M j, Y', strtotime($request['created_at'])) : 'N/A' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="property-icon me-2">
                                                <i class="fas fa-building text-primary"></i>
                                            </div>
                                            <div>
                                                <strong><?= esc($request['property_name'] ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($request['property_address'] ?? '') ?></small>
                                                <?php if (!empty($request['unit_number'])): ?>
                                                    <br>
                                                    <small class="text-info">Unit: <?= esc($request['unit_number']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc($request['title'] ?? 'No Title') ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= esc(substr($request['description'] ?? '', 0, 100)) ?>
                                                <?= strlen($request['description'] ?? '') > 100 ? '...' : '' ?>
                                            </small>
                                            <?php if (!empty($request['tenant_name'])): ?>
                                                <br>
                                                <small class="text-info">
                                                    <i class="fas fa-user"></i> Reported by: <?= esc($request['tenant_name']) ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($request['category'])): ?>
                                                <br>
                                                <span class="badge badge-light">
                                                    <i class="fas fa-tag"></i> <?= ucfirst($request['category']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $priority = $request['priority'] ?? 'medium';
                                        $priorityConfig = [
                                            'urgent' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'URGENT'],
                                            'high' => ['class' => 'warning', 'icon' => 'arrow-up', 'text' => 'High'],
                                            'medium' => ['class' => 'info', 'icon' => 'minus', 'text' => 'Medium'],
                                            'low' => ['class' => 'secondary', 'icon' => 'arrow-down', 'text' => 'Low']
                                        ];
                                        $config = $priorityConfig[$priority] ?? $priorityConfig['medium'];
                                        ?>
                                        <span class="badge badge-<?= $config['class'] ?> badge-pill">
                                            <i class="fas fa-<?= $config['icon'] ?>"></i> <?= $config['text'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $request['status'] ?? 'pending';
                                        $statusConfig = [
                                            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending'],
                                            'approved' => ['class' => 'primary', 'icon' => 'thumbs-up', 'text' => 'Approved'],
                                            'in_progress' => ['class' => 'info', 'icon' => 'cog', 'text' => 'In Progress'],
                                            'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Completed']
                                        ];
                                        $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                        ?>
                                        <span class="badge badge-<?= $config['class'] ?>">
                                            <i class="fas fa-<?= $config['icon'] ?>"></i> <?= $config['text'] ?>
                                        </span>
                                        <?php if (!empty($request['assigned_to'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-user-tie"></i> <?= esc($request['assigned_to']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($request['completion_date']) && $status === 'completed'): ?>
                                            <br>
                                            <small class="text-success">
                                                <i class="fas fa-calendar-check"></i> <?= date('M j', strtotime($request['completion_date'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <strong>Requested:</strong><br>
                                            <?= !empty($request['requested_date']) ? date('M j, Y', strtotime($request['requested_date'])) : 'N/A' ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php
                                                if (!empty($request['requested_date'])) {
                                                    $datetime = strtotime($request['requested_date']);
                                                    $now = time();
                                                    $diff = $now - $datetime;
                                                    $days = floor($diff / (60 * 60 * 24));
                                                    if ($days == 0) {
                                                        echo 'Today';
                                                    } elseif ($days == 1) {
                                                        echo 'Yesterday';
                                                    } else {
                                                        echo $days . ' days ago';
                                                    }
                                                }
                                                ?>
                                            </small>
                                            <?php if (!empty($request['scheduled_date']) && $status !== 'completed'): ?>
                                                <br>
                                                <strong class="text-primary">Scheduled:</strong><br>
                                                <?= date('M j, Y', strtotime($request['scheduled_date'])) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($request['cost']) && $request['cost'] > 0): ?>
                                            <strong class="text-success">$<?= number_format($request['cost'], 2) ?></strong>
                                            <?php if (!empty($request['estimated_cost']) && $request['estimated_cost'] != $request['cost']): ?>
                                                <br>
                                                <small class="text-muted">Est: $<?= number_format($request['estimated_cost'], 2) ?></small>
                                            <?php endif; ?>
                                        <?php elseif (!empty($request['estimated_cost']) && $request['estimated_cost'] > 0): ?>
                                            <span class="text-info">Est: $<?= number_format($request['estimated_cost'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewRequestDetails(<?= $request['id'] ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($status === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="quickApprove(<?= $request['id'] ?>)" 
                                                        title="Quick Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($status, ['pending', 'approved', 'in_progress'])): ?>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="updateStatus(<?= $request['id'] ?>)" 
                                                        title="Update Status">
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

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing <?= count($maintenance_requests) ?> of <?= $total_requests ?? count($maintenance_requests) ?> requests
                    </div>
                    <div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Pagination links would go here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Maintenance Requests Found</h5>
                    <p class="text-muted">No maintenance requests match your current filters.</p>
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="showAddMaintenanceModal()">
                            <i class="fas fa-plus"></i> Add First Request
                        </button>
                        <button class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-refresh"></i> Clear Filters
                        </button>
                    </div>
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
                    <i class="fas fa-plus-circle"></i> Add Maintenance Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMaintenanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_property_id" class="form-label">Property *</label>
                            <select class="form-control" id="add_property_id" name="property_id" required>
                                <option value="">Select Property</option>
                                <?php if (!empty($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?= $property['id'] ?>">
                                            <?= esc($property['property_name']) ?> - <?= esc($property['address'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_priority" class="form-label">Priority *</label>
                            <select class="form-control" id="add_priority" name="priority" required>
                                <option value="medium">Medium Priority</option>
                                <option value="low">Low Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_category" class="form-label">Category</label>
                            <select class="form-control" id="add_category" name="category">
                                <option value="">Select Category</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="hvac">HVAC</option>
                                <option value="appliances">Appliances</option>
                                <option value="structural">Structural</option>
                                <option value="cosmetic">Cosmetic</option>
                                <option value="security">Security</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_unit_number" class="form-label">Unit Number</label>
                            <input type="text" class="form-control" id="add_unit_number" name="unit_number" 
                                   placeholder="e.g., Apt 2B, Unit 5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_title" class="form-label">Issue Title *</label>
                        <input type="text" class="form-control" id="add_title" name="title" required 
                               placeholder="Brief description of the issue">
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Detailed Description *</label>
                        <textarea class="form-control" id="add_description" name="description" rows="4" required 
                                  placeholder="Provide detailed information about the maintenance issue"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_estimated_cost" class="form-label">Estimated Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="add_estimated_cost" name="estimated_cost" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_scheduled_date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="add_scheduled_date" name="scheduled_date" 
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="add_notes" name="notes" rows="2" 
                                  placeholder="Any additional information or special instructions"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
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
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCurrentRequest()">
                    <i class="fas fa-edit"></i> Edit Request
                </button>
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
                    <i class="fas fa-edit"></i> Update Maintenance Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status *</label>
                        <select class="form-control" id="new_status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="approved">Approved</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <input type="text" class="form-control" id="assigned_to" name="assigned_to" 
                               placeholder="Contractor/Technician name">
                    </div>
                    <div class="mb-3">
                        <label for="scheduled_date" class="form-label">Scheduled Date</label>
                        <input type="datetime-local" class="form-control" id="scheduled_date" name="scheduled_date">
                    </div>
                    <div class="mb-3" id="cost_section" style="display: none;">
                        <label for="cost" class="form-label">Total Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="cost" name="cost" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any updates or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="bulk_action" class="form-label">Select Action</label>
                    <select class="form-control" id="bulk_action" name="action">
                        <option value="">Select Action</option>
                        <option value="approve">Approve Selected</option>
                        <option value="assign">Assign Contractor</option>
                        <option value="set_priority">Set Priority</option>
                        <option value="export">Export Selected</option>
                    </select>
                </div>
                <div class="mb-3" id="bulk_assign_section" style="display: none;">
                    <label for="bulk_assigned_to" class="form-label">Assign To</label>
                    <input type="text" class="form-control" id="bulk_assigned_to" name="assigned_to" 
                           placeholder="Contractor/Technician name">
                </div>
                <div class="mb-3" id="bulk_priority_section" style="display: none;">
                    <label for="bulk_priority" class="form-label">Priority</label>
                    <select class="form-control" id="bulk_priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="alert alert-info">
                    <span id="selected_count">0</span> requests selected
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">
                    <i class="fas fa-play"></i> Execute Action
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequestId = null;
let selectedRequests = [];

// Show Add Maintenance Modal
function showAddMaintenanceModal() {
    // Reset form
    document.getElementById('addMaintenanceForm').reset();
    new bootstrap.Modal(document.getElementById('addMaintenanceModal')).show();
}

// View Request Details
function viewRequestDetails(requestId) {
    currentRequestId = requestId;
    
    // Show loading
    document.getElementById('requestDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading request details...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
    
    // Fetch request details
    fetch(`<?= site_url('landlord/maintenance-details') ?>/${requestId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('requestDetailsContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('requestDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading request details: ${error.message}
            </div>
        `;
    });
}

// Quick Approve
function quickApprove(requestId) {
    if (confirm('Are you sure you want to approve this maintenance request?')) {
        updateRequestStatus(requestId, 'approved', {}, 'Request approved successfully');
    }
}

// Update Status
function updateStatus(requestId) {
    currentRequestId = requestId;
    
    // Reset form
    document.getElementById('statusUpdateForm').reset();
    document.getElementById('cost_section').style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
}

// Update Request Status
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
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Edit Current Request
function editCurrentRequest() {
    if (currentRequestId) {
        window.location.href = `<?= site_url('landlord/edit-maintenance') ?>/${currentRequestId}`;
    }
}

// Clear Filters
function clearFilters() {
    document.getElementById('status').value = '';
    document.getElementById('priority').value = '';
    document.getElementById('property').value = '';
    document.getElementById('date_range').value = '';
    document.getElementById('filterForm').submit();
}

// Export Maintenance Report
function exportMaintenanceReport() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open(`<?= site_url('landlord/export-maintenance') ?>?${params.toString()}`, '_blank');
}

// Toggle Select All
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedRequests();
}

// Update Selected Requests
function updateSelectedRequests() {
    selectedRequests = Array.from(document.querySelectorAll('.request-checkbox:checked'))
                           .map(checkbox => parseInt(checkbox.value));
    
    document.getElementById('selected_count').textContent = selectedRequests.length;
}

// Bulk Actions
function bulkActions() {
    updateSelectedRequests();
    
    if (selectedRequests.length === 0) {
        showAlert('warning', 'Please select at least one request');
        return;
    }
    
    new bootstrap.Modal(document.getElementById('bulkActionsModal')).show();
}

// Execute Bulk Action
function executeBulkAction() {
    const action = document.getElementById('bulk_action').value;
    
    if (!action) {
        showAlert('warning', 'Please select an action');
        return;
    }
    
    if (selectedRequests.length === 0) {
        showAlert('warning', 'Please select at least one request');
        return;
    }
    
    let data = {
        action: action,
        requests: selectedRequests
    };
    
    // Add additional data based on action
    if (action === 'assign') {
        data.assigned_to = document.getElementById('bulk_assigned_to').value;
        if (!data.assigned_to) {
            showAlert('warning', 'Please enter a contractor name');
            return;
        }
    } else if (action === 'set_priority') {
        data.priority = document.getElementById('bulk_priority').value;
    }
    
    fetch(`<?= site_url('landlord/bulk-maintenance-action') ?>`, {
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
            bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
            showAlert('success', data.message);
            if (action !== 'export') {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Show/hide sections based on status selection
document.getElementById('new_status').addEventListener('change', function() {
    const costSection = document.getElementById('cost_section');
    if (this.value === 'completed') {
        costSection.style.display = 'block';
        document.getElementById('cost').required = true;
    } else {
        costSection.style.display = 'none';
        document.getElementById('cost').required = false;
    }
});

// Show/hide sections based on bulk action selection
document.getElementById('bulk_action').addEventListener('change', function() {
    const assignSection = document.getElementById('bulk_assign_section');
    const prioritySection = document.getElementById('bulk_priority_section');
    
    // Hide all sections first
    assignSection.style.display = 'none';
    prioritySection.style.display = 'none';
    
    // Show relevant section
    if (this.value === 'assign') {
        assignSection.style.display = 'block';
    } else if (this.value === 'set_priority') {
        prioritySection.style.display = 'block';
    }
});

// Form Submissions
document.getElementById('addMaintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(`<?= site_url('landlord/add-maintenance-request') ?>`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addMaintenanceModal')).hide();
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch(`<?= site_url('landlord/update-maintenance-status') ?>/${currentRequestId}`, {
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
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Auto-submit on filter change
document.querySelectorAll('#status, #priority, #property, #date_range').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Update selected requests when checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('request-checkbox')) {
        updateSelectedRequests();
    }
});

// Alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'times-circle')}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}
</script>

<style>
/* Enhanced Styling */
.hover-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-top: none;
}

.table td {
    vertical-align: middle;
    border-top: 1px solid #e3e6f0;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.075);
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    font-weight: 500;
}

.badge-pill {
    padding-left: 0.6em;
    padding-right: 0.6em;
    border-radius: 10rem;
}

.btn-group .btn {
    margin-right: 0;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.property-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fc;
    border-radius: 50%;
}

.alert.position-fixed {
    min-width: 300px;
}

.text-sm {
    font-size: 0.875rem;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

/* Status-based row highlighting */
.table tbody tr.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

/* Modal enhancements */
.modal-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.modal-title {
    color: #5a5c69;
    font-weight: 600;
}

/* Form enhancements */
.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Loading animations */
.fa-cog.fa-spin {
    animation: fa-spin 2s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
    
    .d-sm-flex {
        flex-direction: column !important;
        gap: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .property-icon {
        width: 25px;
        height: 25px;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.4rem;
        font-size: 0.7rem;
    }
}
</style>

<?= $this->endSection() ?>