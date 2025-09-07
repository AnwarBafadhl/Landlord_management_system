<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Property Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> <?= esc($property['property_name']) ?>
        </h1>
        <div>
            <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Properties
            </a>
            <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Property
            </a>
        </div>
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

    <div class="row">
        <!-- Property Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Property Name:</strong></td>
                                    <td class="value"><?= esc($property['property_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Property Value:</strong></td>
                                    <td class="value">
                                        <span class="badge badge-success" style="font-size: 14px;">
                                            SAR <?= number_format($property['property_value'] ?? 0, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td class="value"><?= esc($property['address']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Shares:</strong></td>
                                    <td class="value">
                                        <span class="badge badge-primary">
                                            <?= number_format($property['total_shares'] ?? 0) ?> shares
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Share Value:</strong></td>
                                    <td class="value">SAR <?= number_format($property['share_value'] ?? 0, 2) ?> per
                                        share</td>
                                </tr>
                                <tr>
                                    <td><strong>Remaining Balance:</strong></td>
                                    <td class="value">
                                        <span class="badge badge-warning" style="font-size: 14px;">
                                            SAR <?= number_format($property['remaining_balance'] ?? 0, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Contribution Duration:</strong></td>
                                    <td class="value"><?= $property['contribution_duration'] ?? 0 ?> months</td>
                                </tr>
                                <tr>
                                    <td><strong>Management Company:</strong></td>
                                    <td class="value"><?= esc($property['management_company'] ?? 'Self-Management') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Management Fee:</strong></td>
                                    <td class="value"><?= $property['management_percentage'] ?? 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td class="value"><?= date('M d, Y', strtotime($property['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Units:</strong></td>
                                    <td class="value">
                                        <span class="badge badge-info"><?= number_format($unitCount ?? 0) ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Units -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-door-open"></i> Units
                        <?php if (($unitCount ?? 0) > 0): ?>
                            <span class="badge badge-info ml-2"><?= number_format($unitCount ?? 0) ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($units) && is_array($units)): ?>
                        <div class="row">
                            <?php foreach ($units as $unit): ?>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card border-left-info h-100">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-door-open text-info mr-2"></i>
                                                <div>
                                                    <strong><?= esc($unit['unit_name'] ?? 'Unit') ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-door-open fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Units Found</h6>
                            <p class="text-muted small mb-0">Add units for better tracking of rentals and status.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shareholders Agreement Conditions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-gavel"></i> Shareholders Agreement Conditions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Non-Operational Involvement</strong>
                                        <p class="text-muted mb-0 small">Shareholders have no involvement in the
                                            property's operation at all.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Income Distribution</strong>
                                        <p class="text-muted mb-0 small">Any financial income from the property will be
                                            distributed to shareholders after deducting expenses.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Violation Refund</strong>
                                        <p class="text-muted mb-0 small">In case of any violation, the shareholder's
                                            contribution amount will be refunded.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Share Transfer Restriction</strong>
                                        <p class="text-muted mb-0 small">Shareholders are not allowed to sell their
                                            shares to anyone outside the current shareholders.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owners Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-users"></i> Owners Information
                        <?php if ($isCreator ?? false): ?>
                            <button class="btn btn-sm btn-outline-success float-right" onclick="addNewOwner()">
                                <i class="fas fa-plus"></i> Add Owner
                            </button>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($owners) && is_array($owners)): ?>
                        <?php $totalOwnedShares = 0; ?>
                        <?php foreach ($owners as $index => $owner): ?>
                            <?php $totalOwnedShares += $owner['shares'] ?? 0; ?>
                            <div
                                class="owner-card mb-3 p-3 border rounded <?= $owner['is_current_user'] ?? false ? 'bg-light-primary border-primary' : '' ?>">
                                <div class="owner-info">
                                    <h6 class="mb-1">
                                        <?= esc($owner['name'] ?? $owner['owner_name'] ?? 'Unknown') ?>
                                        <?php if ($owner['is_current_user'] ?? false): ?>
                                            <span class="badge badge-primary badge-sm">You</span>
                                        <?php endif; ?>
                                        <?php if ($owner['is_primary_owner'] ?? false): ?>
                                            <span class="badge badge-warning badge-sm">Primary</span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="text-muted mb-1 small">
                                        <i class="fas fa-envelope"></i>
                                        <?= esc($owner['email'] ?? $owner['owner_email'] ?? 'No email') ?>
                                    </p>
                                    <div class="ownership-details">
                                        <span class="badge badge-primary">
                                            <?= number_format($owner['shares'] ?? 0) ?> shares
                                        </span>
                                        <span class="badge badge-success ml-1">
                                            <?= number_format($owner['ownership_percentage'] ?? 0, 2) ?>%
                                        </span>
                                    </div>
                                    <?php if (isset($owner['status']) && $owner['status'] === 'pending'): ?>
                                        <div class="mt-1">
                                            <span class="badge badge-warning badge-sm">Pending Invitation</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Ownership Summary -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <strong>Total Allocated:</strong>
                                <span class="text-primary">
                                    <?= number_format($totalOwnedShares) ?> /
                                    <?= number_format($property['total_shares'] ?? 0) ?> shares
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Available Shares:</strong>
                                <span class="text-success">
                                    <?= number_format(($property['total_shares'] ?? 0) - $totalOwnedShares) ?> shares
                                </span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= ($property['total_shares'] ?? 0) > 0 ? ($totalOwnedShares / ($property['total_shares'] ?? 1)) * 100 : 0 ?>%">
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Owners Added Yet</h6>
                            <p class="text-muted small">Click "Add Owner" to start adding property owners.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-line"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Shareholders:</span>
                            <strong><?= count($owners ?? []) ?></strong>
                        </div>
                    </div>

                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Units:</span>
                            <strong><?= number_format($unitCount ?? 0) ?></strong>
                        </div>
                    </div>

                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Share Allocation:</span>
                            <strong><?= number_format((($totalOwnedShares ?? 0) / ($property['total_shares'] ?? 1)) * 100, 1) ?>%</strong>
                        </div>
                    </div>

                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Monthly Contribution:</span>
                            <strong>SAR
                                <?= number_format(($property['property_value'] ?? 0) / ($property['contribution_duration'] ?? 1), 2) ?></strong>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Management Fee:</span>
                            <strong><?= $property['management_percentage'] ?? 0 ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FIXED: Add Owner Modal (only shows for property creators) -->
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
                <!-- ADDED: Hidden redirect parameter for view details -->
                <input type="hidden" name="redirect_to" value="view">

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> The new shareholder will be linked using their email address.
                        They will receive an invitation if they don't have an account.
                    </div>

                    <div class="mb-3">
                        <label for="owner_name" class="form-label">Owner Name *</label>
                        <input type="text" class="form-control" id="owner_name" name="owner_name" required
                            placeholder="Enter full name">
                    </div>

                    <div class="mb-3">
                        <label for="owner_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="owner_email" name="owner_email" required
                            placeholder="Enter email address">
                        <small class="text-muted">This email will be used to connect the owner's account to this
                            property.</small>
                    </div>

                    <div class="mb-3">
                        <label for="owner_shares" class="form-label">Number of Shares *</label>
                        <input type="number" class="form-control" id="owner_shares" name="owner_shares" required min="1"
                            placeholder="Enter number of shares" onchange="calculateNewOwnershipPercentage()">
                        <small class="text-muted" id="availableSharesText">
                            Available shares: <span
                                id="availableSharesCount"><?= number_format(($property['total_shares'] ?? 0) - ($totalAllocatedShares ?? 0)) ?></span>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Owner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Modal backdrop fix */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        /* Changed from hidden to auto */
        outline: 0;
        background: rgba(0, 0, 0, 0.5);
        /* Add backdrop directly to modal */
    }

    .modal.show {
        display: flex !important;
        /* Changed to flex for better centering */
        align-items: center;
        justify-content: center;
    }

    body.modal-open {
        overflow: hidden;
        padding-right: 17px;
        /* Prevent page shift */
    }

    .modal-dialog {
        position: relative;
        width: 90%;
        max-width: 500px;
        margin: 1rem auto;
        pointer-events: auto;
        /* Ensure dialog is clickable */
        z-index: 1051;
        /* Higher than modal backdrop */
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        /* Ensure content is clickable */
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.3rem;
        outline: 0;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.5);
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1rem 1rem;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: calc(0.3rem - 1px);
        border-top-right-radius: calc(0.3rem - 1px);
    }

    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
    }

    .modal-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        padding: 0.75rem;
        border-top: 1px solid #dee2e6;
        border-bottom-right-radius: calc(0.3rem - 1px);
        border-bottom-left-radius: calc(0.3rem - 1px);
    }

    .modal-footer>* {
        margin: 0.25rem;
    }

    .close {
        padding: 0.5rem;
        margin: -0.5rem -0.5rem -0.5rem auto;
        background: transparent;
        border: 0;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
        text-decoration: none;
        opacity: 0.75;
    }

    /* Remove any conflicting backdrop styles */
    .modal-backdrop {
        display: none !important;
    }

    /* Ensure form elements are clickable */
    .modal input,
    .modal select,
    .modal textarea,
    .modal button {
        pointer-events: auto;
        z-index: auto;
    }

    /* Other existing styles remain the same */
    .owner-card {
        transition: all 0.3s ease;
    }

    .owner-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .bg-light-primary {
        background-color: rgba(78, 115, 223, 0.1) !important;
    }

    .border-primary {
        border-color: #4e73df !important;
    }

    .condition-item {
        padding: 10px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }

    .stat-item {
        padding: 8px 0;
        border-bottom: 1px solid #e3e6f0;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .progress {
        height: 8px;
    }

    .badge-sm {
        font-size: 0.7em;
    }

    .badge-info {
        background-color: #4e73df !important;
        color: #fff !important;
    }

    .badge-primary {
        background-color: #36b9cc !important;
        color: #fff !important;
    }

    .value {
        font-weight: 500;
        color: #5a5c69;
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .table td,
    .table th {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .table-borderless td {
        border: none;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
</style>

<!-- Updated JavaScript for better modal handling -->
<script>
    // Global variables
    const totalShares = <?= $property['total_shares'] ?? 0 ?>;
    const totalAllocatedShares = <?= $totalAllocatedShares ?? 0 ?>;
    const availableShares = totalShares - totalAllocatedShares;

    // Improved modal management functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Remove any existing modal backdrops
            const existingBackdrops = document.querySelectorAll('.modal-backdrop');
            existingBackdrops.forEach(backdrop => backdrop.remove());

            modal.style.display = 'flex';
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            // Focus on the modal for accessibility
            modal.focus();
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = ''; // Remove padding adjustment
        }
    }

    // Fixed addNewOwner function
    function addNewOwner() {
        console.log('Adding new owner - available shares:', availableShares);

        if (availableShares <= 0) {
            alert('No shares available. All shares have been allocated.');
            return;
        }

        // Reset form
        const addOwnerForm = document.getElementById('addOwnerForm');
        if (addOwnerForm) {
            addOwnerForm.reset();
        }

        // Set form constraints
        const ownerSharesInput = document.getElementById('owner_shares');
        if (ownerSharesInput) {
            ownerSharesInput.max = availableShares;
            ownerSharesInput.min = 1;
            ownerSharesInput.value = '';
        }

        // Update available shares display
        const availableSharesCount = document.getElementById('availableSharesCount');
        if (availableSharesCount) {
            availableSharesCount.textContent = availableShares.toLocaleString();
        }

        // Show modal - using our custom function
        showModal('addOwnerModal');
    }

    // Fixed calculateNewOwnershipPercentage function
    function calculateNewOwnershipPercentage() {
        const ownerSharesInput = document.getElementById('owner_shares');
        if (!ownerSharesInput) {
            return;
        }

        const ownerShares = parseInt(ownerSharesInput.value) || 0;

        if (ownerShares > availableShares) {
            alert(`Only ${availableShares} shares are available.`);
            ownerSharesInput.value = availableShares;
            return;
        }

        // Calculate and display percentage if elements exist
        const percentage = totalShares > 0 ? (ownerShares / totalShares) * 100 : 0;

        const percentageDisplay = document.getElementById('new_owner_percentage');
        if (percentageDisplay) {
            percentageDisplay.value = percentage.toFixed(2) + '%';
        }
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function () {
        // Modal close handlers
        document.addEventListener('click', function (e) {
            // Close modal when clicking outside the modal content
            if (e.target.classList.contains('modal')) {
                hideModal(e.target.id);
            }

            // Close modal when clicking close button
            if (e.target.matches('[data-dismiss="modal"], .close, .close *')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    hideModal(modal.id);
                }
            }
        });

        // Escape key handler
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    hideModal(modal.id);
                });
            }
        });

        // Owner shares input handler
        const ownerSharesInput = document.getElementById('owner_shares');
        if (ownerSharesInput) {
            ownerSharesInput.addEventListener('input', calculateNewOwnershipPercentage);
            ownerSharesInput.addEventListener('change', calculateNewOwnershipPercentage);
        }

        // Form submission with loading state
        const addOwnerForm = document.getElementById('addOwnerForm');
        if (addOwnerForm) {
            addOwnerForm.addEventListener('submit', function (e) {
                const sharesInput = document.getElementById('owner_shares');
                if (!sharesInput) return;

                const shares = parseInt(sharesInput.value);
                if (shares > availableShares) {
                    e.preventDefault();
                    alert(`Only ${availableShares} shares are available.`);
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    submitBtn.disabled = true;
                }
            });
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

<?= $this->endSection() ?>