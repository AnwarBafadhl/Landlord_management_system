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
            <!-- Edit Property Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Edit Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <form id="editPropertyForm" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        
                        <!-- Debug: Show CSRF info -->
                        <div class="alert alert-info" style="font-size: 12px;">
                            <strong>Debug Info:</strong> 
                            CSRF Token: <?= csrf_token() ?> | 
                            CSRF Hash: <?= csrf_hash() ?>
                        </div>
                        
                        <!-- Basic Property Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name" 
                                       value="<?= esc($property['property_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_type" class="form-label">Property Type *</label>
                                <select class="form-control" id="property_type" name="property_type" required>
                                    <option value="">Select Property Type</option>
                                    <option value="rest_house" <?= $property['property_type'] === 'rest_house' ? 'selected' : '' ?>>Rest House</option>
                                    <option value="chalet" <?= $property['property_type'] === 'chalet' ? 'selected' : '' ?>>Chalet</option>
                                    <option value="other" <?= $property['property_type'] === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="property_address" class="form-label">Property Address *</label>
                                <textarea class="form-control" id="property_address" name="property_address" rows="3" required><?= esc($property['address']) ?></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="number_of_units" class="form-label">Number of Units *</label>
                                <input type="number" class="form-control" id="number_of_units" name="number_of_units" 
                                       value="<?= $property['number_of_units'] ?>" min="1" max="500" required>
                            </div>
                        </div>

                        <!-- Management Information -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="management_company" class="form-label">Management Company (Optional)</label>
                                <input type="text" class="form-control" id="management_company" name="management_company" 
                                       value="<?= esc($property['management_company'] ?? '') ?>" placeholder="Leave empty if self-managed">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="management_percentage" class="form-label">Management Fee (%)</label>
                                <input type="number" class="form-control" id="management_percentage" name="management_percentage" 
                                       value="<?= $property['management_percentage'] ?? 0 ?>" min="0" max="50" step="0.01">
                            </div>
                        </div>

                        <!-- Property Units -->
                        <div class="card bg-light-success border-success mb-4">
                            <div class="card-header">
                                <h6 class="text-success mb-0">
                                    <i class="fas fa-door-open"></i> Property Units
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="unitsContainer">
                                    <?php if (!empty($units)): ?>
                                        <?php foreach ($units as $index => $unit): ?>
                                            <div class="row mb-2 unit-row">
                                                <div class="col-md-10">
                                                    <input type="text" class="form-control" name="unit_names[]" 
                                                           value="<?= esc($unit['unit_name']) ?>" placeholder="Unit Name" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-block" onclick="removeUnitField(this)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="row mb-2 unit-row">
                                            <div class="col-md-10">
                                                <input type="text" class="form-control" name="unit_names[]" placeholder="Unit Name" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-block" onclick="removeUnitField(this)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline-success" onclick="addUnitField()">
                                    <i class="fas fa-plus"></i> Add Unit
                                </button>
                            </div>
                        </div>

                        <!-- Property Expenses -->
                        <div class="card bg-light-warning border-warning mb-4">
                            <div class="card-header">
                                <h6 class="text-warning mb-0">
                                    <i class="fas fa-dollar-sign"></i> Property Expenses
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="expensesContainer">
                                    <?php if (!empty($expenses)): ?>
                                        <?php foreach ($expenses as $index => $expense): ?>
                                            <div class="row mb-2 expense-row">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="expense_names[]" 
                                                           value="<?= esc($expense['expense_name']) ?>" placeholder="Expense Name" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control" name="expense_amounts[]" 
                                                           value="<?= $expense['expense_amount'] ?>" placeholder="Amount" step="0.01" min="0" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-block" onclick="removeExpenseField(this)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="row mb-2 expense-row">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="expense_names[]" placeholder="Expense Name" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" class="form-control" name="expense_amounts[]" placeholder="Amount" step="0.01" min="0" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-block" onclick="removeExpenseField(this)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline-warning" onclick="addExpenseField()">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                        </div>

                        <!-- Current Ownership (Read-only) -->
                        <div class="card bg-light-info border-info mb-4">
                            <div class="card-header">
                                <h6 class="text-info mb-0">
                                    <i class="fas fa-users"></i> Current Ownership (Cannot be modified)
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($owners)): ?>
                                    <?php foreach ($owners as $owner): ?>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" readonly 
                                                       value="<?= esc($owner['landlord_name']) ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" readonly 
                                                       value="<?= esc($owner['username'] ?? 'N/A') ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" readonly 
                                                       value="<?= $owner['ownership_percentage'] ?>%">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Ownership cannot be modified here. Contact admin to change ownership structure.
                                </small>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg" id="updateBtn">
                                    <i class="fas fa-save"></i> Update Property
                                </button>
                                <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addUnitField() {
    const container = document.getElementById('unitsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-2 unit-row';
    newRow.innerHTML = `
        <div class="col-md-10">
            <input type="text" class="form-control" name="unit_names[]" placeholder="Unit Name" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-block" onclick="removeUnitField(this)">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

function removeUnitField(button) {
    const container = document.getElementById('unitsContainer');
    if (container.children.length > 1) {
        button.closest('.unit-row').remove();
    } else {
        alert('At least one unit is required.');
    }
}

function addExpenseField() {
    const container = document.getElementById('expensesContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-2 expense-row';
    newRow.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control" name="expense_names[]" placeholder="Expense Name" required>
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control" name="expense_amounts[]" placeholder="Amount" step="0.01" min="0" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-block" onclick="removeExpenseField(this)">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

function removeExpenseField(button) {
    const container = document.getElementById('expensesContainer');
    if (container.children.length > 1) {
        button.closest('.expense-row').remove();
    } else {
        alert('At least one expense is required.');
    }
}

document.getElementById('editPropertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('updateBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    const propertyId = formData.get('property_id');
    
    // Debug: Log form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    fetch(`<?= site_url('landlord/properties/update') ?>/${propertyId}`, {
        method: 'POST',
        body: formData
        // Don't add custom headers - let FormData handle CSRF automatically
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Error response body:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            alert('Property updated successfully!');
            window.location.href = `<?= site_url('landlord/properties/view') ?>/${propertyId}`;
        } else {
            alert('Error: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        alert('Error updating property: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<style>
.bg-light-success {
    background-color: rgba(28, 200, 138, 0.1) !important;
}

.bg-light-warning {
    background-color: rgba(246, 194, 62, 0.1) !important;
}

.bg-light-info {
    background-color: rgba(54, 185, 204, 0.1) !important;
}

.border-success {
    border-color: #1cc88a !important;
}

.border-warning {
    border-color: #f6c23e !important;
}

.border-info {
    border-color: #36b9cc !important;
}
</style>

<?= $this->endSection() ?>