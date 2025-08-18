<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Property Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
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
                                <?php $unitCount = is_array($units ?? null) ? count($units) : (int) ($property['total_units'] ?? 0); ?>

                                <tr>
                                    <td><strong>Units:</strong></td>
                                    <td class="value">
                                        <span class="badge badge-info"><?= number_format($unitCount ?? 0) ?></span>
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
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $status = strtolower(trim($property['status'] ?? 'vacant'));
                                        $badgeMap = [
                                            'vacant' => 'badge-info',
                                            'occupied' => 'badge-success',
                                            'maintenance' => 'badge-warning',
                                        ];
                                        $badgeClass = $badgeMap[$status] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge badge-info <?= $badgeClass ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td class="value"><?= date('M d, Y', strtotime($property['created_at'])) ?></td>
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
                        <?php if ($unitCount > 0): ?>
                            <span class="badge badge-info ml-2"><?= number_format($unitCount) ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($units) && is_array($units)): ?>
                        <ul class="list-group">
                            <?php foreach ($units as $u):
                                $uStatus = strtolower(trim($u['status'] ?? 'vacant'));
                                $statusMap = [
                                    'vacant' => 'badge-warning',
                                    'occupied' => 'badge-success',
                                    'maintenance' => 'badge-secondary',
                                ];
                                $statusClass = $statusMap[$uStatus] ?? 'badge-light';
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= esc($u['unit_name'] ?? 'Unit') ?></strong>
                                    </div>
                                    <div class="text-right">
                                        <?php if (isset($u['rent_amount'])): ?>
                                            <span class="badge badge-primary">SAR
                                                <?= number_format((float) $u['rent_amount'], 2) ?></span>
                                        <?php endif; ?>
                                        <span class="badge <?= $statusClass ?> ml-1"><?= ucfirst($uStatus) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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
                        <button class="btn btn-sm btn-outline-success float-right" onclick="addNewOwner()">
                            <i class="fas fa-plus"></i> Add Owner
                        </button>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($owners) && is_array($owners)): ?>
                        <?php $totalOwnedShares = 0; ?>
                        <?php foreach ($owners as $index => $owner): ?>
                            <?php $totalOwnedShares += $owner['shares'] ?? 0; ?>
                            <div
                                class="owner-card mb-3 p-3 border rounded <?= $owner['is_current_user'] ?? false ? 'bg-light-primary border-primary' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="owner-info">
                                        <h6 class="mb-1">
                                            <?= esc($owner['name'] ?? $owner['owner_name'] ?? 'Unknown') ?>
                                            <?php if ($owner['is_current_user'] ?? false): ?>
                                                <span class="badge badge-primary badge-sm">You</span>
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

                                    <!-- REPLACE the dropdown section in your property_details.php -->

                                    <?php if (($isCreator ?? false) && !($owner['is_current_user'] ?? false) && !($owner['is_primary_owner'] ?? false)): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                onclick="toggleDropdown(this)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); event.stopPropagation(); editOwner(<?= (int) ($owner['shareholder_id'] ?? $owner['id'] ?? 0) ?>);">
                                                    <i class="fas fa-edit"></i> Edit Shares
                                                </a>
                                                <a class="dropdown-item text-danger" href="#"
                                                    onclick="event.preventDefault(); event.stopPropagation(); removeOwner(<?= (int) ($owner['shareholder_id'] ?? $owner['id'] ?? 0) ?>);">
                                                    <i class="fas fa-trash"></i> Remove Owner
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- REPLACE the JavaScript section with this FIXED version -->
                                    <script>
                                        // Global variables for calculations
                                        const totalShares = <?= $property['total_shares'] ?? 0 ?>;
                                        const totalOwnedShares = <?= $totalOwnedShares ?? 0 ?>;
                                        const availableShares = totalShares - totalOwnedShares;

                                        // FIXED: Simple dropdown toggle function
                                        function toggleDropdown(button) {
                                            if (event) {
                                                event.preventDefault();
                                                event.stopPropagation();
                                            }

                                            // Close all other dropdowns first
                                            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                                                if (menu !== button.nextElementSibling) {
                                                    menu.classList.remove('show');
                                                }
                                            });

                                            // Toggle this dropdown
                                            const menu = button.nextElementSibling;
                                            if (menu && menu.classList.contains('dropdown-menu')) {
                                                menu.classList.toggle('show');
                                            }
                                        }

                                        // Close dropdowns when clicking outside
                                        document.addEventListener('click', function (e) {
                                            if (!e.target.closest('.dropdown')) {
                                                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                                                    menu.classList.remove('show');
                                                });
                                            }
                                        });

                                        // Add new owner function
                                        function addNewOwner() {
                                            console.log('Adding new owner - available shares:', availableShares);

                                            if (availableShares <= 0) {
                                                alert('No shares available. All shares have been allocated.');
                                                return;
                                            }

                                            // Reset form
                                            document.getElementById('addOwnerForm').reset();
                                            document.getElementById('new_owner_percentage').value = '';
                                            document.getElementById('owner_shares').max = availableShares;

                                            // Show modal
                                            if (typeof $ !== 'undefined') {
                                                $('#addOwnerModal').modal('show');
                                            } else {
                                                document.getElementById('addOwnerModal').style.display = 'block';
                                                document.getElementById('addOwnerModal').classList.add('show');
                                            }
                                        }

                                        // Calculate ownership percentage for new owner
                                        function calculateNewOwnershipPercentage() {
                                            const ownerShares = parseInt(document.getElementById('owner_shares').value) || 0;

                                            if (ownerShares > availableShares) {
                                                alert(`Only ${availableShares} shares are available.`);
                                                document.getElementById('owner_shares').value = availableShares;
                                                return;
                                            }

                                            const percentage = totalShares > 0 ? (ownerShares / totalShares) * 100 : 0;
                                            document.getElementById('new_owner_percentage').value = percentage.toFixed(2) + '%';
                                        }

                                        // FIXED: Edit owner function with better debugging
                                        function editOwner(ownerId) {
                                            console.log('Edit owner called with ID:', ownerId);

                                            // Close any open dropdowns
                                            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                                                menu.classList.remove('show');
                                            });

                                            // Find owner data
                                            const ownerData = <?= json_encode($owners ?? []) ?>.find(owner => {
                                                const id1 = parseInt(owner.id || 0);
                                                const id2 = parseInt(owner.shareholder_id || 0);
                                                const targetId = parseInt(ownerId);
                                                return id1 === targetId || id2 === targetId;
                                            });

                                            console.log('Found owner data:', ownerData);

                                            if (ownerData) {
                                                // Populate form fields
                                                document.getElementById('edit_owner_id').value = ownerId;
                                                document.getElementById('edit_owner_name').value = ownerData.name || ownerData.owner_name || 'Unknown';
                                                document.getElementById('edit_owner_shares').value = ownerData.shares || 0;

                                                // Set form action
                                                const form = document.getElementById('editOwnerForm');
                                                form.action = `<?= site_url('landlord/properties/update-owner/' . $property['id']) ?>/${ownerId}`;

                                                console.log('Form action set to:', form.action);

                                                // Calculate percentage
                                                calculateEditOwnershipPercentage();

                                                // Show modal
                                                if (typeof $ !== 'undefined') {
                                                    $('#editOwnerModal').modal('show');
                                                    console.log('Modal shown using jQuery');
                                                } else {
                                                    const modal = document.getElementById('editOwnerModal');
                                                    modal.style.display = 'block';
                                                    modal.classList.add('show');
                                                    console.log('Modal shown using vanilla JS');
                                                }
                                            } else {
                                                console.error('Owner not found with ID:', ownerId);
                                                console.log('Available owners:', <?= json_encode($owners ?? []) ?>);
                                                alert('Owner data not found. Please refresh the page and try again.');
                                            }
                                        }

                                        // Calculate ownership percentage for edited owner
                                        function calculateEditOwnershipPercentage() {
                                            const ownerShares = parseInt(document.getElementById('edit_owner_shares').value) || 0;
                                            const percentage = totalShares > 0 ? (ownerShares / totalShares) * 100 : 0;
                                            document.getElementById('edit_owner_percentage').value = percentage.toFixed(2) + '%';
                                        }

                                        // FIXED: Remove owner function
                                        function removeOwner(ownerId) {
                                            console.log('Remove owner called with ID:', ownerId);

                                            // Close any open dropdowns
                                            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                                                menu.classList.remove('show');
                                            });

                                            if (confirm('Are you sure you want to remove this owner? This action cannot be undone.')) {
                                                // Create form and submit
                                                const form = document.createElement('form');
                                                form.method = 'POST';
                                                form.action = `<?= site_url('landlord/properties/remove-owner/' . $property['id']) ?>/${ownerId}`;

                                                const csrfField = document.createElement('input');
                                                csrfField.type = 'hidden';
                                                csrfField.name = '<?= csrf_token() ?>';
                                                csrfField.value = '<?= csrf_hash() ?>';
                                                form.appendChild(csrfField);

                                                document.body.appendChild(form);
                                                console.log('Submitting remove form to:', form.action);
                                                form.submit();
                                            }
                                        }

                                        // Form submissions with loading states
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const addOwnerForm = document.getElementById('addOwnerForm');
                                            if (addOwnerForm) {
                                                addOwnerForm.addEventListener('submit', function (e) {
                                                    e.preventDefault();
                                                    const submitBtn = this.querySelector('button[type="submit"]');
                                                    const originalText = submitBtn.innerHTML;
                                                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                                                    submitBtn.disabled = true;

                                                    const shares = parseInt(document.getElementById('owner_shares').value);
                                                    if (shares > availableShares) {
                                                        alert(`Only ${availableShares} shares are available.`);
                                                        submitBtn.innerHTML = originalText;
                                                        submitBtn.disabled = false;
                                                        return;
                                                    }

                                                    this.submit();
                                                });
                                            }

                                            const editOwnerForm = document.getElementById('editOwnerForm');
                                            if (editOwnerForm) {
                                                editOwnerForm.addEventListener('submit', function (e) {
                                                    e.preventDefault();
                                                    const submitBtn = this.querySelector('button[type="submit"]');
                                                    const originalText = submitBtn.innerHTML;
                                                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                                                    submitBtn.disabled = true;
                                                    this.submit();
                                                });
                                            }

                                            // Modal close handlers
                                            document.querySelectorAll('[data-dismiss="modal"], .close').forEach(button => {
                                                button.addEventListener('click', function () {
                                                    const modal = this.closest('.modal');
                                                    if (modal) {
                                                        if (typeof $ !== 'undefined') {
                                                            $(modal).modal('hide');
                                                        } else {
                                                            modal.style.display = 'none';
                                                            modal.classList.remove('show');
                                                        }
                                                    }
                                                });
                                            });
                                        });

                                        // Auto-dismiss alerts
                                        setTimeout(function () {
                                            document.querySelectorAll('.alert').forEach(alert => {
                                                if (alert.classList.contains('show')) {
                                                    alert.classList.remove('show');
                                                    setTimeout(() => alert.remove(), 150);
                                                }
                                            });
                                        }, 5000);
                                    </script>

                                    <style>
                                        /* Clean dropdown styles */
                                        .dropdown {
                                            position: relative;
                                            display: inline-block;
                                        }

                                        .dropdown-menu {
                                            position: absolute;
                                            top: 100%;
                                            right: 0;
                                            z-index: 1000;
                                            display: none;
                                            min-width: 150px;
                                            padding: 5px 0;
                                            margin: 2px 0 0;
                                            background-color: #fff;
                                            border: 1px solid #dee2e6;
                                            border-radius: 5px;
                                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                                        }

                                        .dropdown-menu.show {
                                            display: block !important;
                                        }

                                        .dropdown-item {
                                            display: block;
                                            width: 100%;
                                            padding: 8px 15px;
                                            color: #212529;
                                            text-decoration: none;
                                            background-color: transparent;
                                            border: 0;
                                            cursor: pointer;
                                            font-size: 14px;
                                        }

                                        .dropdown-item:hover {
                                            background-color: #f8f9fa;
                                            color: #1e2125;
                                            text-decoration: none;
                                        }

                                        .dropdown-item.text-danger:hover {
                                            background-color: #dc3545;
                                            color: #fff;
                                        }

                                        /* Other existing styles... */
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

                                        .value {
                                            font-weight: 500;
                                            color: #5a5c69;
                                        }

                                        .badge-sm {
                                            font-size: 0.7em;
                                        }

                                        /* Modal improvements */
                                        .modal {
                                            z-index: 1050;
                                        }

                                        .modal-backdrop {
                                            z-index: 1040;
                                        }

                                        .modal.show {
                                            display: block !important;
                                        }

                                        .close {
                                            padding: 0.5rem;
                                            background: transparent;
                                            border: 0;
                                            font-size: 1.25rem;
                                            font-weight: 700;
                                            line-height: 1;
                                            color: #000;
                                            opacity: 0.5;
                                            cursor: pointer;
                                        }

                                        .close:hover {
                                            opacity: 0.75;
                                        }
                                    </style>

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
                            <strong><?= number_format($unitCount) ?></strong>
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

<!-- FIXED: Add Owner Modal -->
<div class="modal fade" id="addOwnerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Add New Owner
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addOwnerForm" method="post"
                action="<?= site_url('landlord/properties/add-owner/' . $property['id']) ?>">
                <div class="modal-body">
                    <?= csrf_field() ?>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> The new owner will be linked to the system using their email address.
                        They must have an account or will be invited to create one.
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
                                id="availableSharesCount"><?= number_format(($property['total_shares'] ?? 0) - ($totalOwnedShares ?? 0)) ?></span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ownership Percentage</label>
                        <input type="text" class="form-control" id="new_owner_percentage" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add Owner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FIXED: Edit Owner Modal -->
<div class="modal fade" id="editOwnerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Owner Shares
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editOwnerForm" method="post">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" id="edit_owner_id" name="owner_id">

                    <div class="mb-3">
                        <label class="form-label">Owner Name</label>
                        <input type="text" class="form-control" id="edit_owner_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit_owner_shares" class="form-label">Number of Shares *</label>
                        <input type="number" class="form-control" id="edit_owner_shares" name="shares" required min="1"
                            onchange="calculateEditOwnershipPercentage()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Ownership Percentage</label>
                        <input type="text" class="form-control" id="edit_owner_percentage" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Shares
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    // FIXED: Close modal handlers
    document.addEventListener('DOMContentLoaded', function () {
        // Handle modal close buttons
        const modals = ['addOwnerModal', 'editOwnerModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
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
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && modal.classList.contains('show')) {
                        closeModal(modalId);
                    }
                });
            }
        });
    });

    // Helper function to close modals
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (typeof bootstrap !== 'undefined') {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            } else if (typeof $ !== 'undefined') {
                $(modal).modal('hide');
            } else {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    }
</script>

<style>
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

    .dropdown-toggle::after {
        margin-left: 0.255em;
    }

    /* FIXED: Modal styles for better compatibility */
    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }

    .modal.show {
        display: block !important;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* FIXED: Dropdown menu styles */
    .dropdown-menu {
        z-index: 1060;
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        color: #212529;
        text-decoration: none;
        background-color: transparent;
        border: 0;
        display: block;
        width: 100%;
        clear: both;
        font-weight: 400;
        text-align: inherit;
        white-space: nowrap;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        color: #1e2125;
        background-color: #e9ecef;
    }

    .dropdown-item.text-danger:hover {
        color: #fff;
        background-color: #dc3545;
    }

    /* Close button improvements */
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
</style>

<?= $this->endSection() ?>