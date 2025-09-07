<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Add New Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Add New Property
        </h1>
        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Properties
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

    <!-- Validation Errors -->
    <?php if (session()->get('validation')): ?>
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->get('validation')->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Property Information Form -->
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <form id="propertyForm" method="post" action="<?= site_url('landlord/add-property') ?>">
                <?= csrf_field() ?>

                <!-- Basic Property Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building"></i> Property Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name" 
                                       value="<?= old('property_name') ?>" required
                                       placeholder="e.g., Sunset Villa, Downtown Complex">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_value" class="form-label">Property Value (SAR) *</label>
                                <input type="number" class="form-control" id="property_value" name="property_value"
                                       value="<?= old('property_value') ?>" required min="1" step="0.01" 
                                       placeholder="e.g., 500000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" 
                                      rows="2" required placeholder="Enter full property address"><?= old('property_address') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="total_shares" class="form-label">Total Shares *</label>
                                <input type="number" class="form-control" id="total_shares" name="total_shares"
                                       value="<?= old('total_shares', 100) ?>" required min="1" max="10000" 
                                       placeholder="e.g., 100">
                                <small class="form-text text-muted">Total shares for the property</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contribution_duration" class="form-label">Contribution Duration (Months) *</label>
                                <input type="number" class="form-control" id="contribution_duration" name="contribution_duration"
                                       value="<?= old('contribution_duration', 12) ?>" required min="1" max="360" 
                                       placeholder="e.g., 12">
                                <small class="form-text text-muted">Investment period in months</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_units" class="form-label">Total Units *</label>
                                <input type="number" class="form-control" id="total_units" name="total_units"
                                       value="<?= old('total_units', 1) ?>" required min="1" max="500" 
                                       placeholder="e.g., 4">
                                <small class="form-text text-muted">Number of rental units</small>
                            </div>
                        </div>

                        <!-- Management Information -->
                        <h5 class="mb-3 mt-4">
                            <i class="fas fa-cogs"></i> Management Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="management_company" class="form-label">Management Company</label>
                                <input type="text" class="form-control" id="management_company" name="management_company"
                                       value="<?= old('management_company') ?>" 
                                       placeholder="Leave empty for self-management">
                                <small class="form-text text-muted">Optional: Leave blank for self-management</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="management_percentage" class="form-label">Management Fee (%)</label>
                                <input type="number" class="form-control" id="management_percentage" name="management_percentage"
                                       value="<?= old('management_percentage', 0) ?>" min="0" max="50" step="0.1" 
                                       placeholder="e.g., 5.0">
                                <small class="form-text text-muted">Percentage of rental income (0-50%)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: Shareholders Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-users"></i> Property Shareholders
                        </h6>
                        <button type="button" class="btn btn-outline-success btn-sm" id="addShareholderBtn">
                            <i class="fas fa-plus"></i> Add Shareholder
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> You will be added as the primary owner. Add other shareholders below if this is a shared property.
                        </div>

                        <!-- Primary Owner (Current User) -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-crown"></i> Primary Owner (You)
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="primary_owner_name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" id="primary_owner_name" name="primary_owner_name" 
                                           value="<?= old('primary_owner_name', trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''))) ?>" 
                                           required placeholder="Your full name">
                                </div>
                                <div class="col-md-6">
                                    <label for="primary_owner_shares" class="form-label">Your Shares *</label>
                                    <input type="number" class="form-control" id="primary_owner_shares" name="primary_owner_shares"
                                           value="<?= old('primary_owner_shares', 100) ?>" required min="1" max="10000" 
                                           placeholder="Number of shares you own">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Shareholders -->
                        <div id="shareholdersContainer">
                            <h6 class="text-secondary mb-3">
                                <i class="fas fa-user-friends"></i> Additional Shareholders
                                <small class="text-muted">(Optional)</small>
                            </h6>
                            <!-- Additional shareholders will be added here dynamically -->
                        </div>

                        <!-- Shares Summary -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="text-xs text-gray-500">Total Shares</div>
                                    <div class="font-weight-bold text-primary" id="total-shares-display">100</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-xs text-gray-500">Allocated Shares</div>
                                    <div class="font-weight-bold text-success" id="allocated-shares-display">100</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-xs text-gray-500">Available Shares</div>
                                    <div class="font-weight-bold text-warning" id="available-shares-display">0</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-xs text-gray-500">Share Value</div>
                                    <div class="font-weight-bold text-info" id="share-value-display">SAR 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Units Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-door-open"></i> Property Units
                        </h6>
                        <button type="button" class="btn btn-outline-info btn-sm" id="addUnitBtn">
                            <i class="fas fa-plus"></i> Add Unit
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="unitsContainer">
                            <div class="unit-input mb-2">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Unit 1</span>
                                    </div>
                                    <input type="text" class="form-control" name="unit_names[]" 
                                           value="<?= old('unit_names.0') ?>"
                                           placeholder="e.g., Apartment A, Unit 101" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-file-contract"></i> Terms & Conditions *
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="agree_terms" name="agree_terms" required>
                            <label class="custom-control-label" for="agree_terms">
                                <strong>I agree to the property investment terms and conditions *</strong>, including:
                                <ul class="mt-2 mb-0 small text-muted">
                                    <li>All shareholders contribute proportionally to property expenses</li>
                                    <li>Rental income distributed according to ownership percentages</li>
                                    <li>Major decisions require majority shareholder approval</li>
                                    <li>Share transfers must be approved by existing shareholders</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    All fields marked with * are required
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-save"></i> Create Property
                                </button>
                                <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary btn-lg ml-2">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let unitCount = 1;
    let shareholderCount = 0;
    
    const totalUnitsInput = document.getElementById('total_units');
    const totalSharesInput = document.getElementById('total_shares');
    const propertyValueInput = document.getElementById('property_value');
    const primaryOwnerSharesInput = document.getElementById('primary_owner_shares');
    
    const unitsContainer = document.getElementById('unitsContainer');
    const shareholdersContainer = document.getElementById('shareholdersContainer');
    
    const addUnitBtn = document.getElementById('addUnitBtn');
    const addShareholderBtn = document.getElementById('addShareholderBtn');

    // Add unit functionality
    addUnitBtn.addEventListener('click', function() {
        unitCount++;
        
        const unitDiv = document.createElement('div');
        unitDiv.className = 'unit-input mb-2';
        unitDiv.innerHTML = `
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Unit ${unitCount}</span>
                </div>
                <input type="text" class="form-control" name="unit_names[]" 
                       placeholder="e.g., Apartment ${String.fromCharCode(64 + unitCount)}, Unit ${100 + unitCount}" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-danger" type="button" onclick="removeUnit(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        unitsContainer.appendChild(unitDiv);
        totalUnitsInput.value = unitCount;
    });

    // Add shareholder functionality
    addShareholderBtn.addEventListener('click', function() {
        shareholderCount++;
        
        const shareholderDiv = document.createElement('div');
        shareholderDiv.className = 'shareholder-input mb-3 p-3 border rounded';
        shareholderDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-secondary mb-0">Shareholder ${shareholderCount}</h6>
                <button class="btn btn-outline-danger btn-sm" type="button" onclick="removeShareholder(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="shareholders[${shareholderCount}][name]" 
                           placeholder="Shareholder full name" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="shareholders[${shareholderCount}][email]" 
                           placeholder="shareholder@email.com" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shares *</label>
                    <input type="number" class="form-control shareholder-shares" name="shareholders[${shareholderCount}][shares]" 
                           placeholder="Number of shares" required min="1" onchange="updateSharesCalculation()">
                </div>
            </div>
        `;
        
        shareholdersContainer.appendChild(shareholderDiv);
        updateSharesCalculation();
    });

    // Update shares calculation
    function updateSharesCalculation() {
        const totalShares = parseInt(totalSharesInput.value) || 0;
        const primaryShares = parseInt(primaryOwnerSharesInput.value) || 0;
        const propertyValue = parseFloat(propertyValueInput.value) || 0;
        
        let additionalShares = 0;
        document.querySelectorAll('.shareholder-shares').forEach(input => {
            additionalShares += parseInt(input.value) || 0;
        });
        
        const allocatedShares = primaryShares + additionalShares;
        const availableShares = Math.max(0, totalShares - allocatedShares);
        const shareValue = totalShares > 0 && propertyValue > 0 ? propertyValue / totalShares : 0;
        
        // Update displays
        document.getElementById('total-shares-display').textContent = totalShares.toLocaleString();
        document.getElementById('allocated-shares-display').textContent = allocatedShares.toLocaleString();
        document.getElementById('available-shares-display').textContent = availableShares.toLocaleString();
        document.getElementById('share-value-display').textContent = `SAR ${shareValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        // Color coding for available shares
        const availableSharesEl = document.getElementById('available-shares-display');
        availableSharesEl.className = availableShares === 0 ? 'font-weight-bold text-success' : 
                                     availableShares < 0 ? 'font-weight-bold text-danger' : 'font-weight-bold text-warning';
    }

    // Event listeners for share calculation
    totalSharesInput.addEventListener('input', updateSharesCalculation);
    propertyValueInput.addEventListener('input', updateSharesCalculation);
    primaryOwnerSharesInput.addEventListener('input', updateSharesCalculation);
    
    // Make function globally available
    window.updateSharesCalculation = updateSharesCalculation;
    
    // Initial calculation
    updateSharesCalculation();

    // Auto-sync primary owner shares with total shares initially
    totalSharesInput.addEventListener('input', function() {
        if (shareholderCount === 0) {
            primaryOwnerSharesInput.value = this.value;
            updateSharesCalculation();
        }
    });

    // Auto-fill management fields
    document.getElementById('management_company').addEventListener('input', function() {
        const managementPercentageInput = document.getElementById('management_percentage');
        
        if (this.value.trim() && managementPercentageInput.value == 0) {
            managementPercentageInput.value = '5.0';
        } else if (!this.value.trim()) {
            managementPercentageInput.value = '0';
        }
    });
});

// Global functions for removing elements
function removeUnit(button) {
    const unitDiv = button.closest('.unit-input');
    const unitInputs = document.querySelectorAll('.unit-input');
    
    if (unitInputs.length <= 1) {
        alert('You must have at least one unit.');
        return;
    }
    
    unitDiv.remove();
    
    // Update unit numbers and count
    const remainingInputs = document.querySelectorAll('.unit-input');
    remainingInputs.forEach((input, index) => {
        const span = input.querySelector('.input-group-text');
        span.textContent = `Unit ${index + 1}`;
    });
    
    document.getElementById('total_units').value = remainingInputs.length;
    unitCount = remainingInputs.length;
}

function removeShareholder(button) {
    const shareholderDiv = button.closest('.shareholder-input');
    shareholderDiv.remove();
    
    // Update shareholder numbers
    const remainingInputs = document.querySelectorAll('.shareholder-input');
    remainingInputs.forEach((input, index) => {
        const title = input.querySelector('h6');
        title.textContent = `Shareholder ${index + 1}`;
    });
    
    shareholderCount = remainingInputs.length;
    updateSharesCalculation();
}

// Form validation and submission
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    const agreeCheckbox = document.getElementById('agree_terms');
    const submitBtn = document.getElementById('submitBtn');
    
    // Check agreement checkbox
    if (!agreeCheckbox.checked) {
        e.preventDefault();
        alert('Please agree to the terms and conditions to proceed.');
        agreeCheckbox.focus();
        return false;
    }
    
    // Validate shares allocation
    const totalShares = parseInt(document.getElementById('total_shares').value) || 0;
    const primaryShares = parseInt(document.getElementById('primary_owner_shares').value) || 0;
    
    let additionalShares = 0;
    document.querySelectorAll('.shareholder-shares').forEach(input => {
        additionalShares += parseInt(input.value) || 0;
    });
    
    const allocatedShares = primaryShares + additionalShares;
    
    if (allocatedShares > totalShares) {
        e.preventDefault();
        alert(`Total allocated shares (${allocatedShares}) cannot exceed total shares (${totalShares}). Please adjust the share distribution.`);
        return false;
    }
    
    if (primaryShares <= 0) {
        e.preventDefault();
        alert('You must own at least 1 share as the primary owner.');
        document.getElementById('primary_owner_shares').focus();
        return false;
    }
    
    // Validate units
    const unitInputs = document.querySelectorAll('input[name="unit_names[]"]');
    let hasEmptyUnit = false;
    
    unitInputs.forEach(input => {
        if (!input.value.trim()) {
            hasEmptyUnit = true;
        }
    });
    
    if (hasEmptyUnit) {
        e.preventDefault();
        alert('Please fill in all unit names or remove empty units.');
        return false;
    }
    
    // Validate management percentage if company is provided
    const managementCompany = document.getElementById('management_company').value.trim();
    const managementPercentage = parseFloat(document.getElementById('management_percentage').value) || 0;
    
    if (managementCompany && managementPercentage <= 0) {
        e.preventDefault();
        alert('Please enter a management percentage greater than 0 if you specified a management company.');
        document.getElementById('management_percentage').focus();
        return false;
    }
    
    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Property...';
    
    // Re-enable button after 15 seconds as fallback
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Property';
    }, 15000);
});
</script>

<style>
.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.btn-outline-success:hover, .btn-outline-info:hover {
    color: white;
}

.unit-input, .shareholder-input {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-control-label::before {
    border-color: #4e73df;
}

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #4e73df;
    border-color: #4e73df;
}

.input-group-text {
    background-color: #f8f9fc;
    border-color: #d1d3e2;
    color: #5a5c69;
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 0.35rem;
}

.card {
    border: none;
    border-radius: 0.35rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
}

.border {
    border-color: #e3e6f0 !important;
}

.bg-light {
    background-color: #f8f9fc !important;
}
</style>
<?= $this->endSection() ?>