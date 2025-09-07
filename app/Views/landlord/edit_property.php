<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Edit Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Property
        </h1>
        <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Property
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Property Information Form -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Edit Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('landlord/properties/update/' . $property['id']) ?>">
                        <?= csrf_field() ?>

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
                                    value="<?= $property['property_value'] ?>" required min="1" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="2"
                                required><?= esc($property['address']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="total_shares" class="form-label">Total Shares *</label>
                                <input type="number" class="form-control" id="total_shares" name="total_shares"
                                    value="<?= $property['total_shares'] ?>" required min="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contribution_duration" class="form-label">Contribution Duration (Months)
                                    *</label>
                                <input type="number" class="form-control" id="contribution_duration"
                                    name="contribution_duration" value="<?= $property['contribution_duration'] ?>"
                                    required min="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_units" class="form-label">Total Units *</label>
                                <input type="number" class="form-control" id="total_units" name="total_units"
                                    value="<?= $property['total_units'] ?>" required min="1">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remaining_balance" class="form-label">Remaining Balance (Read Only)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">SAR</span>
                                    </div>
                                    <input type="text" class="form-control bg-light" id="remaining_balance"
                                        value="<?= number_format($property['remaining_balance'] ?? 0, 2) ?>" readonly>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Calculated from: Net Income - Distributed Cash
                                </small>
                            </div>
                        </div>

                        <!-- Management Information -->
                        <h5 class="mb-3 mt-4">Management Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="management_company" class="form-label">Management Company</label>
                                <input type="text" class="form-control" id="management_company"
                                    name="management_company" value="<?= esc($property['management_company'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="management_percentage" class="form-label">Management Fee (%)</label>
                                <input type="number" class="form-control" id="management_percentage"
                                    name="management_percentage" value="<?= $property['management_percentage'] ?? 0 ?>"
                                    min="0" max="50" step="0.1">
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Units Management Section -->
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
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($units as $unit): ?>
                                        <tr>
                                            <td><strong><?= esc($unit['unit_name']) ?></strong></td>
                                            <td>
                                                <small><?= date('M d, Y', strtotime($unit['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="editUnit(<?= $unit['id'] ?>, '<?= esc($unit['unit_name'], 'attr') ?>')"
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

    <!-- Shareholders Section -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-users"></i> Property Shareholders
                    </h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="showAddShareholderModal()">
                        <i class="fas fa-plus"></i> Add Shareholder
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($owners)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Owner Name</th>
                                        <th>Email</th>
                                        <th>Shares</th>
                                        <th>Ownership %</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($owners as $owner): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($owner['name']) ?></strong>
                                                <?php if ($owner['is_current_user']): ?>
                                                    <span class="badge badge-primary ml-1">You</span>
                                                <?php endif; ?>
                                                <?php if ($owner['is_primary_owner']): ?>
                                                    <span class="badge badge-warning ml-1">Primary</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($owner['owner_email']) ?></td>
                                            <td><?= number_format($owner['shares']) ?></td>
                                            <td><?= number_format($owner['ownership_percentage'], 2) ?>%</td>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $owner['status'] === 'active' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($owner['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="editShareholder(<?= $owner['id'] ?? 0 ?>, '<?= esc($owner['name'], 'attr') ?>', '<?= esc($owner['owner_email'], 'attr') ?>', <?= $owner['shares'] ?>)"
                                                        title="Edit Shareholder">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (!$owner['is_current_user'] && !$owner['is_primary_owner']): ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="confirmRemoveShareholder(<?= $owner['id'] ?? 0 ?>, '<?= esc($owner['name'], 'attr') ?>')"
                                                            title="Remove Shareholder">
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

                        <!-- Share Summary -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="text-xs text-gray-500">Total Shares</div>
                                    <div class="font-weight-bold text-primary">
                                        <?= number_format($property['total_shares']) ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-xs text-gray-500">Allocated Shares</div>
                                    <div class="font-weight-bold text-success"><?= number_format($totalAllocatedShares) ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-xs text-gray-500">Available Shares</div>
                                    <div class="font-weight-bold text-warning">
                                        <?= number_format($property['total_shares'] - $totalAllocatedShares) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Shareholders Found</h6>
                            <p class="text-muted">There seems to be an issue with the shareholders data</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Property Buttons - Moved to the end with dynamic form data -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <button type="button" class="btn btn-primary btn-lg mr-3" onclick="updateProperty()">
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

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-labelledby="addUnitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUnitModalLabel">
                    <i class="fas fa-plus"></i> Add New Unit
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addUnitForm" method="post"
                action="<?= site_url('landlord/properties/add-unit/' . $property['id']) ?>">
                <?= csrf_field() ?>
                <!-- ADDED: Dynamic redirect parameter will be set by JavaScript -->
                <input type="hidden" name="redirect_to" id="addUnitRedirectTo" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="unit_name" class="form-label">Unit Name *</label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name" required
                            placeholder="e.g., Unit 1A, Apt 101, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-labelledby="editUnitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUnitModalLabel">
                    <i class="fas fa-edit"></i> Edit Unit
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUnitForm" method="post">
                <?= csrf_field() ?>
                <!-- ADDED: Dynamic redirect parameter will be set by JavaScript -->
                <input type="hidden" name="redirect_to" id="editUnitRedirectTo" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_unit_name" class="form-label">Unit Name *</label>
                        <input type="text" class="form-control" id="edit_unit_name" name="unit_name" required>
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

<!-- Add Shareholder Modal -->
<div class="modal fade" id="addShareholderModal" tabindex="-1" role="dialog">
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
            <form id="addShareholderForm" method="post"
                action="<?= site_url('landlord/properties/add-owner/' . $property['id']) ?>">
                <?= csrf_field() ?>
                <!-- ADDED: Hidden redirect parameter for edit property -->
                <input type="hidden" name="redirect_to" value="edit">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="shareholder_name" class="form-label">Shareholder Name *</label>
                        <input type="text" class="form-control" id="shareholder_name" name="owner_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="shareholder_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="shareholder_email" name="owner_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="shareholder_shares" class="form-label">Shares *</label>
                        <input type="number" class="form-control" id="shareholder_shares" name="owner_shares" required
                            min="1" max="<?= $property['total_shares'] - $totalAllocatedShares ?>">
                        <small class="text-muted">Available:
                            <?= number_format($property['total_shares'] - $totalAllocatedShares) ?> shares</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Shareholder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Shareholder Modal -->
<div class="modal fade" id="editShareholderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Shareholder
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editShareholderForm" method="post">
                <?= csrf_field() ?>
                <!-- ADDED: Dynamic redirect parameter will be set by JavaScript -->
                <input type="hidden" name="redirect_to" id="editRedirectTo" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shareholder Name</label>
                        <input type="text" class="form-control" id="edit_shareholder_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_shareholder_email" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_shareholder_shares" class="form-label">Shares *</label>
                        <input type="number" class="form-control" id="edit_shareholder_shares" name="shares" required
                            min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Shares</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FIXED JavaScript - removed jQuery dependency and removed description references -->
<script>
    // Global variables
    const totalShares = <?= $property['total_shares'] ?? 0 ?>;
    const totalAllocatedShares = <?= $totalAllocatedShares ?? 0 ?>;
    const availableShares = totalShares - totalAllocatedShares;

    // FUNCTION: Detect current page context for redirect
    function getCurrentPageContext() {
        const currentUrl = window.location.href;

        if (currentUrl.includes('/edit/')) {
            return 'edit';
        } else if (currentUrl.includes('/view/')) {
            return 'view';
        } else {
            return 'view'; // Default fallback
        }
    }

    // FUNCTION: Set redirect parameters in forms
    function setRedirectParameters() {
        const context = getCurrentPageContext();

        // Set redirect parameters for all forms
        const redirectInputs = document.querySelectorAll('[name="redirect_to"]');
        redirectInputs.forEach(input => {
            if (input.id === 'addUnitRedirectTo' || input.id === 'editUnitRedirectTo' ||
                input.id === 'editRedirectTo' || input.value === '') {
                input.value = context;
            }
        });
    }

    // Modal management functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            document.body.classList.add('modal-open');

            // Set redirect parameters when modal is shown
            setRedirectParameters();
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.style.backgroundColor = '';
            document.body.classList.remove('modal-open');
        }
    }

    // UPDATED: Unit Management Functions with redirect context
    function showAddUnitModal() {
        document.getElementById('addUnitForm').reset();
        showModal('addUnitModal');
    }

    function editUnit(unitId, unitName) {
        document.getElementById('edit_unit_name').value = unitName;
        document.getElementById('editUnitForm').action = '<?= site_url('landlord/properties/update-unit/' . $property['id']) ?>/' + unitId;
        showModal('editUnitModal');
    }

    function confirmRemoveUnit(unitId, unitName) {
        if (confirm(`Are you sure you want to remove "${unitName}"? This action cannot be undone.`)) {
            window.location.href = '<?= site_url('landlord/properties/remove-unit/' . $property['id']) ?>/' + unitId;
        }
    }

    // UPDATED: Shareholder Management Functions with redirect context
    function showAddShareholderModal() {
        if (availableShares <= 0) {
            alert('No shares available. All shares have been allocated.');
            return;
        }

        const form = document.getElementById('addShareholderForm');
        if (form) {
            form.reset();
            const sharesInput = document.getElementById('shareholder_shares');
            if (sharesInput) {
                sharesInput.max = availableShares;
            }
            showModal('addShareholderModal');
        }
    }

    // For property details page - different modal
    function showAddOwnerModal() {
        if (availableShares <= 0) {
            alert('No shares available. All shares have been allocated.');
            return;
        }

        const form = document.getElementById('addOwnerForm');
        if (form) {
            form.reset();
            const sharesInput = document.getElementById('owner_shares');
            if (sharesInput) {
                sharesInput.max = availableShares;
            }
            showModal('addOwnerModal');
        }
    }

    function editShareholder(ownerId, name, email, shares) {
        document.getElementById('edit_shareholder_name').value = name;
        document.getElementById('edit_shareholder_email').value = email;
        document.getElementById('edit_shareholder_shares').value = shares;

        // Set form action
        const form = document.getElementById('editShareholderForm');
        if (form) {
            form.action = '<?= site_url('landlord/properties/update-owner/' . $property['id']) ?>/' + ownerId;
        }

        showModal('editShareholderModal');
    }

    function confirmRemoveShareholder(ownerId, name) {
        if (confirm(`Are you sure you want to remove "${name}" from this property? This action cannot be undone.`)) {
            window.location.href = '<?= site_url('landlord/properties/remove-owner/' . $property['id']) ?>/' + ownerId;
        }
    }

    // UPDATED: Property update function for edit page
    function updateProperty() {
        const propertyName = document.getElementById('property_name').value;
        const propertyValue = document.getElementById('property_value').value;
        const propertyAddress = document.getElementById('property_address').value;
        const totalShares = document.getElementById('total_shares').value;
        const contributionDuration = document.getElementById('contribution_duration').value;
        const totalUnits = document.getElementById('total_units').value;

        if (!propertyName || !propertyValue || !propertyAddress || !totalShares || !contributionDuration || !totalUnits) {
            alert('Please fill in all required fields.');
            return;
        }

        const mainForm = document.querySelector('form[action*="update"]');
        if (mainForm) {
            mainForm.submit();
        }
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function () {
        // Set initial redirect parameters
        setRedirectParameters();

        // Close modal handlers
        document.querySelectorAll('[data-dismiss="modal"], .close').forEach(button => {
            button.addEventListener('click', function () {
                const modal = this.closest('.modal');
                if (modal) {
                    hideModal(modal.id);
                }
            });
        });

        // Backdrop click handlers
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    hideModal(modal.id);
                }
            });
        });

        // Escape key handler
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    hideModal(modal.id);
                });
            }
        });

        // Property value and shares calculation
        const propertyValueInput = document.getElementById('property_value');
        const totalSharesInput = document.getElementById('total_shares');

        function updateShareValue() {
            const propertyValue = parseFloat(propertyValueInput?.value) || 0;
            const totalShares = parseInt(totalSharesInput?.value) || 1;
            const shareValue = propertyValue / totalShares;

            let shareValueDisplay = document.getElementById('share-value-display');
            if (!shareValueDisplay && totalSharesInput) {
                shareValueDisplay = document.createElement('small');
                shareValueDisplay.id = 'share-value-display';
                shareValueDisplay.className = 'form-text text-info font-weight-bold';
                totalSharesInput.parentNode.appendChild(shareValueDisplay);
            }

            if (shareValueDisplay) {
                if (propertyValue > 0 && totalShares > 0) {
                    shareValueDisplay.textContent = `Share value: SAR ${shareValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} per share`;
                } else {
                    shareValueDisplay.textContent = '';
                }
            }
        }

        if (propertyValueInput && totalSharesInput) {
            propertyValueInput.addEventListener('input', updateShareValue);
            totalSharesInput.addEventListener('input', updateShareValue);
            updateShareValue();
        }

        // Auto-dismiss alerts
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            });
        }, 5000);
    });
</script>

<style>
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .table th {
        border-top: none;
    }

    .bg-light {
        background-color: #f8f9fc !important;
    }

    .text-gray-500 {
        color: #858796 !important;
    }

    /* Modal styles for vanilla JS compatibility */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        outline: 0;
    }

    .modal.show {
        display: block !important;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: #000;
        opacity: 0.5;
    }

    body.modal-open {
        overflow: hidden;
    }

    .modal-dialog {
        position: relative;
        width: auto;
        margin: 0.5rem;
        pointer-events: none;
    }

    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 500px;
            margin: 1.75rem auto;
        }
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
</style>

<?= $this->endSection() ?>