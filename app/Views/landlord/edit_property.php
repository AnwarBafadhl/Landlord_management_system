<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Edit Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Property
        </h1>
        <div>
            <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Warning Notice -->
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> Changes to share structure will affect all owners. Ensure all stakeholders are informed before making modifications.
            </div>

            <!-- Edit Property Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <form id="editPropertyForm" method="post" action="<?= site_url('landlord/properties/update/' . $property['id']) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        
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
                            <textarea class="form-control" id="property_address" name="property_address" rows="3" required><?= esc($property['address']) ?></textarea>
                        </div>

                        <!-- Share Information -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-info">
                                    <i class="fas fa-chart-pie"></i> Share Structure
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Changing the total number of shares will recalculate all ownership percentages. Current owners' share counts will remain the same.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="total_shares" class="form-label">Total Number of Shares *</label>
                                        <input type="number" class="form-control" id="total_shares" name="total_shares" 
                                               value="<?= $property['total_shares'] ?? 100 ?>" required min="1" max="10000"
                                               onchange="calculateShareValue()">
                                        <small class="text-muted">Current allocated: <?= $totalAllocatedShares ?? 0 ?> shares</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="share_value" class="form-label">Share Value (SAR) *</label>
                                        <input type="number" class="form-control" id="share_value" name="share_value" 
                                               value="<?= $property['share_value'] ?? 0 ?>" step="0.01" readonly>
                                        <small class="text-muted">Calculated automatically</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="contribution_duration" class="form-label">Contribution Duration (Months) *</label>
                                        <input type="number" class="form-control" id="contribution_duration" name="contribution_duration" 
                                               value="<?= $property['contribution_duration'] ?? 12 ?>" required min="1" max="360">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Management Information -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="management_company" class="form-label">Management Company *</label>
                                <input type="text" class="form-control" id="management_company" name="management_company" 
                                       value="<?= esc($property['management_company'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="management_percentage" class="form-label">Management Percentage (%) *</label>
                                <input type="number" class="form-control" id="management_percentage" name="management_percentage" 
                                       value="<?= $property['management_percentage'] ?? 0 ?>" required min="0" max="50" step="0.1">
                            </div>
                        </div>

                        <!-- Owners Preview Section -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-success">
                                    <i class="fas fa-users"></i> Current Owners
                                    <span class="badge badge-info ml-2"><?= count($owners ?? []) ?> owners</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($owners) && is_array($owners)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Owner Name</th>
                                                    <th>Email</th>
                                                    <th>Shares</th>
                                                    <th>Current %</th>
                                                    <th>New %</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ownersPreviewTable">
                                                <?php foreach ($owners as $owner): ?>
                                                    <tr>
                                                        <td>
                                                            <?= esc($owner['name']) ?>
                                                            <?php if ($owner['is_current_user'] ?? false): ?>
                                                                <span class="badge badge-primary badge-sm">You</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= esc($owner['email']) ?></td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                <?= number_format($owner['shares'] ?? 0) ?>
                                                            </span>
                                                        </td>
                                                        <td class="current-percentage">
                                                            <?= number_format($owner['ownership_percentage'] ?? 0, 2) ?>%
                                                        </td>
                                                        <td class="new-percentage text-primary" data-shares="<?= $owner['shares'] ?? 0 ?>">
                                                            <?= number_format($owner['ownership_percentage'] ?? 0, 2) ?>%
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-calculator"></i>
                                        <strong>Impact Preview:</strong> The "New %" column shows how ownership percentages will change with the updated total shares.
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No owners found for this property.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status and Notes -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Property Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" <?= ($property['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($property['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="pending" <?= ($property['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Updated</label>
                                <input type="text" class="form-control" value="<?= date('M d, Y H:i', strtotime($property['updated_at'] ?? $property['created_at'])) ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description/Notes</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                placeholder="Add any additional notes or description about this property..."><?= esc($property['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Form Actions -->
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
                                        <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate share value and update ownership percentages
function calculateShareValue() {
    const propertyValue = parseFloat(document.getElementById('property_value').value) || 0;
    const totalShares = parseInt(document.getElementById('total_shares').value) || 1;
    
    // Calculate and update share value
    const shareValue = propertyValue / totalShares;
    document.getElementById('share_value').value = shareValue.toFixed(2);
    
    // Update ownership percentages preview
    updateOwnershipPercentagesPreview();
}

// Update ownership percentages preview in the table
function updateOwnershipPercentagesPreview() {
    const totalShares = parseInt(document.getElementById('total_shares').value) || 1;
    const percentageCells = document.querySelectorAll('.new-percentage');
    
    percentageCells.forEach(cell => {
        const ownerShares = parseInt(cell.getAttribute('data-shares')) || 0;
        const newPercentage = (ownerShares / totalShares) * 100;
        cell.textContent = newPercentage.toFixed(2) + '%';
        
        // Highlight if percentage changed significantly
        const currentPercentage = parseFloat(cell.parentNode.querySelector('.current-percentage').textContent);
        const percentageDiff = Math.abs(newPercentage - currentPercentage);
        
        if (percentageDiff > 0.1) {
            cell.classList.add('font-weight-bold');
            if (newPercentage > currentPercentage) {
                cell.classList.add('text-success');
                cell.classList.remove('text-danger', 'text-primary');
            } else {
                cell.classList.add('text-danger');
                cell.classList.remove('text-success', 'text-primary');
            }
        } else {
            cell.classList.remove('font-weight-bold', 'text-success', 'text-danger');
            cell.classList.add('text-primary');
        }
    });
}

// Validate total shares against allocated shares
function validateShares() {
    const totalShares = parseInt(document.getElementById('total_shares').value) || 0;
    const allocatedShares = <?= $totalAllocatedShares ?? 0 ?>;
    const submitBtn = document.getElementById('updateBtn');
    
    if (totalShares < allocatedShares) {
        document.getElementById('total_shares').classList.add('is-invalid');
        submitBtn.disabled = true;
        
        // Show error message
        let errorDiv = document.getElementById('shares-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'shares-error';
            errorDiv.className = 'invalid-feedback';
            document.getElementById('total_shares').parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = `Total shares cannot be less than allocated shares (${allocatedShares}).`;
    } else {
        document.getElementById('total_shares').classList.remove('is-invalid');
        const errorDiv = document.getElementById('shares-error');
        if (errorDiv) {
            errorDiv.remove();
        }
        submitBtn.disabled = false;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initial calculations
    calculateShareValue();
    
    // Share validation
    document.getElementById('total_shares').addEventListener('input', function() {
        validateShares();
        calculateShareValue();
    });
    
    // Property value changes
    document.getElementById('property_value').addEventListener('input', calculateShareValue);
});

// Form submission
document.getElementById('editPropertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('updateBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    // Validate before submission
    const totalShares = parseInt(document.getElementById('total_shares').value) || 0;
    const allocatedShares = <?= $totalAllocatedShares ?? 0 ?>;
    
    if (totalShares < allocatedShares) {
        alert('Total shares cannot be less than currently allocated shares.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    // Show confirmation for significant changes
    const percentageCells = document.querySelectorAll('.new-percentage');
    let hasSignificantChanges = false;
    
    percentageCells.forEach(cell => {
        const currentPercentage = parseFloat(cell.parentNode.querySelector('.current-percentage').textContent);
        const newPercentage = parseFloat(cell.textContent);
        const percentageDiff = Math.abs(newPercentage - currentPercentage);
        
        if (percentageDiff > 0.1) {
            hasSignificantChanges = true;
        }
    });
    
    if (hasSignificantChanges) {
        if (!confirm('This update will change ownership percentages for existing owners. Are you sure you want to continue?')) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }
    }
    
    // Submit the form
    this.submit();
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.badge-sm {
    font-size: 0.7em;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.85rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.invalid-feedback {
    display: block;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.alert {
    border: none;
    border-radius: 0.35rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-primary {
    color: #4e73df !important;
}

.text-success {
    color: #1cc88a !important;
}

.text-danger {
    color: #e74a3b !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}
</style>

<?= $this->endSection() ?>