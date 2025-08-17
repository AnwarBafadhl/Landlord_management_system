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
                                        <span class="badge badge-info">
                                            <?= number_format($property['total_shares'] ?? 0) ?> shares
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Share Value:</strong></td>
                                    <td class="value">SAR <?= number_format($property['share_value'] ?? 0, 2) ?> per share</td>
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
                                    <td class="value"><?= esc($property['management_company'] ?? 'Not specified') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Management Fee:</strong></td>
                                    <td class="value"><?= $property['management_percentage'] ?? 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-<?= $property['status'] === 'active' ? 'success' : ($property['status'] === 'inactive' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($property['status'] ?? 'pending') ?>
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
                                        <p class="text-muted mb-0 small">Shareholders have no involvement in the property's operation at all.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Income Distribution</strong>
                                        <p class="text-muted mb-0 small">Any financial income from the property will be distributed to shareholders after deducting expenses.</p>
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
                                        <p class="text-muted mb-0 small">In case of any violation, the shareholder's contribution amount will be refunded.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="condition-item mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                    <div>
                                        <strong>Share Transfer Restriction</strong>
                                        <p class="text-muted mb-0 small">Shareholders are not allowed to sell their shares to anyone outside the current shareholders.</p>
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
                            <div class="owner-card mb-3 p-3 border rounded <?= $owner['is_current_user'] ?? false ? 'bg-light-primary border-primary' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="owner-info">
                                        <h6 class="mb-1">
                                            <?= esc($owner['name'] ?? $owner['owner_name'] ?? 'Unknown') ?>
                                            <?php if ($owner['is_current_user'] ?? false): ?>
                                                <span class="badge badge-primary badge-sm">You</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-envelope"></i> <?= esc($owner['email'] ?? $owner['owner_email'] ?? 'No email') ?>
                                        </p>
                                        <div class="ownership-details">
                                            <span class="badge badge-info">
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
                                    
                                    <?php if (!($owner['is_current_user'] ?? false) && !($owner['is_primary_owner'] ?? false)): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#" onclick="editOwner(<?= $owner['id'] ?? 0 ?>)">
                                                    <i class="fas fa-edit"></i> Edit Shares
                                                </a>
                                                <a class="dropdown-item text-danger" href="#" onclick="removeOwner(<?= $owner['id'] ?? 0 ?>)">
                                                    <i class="fas fa-trash"></i> Remove Owner
                                                </a>
                                            </div>
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
                                    <?= number_format($totalOwnedShares) ?> / <?= number_format($property['total_shares'] ?? 0) ?> shares
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
                            <span class="text-muted">Share Allocation:</span>
                            <strong><?= number_format((($totalOwnedShares ?? 0) / ($property['total_shares'] ?? 1)) * 100, 1) ?>%</strong>
                        </div>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Monthly Contribution:</span>
                            <strong>SAR <?= number_format(($property['property_value'] ?? 0) / ($property['contribution_duration'] ?? 1), 2) ?></strong>
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

<!-- Add Owner Modal -->
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
            <form id="addOwnerForm" method="post" action="<?= site_url('landlord/properties/add-owner/' . $property['id']) ?>">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> The new owner will be linked to the system using their email address. They must have an account or will be invited to create one.
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
                        <small class="text-muted">This email will be used to connect the owner's account to this property.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="owner_shares" class="form-label">Number of Shares *</label>
                        <input type="number" class="form-control" id="owner_shares" name="owner_shares" required
                            min="1" max="<?= ($property['total_shares'] ?? 0) - ($totalOwnedShares ?? 0) ?>"
                            placeholder="Enter number of shares" onchange="calculateNewOwnershipPercentage()">
                        <small class="text-muted">
                            Available shares: <?= number_format(($property['total_shares'] ?? 0) - ($totalOwnedShares ?? 0)) ?>
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

<!-- Edit Owner Modal -->
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
                        <input type="number" class="form-control" id="edit_owner_shares" name="shares" required
                            min="1" onchange="calculateEditOwnershipPercentage()">
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
// Add new owner
function addNewOwner() {
    const availableShares = <?= ($property['total_shares'] ?? 0) - ($totalOwnedShares ?? 0) ?>;
    
    if (availableShares <= 0) {
        alert('No shares available. All shares have been allocated.');
        return;
    }
    
    $('#addOwnerModal').modal('show');
}

// Calculate ownership percentage for new owner
function calculateNewOwnershipPercentage() {
    const totalShares = <?= $property['total_shares'] ?? 0 ?>;
    const ownerShares = parseInt(document.getElementById('owner_shares').value) || 0;
    
    const percentage = (ownerShares / totalShares) * 100;
    document.getElementById('new_owner_percentage').value = percentage.toFixed(2) + '%';
}

// Edit owner
function editOwner(ownerId) {
    // Find owner data (this would typically come from a data attribute or AJAX call)
    const ownerData = <?= json_encode($owners ?? []) ?>.find(owner => owner.id == ownerId);
    
    if (ownerData) {
        document.getElementById('edit_owner_id').value = ownerId;
        document.getElementById('edit_owner_name').value = ownerData.name || ownerData.owner_name;
        document.getElementById('edit_owner_shares').value = ownerData.shares;
        
        // Set form action
        document.getElementById('editOwnerForm').action = 
            `<?= site_url('landlord/properties/update-owner/' . $property['id']) ?>/${ownerId}`;
        
        calculateEditOwnershipPercentage();
        $('#editOwnerModal').modal('show');
    }
}

// Calculate ownership percentage for edited owner
function calculateEditOwnershipPercentage() {
    const totalShares = <?= $property['total_shares'] ?? 0 ?>;
    const ownerShares = parseInt(document.getElementById('edit_owner_shares').value) || 0;
    
    const percentage = (ownerShares / totalShares) * 100;
    document.getElementById('edit_owner_percentage').value = percentage.toFixed(2) + '%';
}

// Remove owner
function removeOwner(ownerId) {
    if (confirm('Are you sure you want to remove this owner? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= site_url('landlord/properties/remove-owner/' . $property['id']) ?>/${ownerId}`;
        
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '<?= csrf_token() ?>';
        csrfField.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Form submissions
document.getElementById('addOwnerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitBtn.disabled = true;
    
    this.submit();
});

document.getElementById('editOwnerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    this.submit();
});

// Auto-dismiss alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('show')) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    });
}, 5000);
</script>

<style>
.owner-card {
    transition: all 0.3s ease;
}

.owner-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

.value {
    font-weight: 500;
    color: #5a5c69;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.table td, .table th {
    padding: 0.5rem;
    vertical-align: middle;
}

.table-borderless td {
    border: none;
}

.dropdown-toggle::after {
    margin-left: 0.255em;
}
</style>

<?= $this->endSection() ?>