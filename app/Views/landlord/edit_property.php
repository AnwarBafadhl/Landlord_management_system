<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Edit Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Edit Property
            </h1>
            <p class="text-muted mb-0">Modify property details, share structure, and manage units</p>
        </div>
        <div>
            <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <!-- FIXED: Add form tag properly -->
    <form id="editPropertyForm" method="post" action="<?= site_url('landlord/properties/update/' . $property['id']) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Enhanced Warning Notice -->
                <div class="alert alert-warning border-left-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>
                        <div>
                            <strong>Important Notice:</strong> Changes to share structure will affect all shareholders.
                            Ensure all stakeholders are informed before making modifications.
                            <div class="mt-1">
                                <small class="text-muted">
                                    Any changes to total shares or property value will automatically recalculate
                                    ownership
                                    percentages.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Edit Property Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building"></i> Property Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Basic Property Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name"
                                    value="<?= esc($property['property_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_value" class="form-label">Property Value (SAR) *</label>
                                <input type="number" class="form-control" id="property_value" name="property_value"
                                    value="<?= $property['property_value'] ?? 0 ?>" required min="1" step="0.01"
                                    onchange="calculateShareValue()">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="3"
                                required><?= esc($property['address']) ?></textarea>
                        </div>

                        <!-- Enhanced Share Information -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-info">
                                    <i class="fas fa-chart-pie"></i> Share Structure
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Share Structure Changes:</strong> Modifying the total number of shares will
                                    recalculate all ownership percentages.
                                    Current shareholders' share counts will remain the same, but their percentages may
                                    change.
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="total_shares" class="form-label">Total Number of Shares *</label>
                                        <input type="number" class="form-control" id="total_shares" name="total_shares"
                                            value="<?= $property['total_shares'] ?? 100 ?>" required min="1" max="10000"
                                            onchange="calculateShareValue(); validateShares();">
                                        <small class="text-muted">
                                            Currently allocated: <span
                                                class="text-primary font-weight-bold"><?= $totalAllocatedShares ?? 0 ?></span>
                                            shares
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="share_value" class="form-label">Share Value (SAR)</label>
                                        <input type="number" class="form-control bg-light" id="share_value"
                                            name="share_value" value="<?= $property['share_value'] ?? 0 ?>" step="0.01"
                                            readonly>
                                        <small class="text-muted">Calculated automatically: Property Value ÷ Total
                                            Shares</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="contribution_duration" class="form-label">Contribution Duration
                                            (Months) *</label>
                                        <input type="number" class="form-control" id="contribution_duration"
                                            name="contribution_duration"
                                            value="<?= $property['contribution_duration'] ?? 12 ?>" required min="1"
                                            max="360">
                                        <small class="text-muted">Period for collecting shareholder
                                            contributions</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Management Information -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-success">
                                    <i class="fas fa-building"></i> Management Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="management_company" class="form-label">Management Company</label>
                                        <input type="text" class="form-control" id="management_company"
                                            name="management_company"
                                            value="<?= esc($property['management_company'] ?? '') ?>">
                                        <small class="text-muted">Company or individual responsible for property
                                            management</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="management_percentage" class="form-label">Management Fee (%)</label>
                                        <input type="number" class="form-control" id="management_percentage"
                                            name="management_percentage"
                                            value="<?= $property['management_percentage'] ?? 0 ?>" min="0" max="50"
                                            step="0.1">
                                        <small class="text-muted">Percentage of rental income (0-50%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Changes will be saved immediately and visible to all owners.
                                        </small>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-lg" id="updateBtn">
                                            <i class="fas fa-save"></i> Update Property
                                        </button>
                                        <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>"
                                            class="btn btn-secondary btn-lg">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- NEW: Units Management Section -->
    <?php if (!empty($units) || 1): // Always show units section ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-door-open"></i> Property Units
                            <span class="badge badge-info ml-2"><?= count($units ?? []) ?></span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-info" onclick="showAddUnitModal()">
                            <i class="fas fa-plus"></i> Add Unit
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($units)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Unit Name</th>
                                            <th>Status</th>
                                            <th>Rent Amount</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($units as $unit): ?>
                                            <tr>
                                                <td><strong><?= esc($unit['unit_name']) ?></strong></td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'vacant' => 'badge-success',
                                                        'occupied' => 'badge-primary',
                                                        'maintenance' => 'badge-warning'
                                                    ];
                                                    $status = $unit['status'] ?? 'vacant';
                                                    ?>
                                                    <span class="badge <?= $statusClass[$status] ?? 'badge-secondary' ?>">
                                                        <?= ucfirst($status) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($unit['rent_amount'] > 0): ?>
                                                        SAR <?= number_format($unit['rent_amount'], 2) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= esc($unit['description'] ?? 'No description') ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            onclick="editUnit(<?= $unit['id'] ?>, '<?= esc($unit['unit_name'], 'attr') ?>', '<?= $unit['status'] ?>', <?= $unit['rent_amount'] ?? 0 ?>, '<?= esc($unit['description'] ?? '', 'attr') ?>')"
                                                            title="Edit Unit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="confirmRemoveUnit(<?= $unit['id'] ?>, '<?= esc($unit['unit_name'], 'attr') ?>')"
                                                            title="Remove Unit">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-door-open fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No Units Added Yet</h6>
                                <p class="text-muted">Add units to manage rental spaces</p>
                                <button type="button" class="btn btn-info" onclick="showAddUnitModal()">
                                    <i class="fas fa-plus"></i> Add First Unit
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Shareholders Section -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (!empty($owners) && is_array($owners)): ?>
                <div class="card bg-light mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-users"></i> Current Shareholders
                            <span class="badge badge-success ml-2"><?= count($owners) ?></span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="showAddOwnerModal()">
                            <i class="fas fa-user-plus"></i> Add Shareholder
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-calculator"></i>
                            <strong>Impact Preview:</strong> The "New %" column shows how ownership percentages
                            will change with the updated total shares.
                            Changes are highlighted in different colors.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Shareholder Name</th>
                                        <th>Email</th>
                                        <th>Shares</th>
                                        <th>Current %</th>
                                        <th>New %</th>
                                        <th>Investment Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ownersPreviewTable">
                                    <?php foreach ($owners as $owner): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?= esc($owner['name']) ?>
                                                    <?php if ($owner['is_current_user'] ?? false): ?>
                                                        <span class="badge badge-primary badge-sm ml-1">You</span>
                                                    <?php endif; ?>
                                                    <?php if (($owner['is_primary_owner'] ?? 0) == 1): ?>
                                                        <span class="badge badge-warning badge-sm ml-1">Primary</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?= esc($owner['email'] ?? $owner['owner_email'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= number_format($owner['shares'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td class="current-percentage">
                                                <?= number_format($owner['ownership_percentage'] ?? 0, 2) ?>%
                                            </td>
                                            <td class="new-percentage text-primary font-weight-bold"
                                                data-shares="<?= $owner['shares'] ?? 0 ?>">
                                                <?= number_format($owner['ownership_percentage'] ?? 0, 2) ?>%
                                            </td>
                                            <td class="investment-value" data-shares="<?= $owner['shares'] ?? 0 ?>">
                                                SAR
                                                <?= number_format(($owner['shares'] ?? 0) * ($property['share_value'] ?? 0), 0) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="editOwnerShares(<?= $owner['id'] ?? 0 ?>, '<?= esc($owner['name'], 'attr') ?>', <?= $owner['shares'] ?? 0 ?>, <?= $owner['is_current_user'] ?? 0 ? 'true' : 'false' ?>)"
                                                        title="Edit Shares">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (!($owner['is_current_user'] ?? false) && ($owner['is_primary_owner'] ?? 0) != 1): ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="removeOwner(<?= $owner['id'] ?? 0 ?>, '<?= esc($owner['name'], 'attr') ?>')"
                                                            title="Remove Owner">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th>
                                            <span class="badge badge-primary">
                                                <?= number_format($totalAllocatedShares ?? 0) ?>
                                            </span>
                                        </th>
                                        <th colspan="2">
                                            Available: <span
                                                id="availableSharesDisplay"><?= number_format(($property['total_shares'] ?? 100) - ($totalAllocatedShares ?? 0)) ?></span>
                                            shares
                                        </th>
                                        <th colspan="2">
                                            SAR
                                            <?= number_format(($totalAllocatedShares ?? 0) * ($property['share_value'] ?? 0), 0) ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-light mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-users"></i> Shareholders
                        </h6>
                    </div>
                    <div class="card-body text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No Shareholders Added Yet</h6>
                        <p class="text-muted">Add shareholders to manage property ownership</p>
                        <button type="button" class="btn btn-success" onclick="showAddOwnerModal()">
                            <i class="fas fa-user-plus"></i> Add First Shareholder
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Owner Modal -->
<div class="modal fade" id="addOwnerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New Shareholder
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addOwnerForm" method="post"
                action="<?= site_url('landlord/properties/add-owner/' . $property['id']) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> The new shareholder will be linked using their email address.
                        They will receive an invitation if they don't have an account.
                    </div>

                    <div class="mb-3">
                        <label for="owner_name" class="form-label">Shareholder Name *</label>
                        <input type="text" class="form-control" id="owner_name" name="owner_name" required
                            placeholder="Enter full name">
                    </div>

                    <div class="mb-3">
                        <label for="owner_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="owner_email" name="owner_email" required
                            placeholder="Enter email address">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="owner_shares" class="form-label">Number of Shares *</label>
                            <input type="number" class="form-control" id="owner_shares" name="owner_shares" required
                                min="1" placeholder="Enter shares" onchange="calculateModalOwnership()">
                            <small class="text-muted" id="availableSharesText">
                                Available:
                                <?= number_format(($property['total_shares'] ?? 100) - ($totalAllocatedShares ?? 0)) ?>
                                shares
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ownership %</label>
                            <input type="text" class="form-control bg-light" id="modal_ownership_percentage" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Investment Value</label>
                        <input type="text" class="form-control bg-light" id="modal_investment_value" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add Shareholder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Owner Modal -->
<div class="modal fade" id="editOwnerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Shareholder Shares
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editOwnerForm" method="post">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_owner_id" name="owner_id">
                <input type="hidden" id="edit_original_shares" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shareholder Name</label>
                        <input type="text" class="form-control bg-light" id="edit_owner_name" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_owner_shares" class="form-label">Number of Shares *</label>
                            <input type="number" class="form-control" id="edit_owner_shares" name="shares" required
                                min="1" onchange="calculateEditModalOwnership()"
                                oninput="calculateEditModalOwnership()">
                            <small class="text-muted" id="editAvailableSharesText">
                                Available for editing...
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Ownership %</label>
                            <input type="text" class="form-control bg-light" id="edit_modal_ownership_percentage"
                                readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Investment Value</label>
                        <input type="text" class="form-control bg-light" id="edit_modal_investment_value" readonly>
                    </div>

                    <div class="alert alert-warning" id="editSharesWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> <span id="editWarningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editOwnerSubmitBtn">
                        <i class="fas fa-save"></i> Update Shares
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NEW: Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Unit
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addUnitForm" method="post"
                action="<?= site_url('landlord/properties/add-unit/' . $property['id']) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="unit_name" class="form-label">Unit Name *</label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name" required
                            placeholder="e.g., Unit 1A, Apt 101, etc.">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_status" class="form-label">Status</label>
                            <select class="form-control" id="unit_status" name="unit_status">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Under Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rent_amount" class="form-label">Rent Amount (SAR)</label>
                            <input type="number" class="form-control" id="rent_amount" name="rent_amount" min="0"
                                step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="unit_description" name="unit_description" rows="2"
                            placeholder="Optional description of the unit"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-plus"></i> Add Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NEW: Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Unit
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editUnitForm" method="post">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_unit_id" name="unit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_unit_name" class="form-label">Unit Name *</label>
                        <input type="text" class="form-control" id="edit_unit_name" name="unit_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_unit_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_unit_status" name="unit_status">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Under Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_rent_amount" class="form-label">Rent Amount (SAR)</label>
                            <input type="number" class="form-control" id="edit_rent_amount" name="rent_amount" min="0"
                                step="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_unit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_unit_description" name="unit_description"
                            rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Enhanced JavaScript with Units Management + FIXED Form Actions

    // Global variables
    let currentPropertyTotalShares = <?= $property['total_shares'] ?? 100 ?>;
    let currentPropertyShareValue = <?= $property['share_value'] ?? 0 ?>;
    let currentAllocatedShares = <?= $totalAllocatedShares ?? 0 ?>;
    let currentPropertyId = <?= $property['id'] ?? 0 ?>;

    // Helper function to close modals
    function closeModal(modalId) {
        try {
            const modal = safeGetElement(modalId);
            if (modal) {
                if (typeof bootstrap !== 'undefined') {
                    const bootstrapModal = bootstrap.Modal.getInstance(modal);
                    if (bootstrapModal) {
                        bootstrapModal.hide();
                    } else {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        modal.style.backgroundColor = '';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    }
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('hide');
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    modal.style.backgroundColor = '';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            }
        } catch (error) {
            console.error('Error closing modal:', error);
        }
    }

    // Helper functions
    function safeGetElement(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Element with id '${id}' not found`);
        }
        return element;
    }

    function safeSetValue(id, value) {
        const element = safeGetElement(id);
        if (element) {
            element.value = value;
            return true;
        }
        return false;
    }

    function safeSetText(id, text) {
        const element = safeGetElement(id);
        if (element) {
            element.textContent = text;
            return true;
        }
        return false;
    }

    // Unit Management Functions
    function showAddUnitModal() {
        try {
            const addUnitForm = safeGetElement('addUnitForm');
            if (addUnitForm) {
                addUnitForm.reset();
                // FIXED: Correct route for add unit
                addUnitForm.action = `<?= site_url('landlord/properties/add-unit/' . $property['id']) ?>`;
            }

            const modal = safeGetElement('addUnitModal');
            if (modal) {
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modal).show();
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('show');
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    document.body.classList.add('modal-open');
                }
            }
        } catch (error) {
            console.error('Error in showAddUnitModal:', error);
            alert('Error opening add unit modal. Please refresh the page and try again.');
        }
    }

    function editUnit(unitId, unitName, status, rentAmount, description) {
        try {
            safeSetValue('edit_unit_id', unitId);
            safeSetValue('edit_unit_name', unitName);
            safeSetValue('edit_unit_status', status);
            safeSetValue('edit_rent_amount', rentAmount);
            safeSetValue('edit_unit_description', description);

            const editUnitForm = safeGetElement('editUnitForm');
            if (editUnitForm) {
                // FIXED: Correct route for update unit
                editUnitForm.action = `<?= site_url('landlord/properties/update-unit/' . $property['id']) ?>/${unitId}`;
            }

            const modal = safeGetElement('editUnitModal');
            if (modal) {
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modal).show();
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('show');
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                }
            }
        } catch (error) {
            console.error('Error in editUnit:', error);
            alert('Error opening edit unit modal. Please refresh the page and try again.');
        }
    }

    function confirmRemoveUnit(unitId, unitName) {
        try {
            if (confirm(`Are you sure you want to remove unit "${unitName}"? This action cannot be undone.`)) {
                // Show loading state
                const btn = event.target.closest('button');
                if (btn) {
                    const originalContent = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled = true;
                }

                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                // FIXED: Correct route for remove unit
                form.action = `<?= site_url('landlord/properties/remove-unit/' . $property['id']) ?>/${unitId}`;

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '<?= csrf_token() ?>';
                csrfField.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        } catch (error) {
            console.error('Error in confirmRemoveUnit:', error);
            alert('Error removing unit. Please refresh the page and try again.');
        }
    }

    // Shareholder functions
    function showAddOwnerModal() {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const totalShares = totalSharesElement ? parseInt(totalSharesElement.value) || currentPropertyTotalShares : currentPropertyTotalShares;
            const availableShares = totalShares - currentAllocatedShares;

            if (availableShares <= 0) {
                alert('No shares available. All shares have been allocated. Please increase total shares first or remove some existing shareholders.');
                return;
            }

            const addOwnerForm = safeGetElement('addOwnerForm');
            if (addOwnerForm) {
                addOwnerForm.reset();
                // FIXED: Correct route for add owner
                addOwnerForm.action = `<?= site_url('landlord/properties/add-owner/' . $property['id']) ?>`;
            }

            safeSetValue('modal_ownership_percentage', '');
            safeSetValue('modal_investment_value', '');

            const ownerSharesElement = safeGetElement('owner_shares');
            if (ownerSharesElement) {
                ownerSharesElement.max = availableShares;
                ownerSharesElement.min = 1;
                ownerSharesElement.value = '';
            }

            safeSetText('availableSharesText', `Available: ${availableShares.toLocaleString()} shares`);

            const modal = safeGetElement('addOwnerModal');
            if (modal) {
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modal).show();
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('show');
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    document.body.classList.add('modal-open');
                }
            }
        } catch (error) {
            console.error('Error in showAddOwnerModal:', error);
            alert('Error opening add owner modal. Please refresh the page and try again.');
        }
    }

    function calculateModalOwnership() {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const shareValueElement = safeGetElement('share_value');
            const ownerSharesElement = safeGetElement('owner_shares');

            const totalShares = totalSharesElement ? parseInt(totalSharesElement.value) || currentPropertyTotalShares : currentPropertyTotalShares;
            const shareValue = shareValueElement ? parseFloat(shareValueElement.value) || currentPropertyShareValue : currentPropertyShareValue;
            const ownerShares = ownerSharesElement ? parseInt(ownerSharesElement.value) || 0 : 0;

            if (ownerShares > 0 && totalShares > 0) {
                const percentage = (ownerShares / totalShares) * 100;
                const investment = ownerShares * shareValue;

                safeSetValue('modal_ownership_percentage', percentage.toFixed(2) + '%');
                safeSetValue('modal_investment_value', 'SAR ' + investment.toLocaleString());
            } else {
                safeSetValue('modal_ownership_percentage', '0.00%');
                safeSetValue('modal_investment_value', 'SAR 0');
            }
        } catch (error) {
            console.error('Error in calculateModalOwnership:', error);
        }
    }

    function editOwnerShares(ownerId, ownerName, currentShares, isCurrentUser) {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const totalShares = totalSharesElement ? parseInt(totalSharesElement.value) || currentPropertyTotalShares : currentPropertyTotalShares;
            const availableShares = totalShares - currentAllocatedShares + currentShares;

            safeSetValue('edit_owner_id', ownerId);
            safeSetValue('edit_owner_name', ownerName);
            safeSetValue('edit_owner_shares', currentShares);
            safeSetValue('edit_original_shares', currentShares);

            const editSharesElement = safeGetElement('edit_owner_shares');
            if (editSharesElement) {
                editSharesElement.min = 1;
                editSharesElement.max = availableShares;
            }

            safeSetText('editAvailableSharesText',
                `You can allocate up to ${availableShares.toLocaleString()} shares (including current ${currentShares} shares)`);

            const editOwnerForm = safeGetElement('editOwnerForm');
            if (editOwnerForm) {
                // FIXED: Correct route for update owner
                editOwnerForm.action = `<?= site_url('landlord/properties/update-owner/' . $property['id']) ?>/${ownerId}`;
            }

            calculateEditModalOwnership();

            const modal = safeGetElement('editOwnerModal');
            if (modal) {
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modal).show();
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('show');
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    document.body.classList.add('modal-open');
                }
            }
        } catch (error) {
            console.error('Error in editOwnerShares:', error);
            alert('Error opening edit modal. Please refresh the page and try again.');
        }
    }

    function calculateEditModalOwnership() {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const shareValueElement = safeGetElement('share_value');
            const editSharesElement = safeGetElement('edit_owner_shares');

            const totalShares = totalSharesElement ? parseInt(totalSharesElement.value) || currentPropertyTotalShares : currentPropertyTotalShares;
            const shareValue = shareValueElement ? parseFloat(shareValueElement.value) || currentPropertyShareValue : currentPropertyShareValue;
            const ownerShares = editSharesElement ? parseInt(editSharesElement.value) || 0 : 0;
            const maxAllowed = editSharesElement ? parseInt(editSharesElement.max) || 0 : 0;

            const percentage = (ownerShares / totalShares) * 100;
            const investment = ownerShares * shareValue;

            safeSetValue('edit_modal_ownership_percentage', percentage.toFixed(2) + '%');
            safeSetValue('edit_modal_investment_value', 'SAR ' + investment.toLocaleString());

            const warningDiv = safeGetElement('editSharesWarning');
            const warningText = safeGetElement('editWarningText');
            const submitBtn = safeGetElement('editOwnerSubmitBtn');

            if (warningDiv && warningText && submitBtn) {
                if (ownerShares > maxAllowed) {
                    warningText.textContent = `You can only allocate up to ${maxAllowed.toLocaleString()} shares.`;
                    warningDiv.style.display = 'block';
                    submitBtn.disabled = true;
                } else if (ownerShares < 1) {
                    warningText.textContent = 'Shares must be at least 1.';
                    warningDiv.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    warningDiv.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error in calculateEditModalOwnership:', error);
        }
    }

    function removeOwner(ownerId, ownerName) {
        try {
            if (confirm(`Are you sure you want to remove "${ownerName}" from this property? This action cannot be undone and will redistribute ownership percentages.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                // FIXED: Correct route for remove owner
                form.action = `<?= site_url('landlord/properties/remove-owner/' . $property['id']) ?>/${ownerId}`;

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '<?= csrf_token() ?>';
                csrfField.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        } catch (error) {
            console.error('Error in removeOwner:', error);
            alert('Error removing owner. Please refresh the page and try again.');
        }
    }

    function calculateShareValue() {
        try {
            const propertyValueElement = safeGetElement('property_value');
            const totalSharesElement = safeGetElement('total_shares');
            const shareValueElement = safeGetElement('share_value');

            if (propertyValueElement && totalSharesElement && shareValueElement) {
                const propertyValue = parseFloat(propertyValueElement.value) || 0;
                const totalShares = parseInt(totalSharesElement.value) || 1;
                const shareValue = propertyValue / totalShares;

                shareValueElement.value = shareValue.toFixed(2);
                currentPropertyShareValue = shareValue;

                updatePreviewTable();
            }
        } catch (error) {
            console.error('Error in calculateShareValue:', error);
        }
    }

    function updatePreviewTable() {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const shareValueElement = safeGetElement('share_value');

            if (!totalSharesElement || !shareValueElement) return;

            const totalShares = parseInt(totalSharesElement.value) || currentPropertyTotalShares;
            const shareValue = parseFloat(shareValueElement.value) || currentPropertyShareValue;

            const newPercentageCells = document.querySelectorAll('.new-percentage');
            newPercentageCells.forEach(cell => {
                const shares = parseInt(cell.getAttribute('data-shares')) || 0;
                const newPercentage = (shares / totalShares) * 100;
                cell.textContent = newPercentage.toFixed(2) + '%';

                const currentCell = cell.parentNode.querySelector('.current-percentage');
                if (currentCell) {
                    const currentPercentage = parseFloat(currentCell.textContent);
                    const percentageDiff = Math.abs(newPercentage - currentPercentage);

                    cell.classList.remove('text-success', 'text-warning', 'text-danger');
                    if (percentageDiff > 5) {
                        cell.classList.add('text-danger');
                    } else if (percentageDiff > 1) {
                        cell.classList.add('text-warning');
                    } else if (percentageDiff > 0.1) {
                        cell.classList.add('text-success');
                    }
                }
            });

            const investmentCells = document.querySelectorAll('.investment-value');
            investmentCells.forEach(cell => {
                const shares = parseInt(cell.getAttribute('data-shares')) || 0;
                const investment = shares * shareValue;
                cell.textContent = 'SAR ' + investment.toLocaleString();
            });

            const availableShares = totalShares - currentAllocatedShares;
            safeSetText('availableSharesDisplay', availableShares.toLocaleString());

        } catch (error) {
            console.error('Error in updatePreviewTable:', error);
        }
    }

    function validateShares() {
        try {
            const totalSharesElement = safeGetElement('total_shares');
            const updateBtn = safeGetElement('updateBtn');

            if (!totalSharesElement) return;

            const totalShares = parseInt(totalSharesElement.value) || 0;
            const allocatedShares = currentAllocatedShares;

            if (totalShares < allocatedShares) {
                totalSharesElement.classList.add('is-invalid');
                if (updateBtn) updateBtn.disabled = true;

                let errorDiv = safeGetElement('shares-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'shares-error';
                    errorDiv.className = 'invalid-feedback';
                    totalSharesElement.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = `Total shares cannot be less than allocated shares (${allocatedShares}).`;
            } else {
                totalSharesElement.classList.remove('is-invalid');
                const errorDiv = safeGetElement('shares-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
                if (updateBtn) updateBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error in validateShares:', error);
        }
    }

    // Enhanced form submissions
    document.addEventListener('DOMContentLoaded', function () {
        try {
            console.log('Initializing edit property page for Property ID:', currentPropertyId);

            // Wire up modal close buttons and backdrop clicks
            const modals = ['addOwnerModal', 'editOwnerModal', 'addUnitModal', 'editUnitModal'];
            modals.forEach(modalId => {
                const modal = safeGetElement(modalId);
                if (modal) {
                    // Close button handlers
                    const closeButtons = modal.querySelectorAll('[data-dismiss="modal"], .close');
                    closeButtons.forEach(btn => {
                        btn.addEventListener('click', function () {
                            closeModal(modalId);
                        });
                    });

                    // Backdrop click handler
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) {
                            closeModal(modalId);
                        }
                    });

                    // Escape key handler
                    modal.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            closeModal(modalId);
                        }
                    });
                }
            });

            // FIXED: Main property form submission
            const editPropertyForm = safeGetElement('editPropertyForm');
            if (editPropertyForm) {
                console.log('Main property form found, setting up event listener');
                editPropertyForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    console.log('Main property form submitted');

                    const updateBtn = safeGetElement('updateBtn');
                    if (updateBtn) {
                        const originalText = updateBtn.innerHTML;
                        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        updateBtn.disabled = true;

                        // Validate shares
                        const totalSharesElement = safeGetElement('total_shares');
                        if (totalSharesElement) {
                            const totalShares = parseInt(totalSharesElement.value) || 0;
                            const allocatedShares = currentAllocatedShares;

                            if (totalShares < allocatedShares) {
                                alert(`Total shares cannot be less than allocated shares (${allocatedShares}).`);
                                updateBtn.innerHTML = originalText;
                                updateBtn.disabled = false;
                                return;
                            }
                        }

                        console.log('Submitting main property form to:', this.action);
                        this.submit();
                    }
                });
            } else {
                console.warn('Main property form not found');
            }

            // Add owner form submission
            const addOwnerForm = safeGetElement('addOwnerForm');
            if (addOwnerForm) {
                addOwnerForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                        submitBtn.disabled = true;

                        const sharesElement = safeGetElement('owner_shares');
                        const totalSharesElement = safeGetElement('total_shares');

                        if (sharesElement && totalSharesElement) {
                            const shares = parseInt(sharesElement.value);
                            const totalShares = parseInt(totalSharesElement.value) || currentPropertyTotalShares;
                            const availableShares = totalShares - currentAllocatedShares;

                            if (shares > availableShares) {
                                alert(`Only ${availableShares} shares are available.`);
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                                return;
                            }
                        }

                        this.submit();
                    }
                });
            }

            // Edit owner form submission
            const editOwnerForm = safeGetElement('editOwnerForm');
            if (editOwnerForm) {
                editOwnerForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = safeGetElement('editOwnerSubmitBtn');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        submitBtn.disabled = true;

                        const sharesElement = safeGetElement('edit_owner_shares');
                        if (sharesElement) {
                            const shares = parseInt(sharesElement.value);
                            const maxAllowed = parseInt(sharesElement.max);

                            if (shares > maxAllowed || shares < 1) {
                                alert('Please enter a valid number of shares.');
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                                return;
                            }
                        }

                        this.submit();
                    }
                });
            }

            // Add unit form submission
            const addUnitForm = safeGetElement('addUnitForm');
            if (addUnitForm) {
                addUnitForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                        submitBtn.disabled = true;

                        // Basic validation
                        const unitNameElement = safeGetElement('unit_name');
                        if (unitNameElement && !unitNameElement.value.trim()) {
                            alert('Please enter a unit name.');
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            return;
                        }

                        this.submit();
                    }
                });
            }

            // Edit unit form submission
            const editUnitForm = safeGetElement('editUnitForm');
            if (editUnitForm) {
                editUnitForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        submitBtn.disabled = true;

                        // Basic validation
                        const unitNameElement = safeGetElement('edit_unit_name');
                        if (unitNameElement && !unitNameElement.value.trim()) {
                            alert('Please enter a unit name.');
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            return;
                        }

                        this.submit();
                    }
                });
            }

            // Wire up value changes
            const totalSharesElement = safeGetElement('total_shares');
            const propertyValueElement = safeGetElement('property_value');

            if (totalSharesElement) {
                totalSharesElement.addEventListener('input', function () {
                    validateShares();
                    calculateShareValue();
                    updatePreviewTable();
                });
            }

            if (propertyValueElement) {
                propertyValueElement.addEventListener('input', function () {
                    calculateShareValue();
                    updatePreviewTable();
                });
            }

            // Initial calculations
            calculateShareValue();
            updatePreviewTable();
            validateShares();

            console.log('Edit property page initialization complete');

        } catch (error) {
            console.error('Error in DOMContentLoaded:', error);
        }
    });
</script>

// ADD THIS CSS as well:
<style>
    /* Enhanced styles */
    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-bottom: 1px solid #e3e6f0;
        background-color: #f8f9fc;
    }

    .modal-footer {
        border-top: 1px solid #e3e6f0;
        background-color: #f8f9fc;
    }

    .btn-group-sm>.btn,
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .badge-sm {
        font-size: 0.75em;
        padding: 0.25em 0.5em;
    }

    #editSharesWarning {
        border-left: 4px solid #f6c23e;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .new-percentage.text-success {
        color: #28a745 !important;
        font-weight: bold;
    }

    .new-percentage.text-warning {
        color: #ffc107 !important;
        font-weight: bold;
    }

    .new-percentage.text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }

    /* Unit status badges */
    .badge-success {
        background-color: #28a745;
    }

    .badge-primary {
        background-color: #007bff;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    /* Modal improvements */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
    }

    .modal.show {
        display: block !important;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1040;
    }

    .modal-dialog {
        position: relative;
        width: auto;
        margin: 1.75rem auto;
        pointer-events: none;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.3rem;
        outline: 0;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* Close button styles */
    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        background: transparent;
        border: 0;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
        text-decoration: none;
        opacity: 0.75;
    }
</style>>

<?= $this->endSection() ?>