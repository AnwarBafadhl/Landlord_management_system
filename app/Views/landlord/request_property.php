// Event listeners for form changes to enable/disable submit button
    const form = document.getElementById('propertyRequestForm');
    if (form) {
        form.addEventListener('input', updateSubmitButtonState);
        form.addEventListener('change', updateSubmitButtonState);
    }<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Request New Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Request New Property
        </h1>
        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Main Request Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Add New Property
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-plus-circle"></i>
                        <strong>Add Property:</strong> Fill out this form to add a new property to your portfolio.
                        Other landlords you add will automatically see this property in their accounts.
                    </div>

                    <form id="propertyRequestForm">
                        <?= csrf_field() ?>

                        <!-- Basic Property Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name" required
                                    placeholder="e.g., Sunset Apartments, Downtown Condo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_type" class="form-label">Property Type *</label>
                                <select class="form-control" id="property_type" name="property_type" required>
                                    <option value="">Select Property Type</option>
                                    <option value="rest_house">Rest House</option>
                                    <option value="chalet">Chalet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Property Address -->
                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="3"
                                required
                                placeholder="Enter the complete property address including street, city, state, and ZIP code"></textarea>
                        </div>

                        <!-- Landlords Section -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="number_of_landlords" class="form-label">Number of Landlords (Including You) *</label>
                                <input type="number" class="form-control" id="number_of_landlords"
                                    name="number_of_landlords" min="1" max="100" required value="1"
                                    placeholder="Enter number (e.g., 1, 5, 23)">
                                <small class="text-muted">Enter the total number of landlords including yourself. You will be automatically added as the first landlord.</small>
                            </div>
                        </div>

                        <!-- Landlords Section (Dynamic) -->
                        <div id="landlordsSection" style="display: block;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-users"></i> Landlords Information
                                </h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="distributeBtn"
                                    style="display: none;" onclick="distributeEqually()">
                                    <i class="fas fa-equals"></i> Distribute Equally
                                </button>
                            </div>

                            <div class="row" id="landlordsContainer">
                                <!-- Landlord fields will be generated here -->
                            </div>

                            <!-- Ownership Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="alert alert-info" id="ownershipSummary">
                                        <strong>Total Ownership:</strong> <span id="totalOwnership">0</span>%
                                        <div id="ownershipWarning" class="text-danger mt-2" style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i> Total must equal 100%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Units Section -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="number_of_units" class="form-label">Number of Units *</label>
                                <input type="number" class="form-control" id="number_of_units" name="number_of_units"
                                    min="1" max="500" required placeholder="Enter number of units (e.g., 1, 5, 20)">
                                <small class="text-muted">Enter any number from 1 to 500</small>
                            </div>
                        </div>

                        <!-- Units Details Section (Dynamic) -->
                        <div id="unitsSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-success">
                                    <i class="fas fa-home"></i> Units Information
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-outline-success btn-sm me-2"
                                        onclick="autoGenerateUnitNames()">
                                        <i class="fas fa-magic"></i> Auto Generate Names
                                    </button>
                                </div>
                            </div>

                            <div class="row" id="unitsContainer">
                                <!-- Unit fields will be generated here -->
                            </div>

                            <!-- Units Summary -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info" id="unitsSummary">
                                        <strong>Total Units:</strong> <span id="totalUnits">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <h6 class="mb-3 text-warning">
                            <i class="fas fa-dollar-sign"></i> Property Expenses
                        </h6>
                        
                        <div id="expensesSection">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label h6 mb-0">Monthly Expenses</label>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="addExpenseField()">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                            
                            <div id="expensesContainer">
                                <!-- Expense fields will be generated here -->
                            </div>
                            
                            <!-- Expenses Summary -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info" id="expensesSummary">
                                        <strong>Total Monthly Expenses:</strong> SAR <span id="totalExpenses">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Management Company -->
                        <h6 class="mb-3 text-info">
                            <i class="fas fa-building"></i> Property Management
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="management_company" class="form-label">Management Company (Optional)</label>
                                <input type="text" class="form-control" id="management_company"
                                    name="management_company" placeholder="e.g., ABC Property Management">
                                <small class="text-muted">Leave blank if self-managed</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="management_percentage" class="form-label">Management Fee %
                                    (Optional)</label>
                                <input type="number" class="form-control" id="management_percentage"
                                    name="management_percentage" step="0.1" min="0" max="50" value="0" placeholder="0">
                                <small class="text-muted">Enter 0 if no management fee</small>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" onclick="window.location.href='<?= site_url('landlord/properties') ?>'">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-plus"></i> Add Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

// Current user data - use simpler approach for now
let currentUser = {
    id: <?= session()->get('user_id') ?? 'null' ?>,
    name: 'Current User', // Will be updated when form loads
    username: '<?= session()->get('username') ?? '' ?>'
};

// Simple approach - get user data from PHP directly
<?php if (session()->get('user_id')): ?>
<?php 
$userModel = new \App\Models\UserModel();
$currentUserData = $userModel->find(session()->get('user_id'));
?>
currentUser.name = '<?= ($currentUserData['first_name'] ?? '') . ' ' . ($currentUserData['last_name'] ?? '') ?>'.trim() || 'Current User';
<?php endif; ?>

// Global functions for expenses management
window.addExpenseField = function() {
    const expensesContainer = document.getElementById('expensesContainer');
    const expenseCount = expensesContainer.children.length;
    
    const expenseDiv = document.createElement('div');
    expenseDiv.className = 'row mb-3 expense-item';
    expenseDiv.innerHTML = `
        <div class="col-md-5">
            <label class="form-label">Expense Name</label>
            <input type="text" class="form-control" name="expense_names[]" 
                   placeholder="e.g., Maintenance, Taxes, Insurance" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Amount (SAR)</label>
            <div class="input-group">
                <input type="number" class="form-control expense-amount" name="expense_amounts[]" 
                       step="0.01" min="0" required placeholder="500.00"
                       oninput="calculateTotalExpenses()">
                <div class="input-group-text">SAR</div>
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeExpenseField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    expensesContainer.appendChild(expenseDiv);
    calculateTotalExpenses();
};

window.removeExpenseField = function(button) {
    const expenseItem = button.closest('.expense-item');
    const expensesContainer = document.getElementById('expensesContainer');
    
    // Don't allow removing if it's the only expense
    if (expensesContainer.children.length > 1) {
        expenseItem.remove();
        calculateTotalExpenses();
    } else {
        alert('You must have at least one expense item.');
    }
};

// Function to calculate total expenses
function calculateTotalExpenses() {
    const expenseAmounts = document.querySelectorAll('.expense-amount');
    let total = 0;
    
    expenseAmounts.forEach(input => {
        const amount = parseFloat(input.value) || 0;
        total += amount;
    });
    
    const totalExpensesEl = document.getElementById('totalExpenses');
    if (totalExpensesEl) {
        totalExpensesEl.textContent = total.toFixed(2);
    }
}

// Global functions that need to be accessible from onclick handlers
window.autoGenerateUnitNames = function() {
    const unitNameInputs = document.querySelectorAll('input[name="unit_names[]"]');
    const propertyType = document.getElementById('property_type').value;

    unitNameInputs.forEach((input, index) => {
        const unitNumber = index + 1;
        let unitName = '';

        switch (propertyType) {
            case 'rest_house':
                unitName = `Room ${unitNumber}`;
                break;
            case 'chalet':
                unitName = `Unit ${unitNumber}`;
                break;
            default:
                unitName = `Unit ${String(unitNumber).padStart(3, '0')}`;
        }

        input.value = unitName;
    });
};

window.distributeEqually = function() {
    const ownershipInputs = document.querySelectorAll('input[name="ownership_percentages[]"]');
    const numberOfLandlords = ownershipInputs.length;

    if (numberOfLandlords > 0) {
        const basePercentage = Math.floor((100 / numberOfLandlords) * 100) / 100;
        const remainder = 100 - (basePercentage * numberOfLandlords);
        const extraCents = Math.round(remainder * 100);

        ownershipInputs.forEach((input, index) => {
            let percentage = basePercentage;

            if (index < extraCents) {
                percentage += 0.01;
            }

            input.value = percentage.toFixed(2);
        });

        calculateTotalOwnership();
    }
};

// Function to generate unit fields (updated - no rent fields)
function generateUnitFields(numberOfUnits) {
    const unitsContainer = document.getElementById('unitsContainer');
    const unitsSection = document.getElementById('unitsSection');

    if (!unitsContainer) {
        console.error('unitsContainer not found');
        return;
    }

    unitsContainer.innerHTML = '';

    if (numberOfUnits > 0) {
        unitsSection.style.display = 'block';

        for (let i = 1; i <= numberOfUnits; i++) {
            const unitDiv = document.createElement('div');
            unitDiv.className = 'col-md-6 mb-3';
            unitDiv.innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Unit ${i}</h6>
                        <div class="mb-2">
                            <label class="form-label">Unit Name *</label>
                            <input type="text" class="form-control" name="unit_names[]" 
                                   placeholder="e.g., Unit ${String(i).padStart(3, '0')}" required>
                        </div>
                    </div>
                </div>
            `;
            unitsContainer.appendChild(unitDiv);
        }
    } else {
        unitsSection.style.display = 'none';
    }

    updateUnitsSummary();
}

// Function to generate landlord fields (updated with username fields and current user auto-fill)
function generateLandlordFields(numberOfLandlords) {
    const landlordsContainer = document.getElementById('landlordsContainer');
    const landlordsSection = document.getElementById('landlordsSection');
    const distributeBtn = document.getElementById('distributeBtn');

    if (!landlordsContainer) {
        console.error('landlordsContainer not found');
        return;
    }

    landlordsContainer.innerHTML = '';

    if (numberOfLandlords > 0) {
        landlordsSection.style.display = 'block';
        
        if (numberOfLandlords > 1) {
            distributeBtn.style.display = 'inline-block';
        } else {
            distributeBtn.style.display = 'none';
        }

        for (let i = 1; i <= numberOfLandlords; i++) {
            const landlordDiv = document.createElement('div');
            landlordDiv.className = 'col-md-6 mb-3';
            
            // First landlord is always the current user
            const isCurrentUser = i === 1;
            const readonlyAttr = isCurrentUser ? 'readonly' : '';
            const bgClass = isCurrentUser ? 'bg-light' : '';
            
            landlordDiv.innerHTML = `
                <div class="card ${isCurrentUser ? 'border-primary' : ''}">
                    <div class="card-body">
                        <h6 class="card-title">
                            Landlord ${i} ${isCurrentUser ? '<span class="badge bg-primary">You</span>' : ''}
                        </h6>
                        <div class="mb-2">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control ${bgClass}" name="landlord_names[]" 
                                   placeholder="Enter landlord name" required ${readonlyAttr}
                                   value="${isCurrentUser ? currentUser.name : ''}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Username ${isCurrentUser ? '' : '(Optional)'}</label>
                            <input type="text" class="form-control ${bgClass}" name="landlord_usernames[]" 
                                   placeholder="${isCurrentUser ? 'Your username' : 'Enter username to share property'}" 
                                   ${readonlyAttr} value="${isCurrentUser ? currentUser.username : ''}">
                            <small class="text-muted">
                                ${isCurrentUser ? 'Your account username' : 'If username exists, property will be shared with that user'}
                            </small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Ownership % *</label>
                            <input type="number" class="form-control" name="ownership_percentages[]" 
                                   step="0.01" min="0" max="100" required
                                   placeholder="Enter ownership percentage"
                                   value="${numberOfLandlords === 1 ? '100' : ''}"
                                   oninput="calculateTotalOwnership()">
                        </div>
                    </div>
                </div>
            `;
            landlordsContainer.appendChild(landlordDiv);
        }
        
        // Calculate ownership after fields are added
        setTimeout(() => {
            calculateTotalOwnership();
            updateSubmitButtonState();
        }, 100);
    } else {
        landlordsSection.style.display = 'none';
    }
}

// Function to update units summary
function updateUnitsSummary() {
    const unitNameInputs = document.querySelectorAll('input[name="unit_names[]"]');
    const totalUnitsEl = document.getElementById('totalUnits');

    if (totalUnitsEl) {
        totalUnitsEl.textContent = unitNameInputs.length;
    }
}

// Function to calculate total ownership
function calculateTotalOwnership() {
    const ownershipInputs = document.querySelectorAll('input[name="ownership_percentages[]"]');
    let total = 0;

    ownershipInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });

    const totalOwnershipEl = document.getElementById('totalOwnership');
    const ownershipWarningEl = document.getElementById('ownershipWarning');
    const ownershipSummaryEl = document.getElementById('ownershipSummary');

    if (totalOwnershipEl) {
        totalOwnershipEl.textContent = total.toFixed(2);

        if (Math.abs(total - 100) < 0.01) {
            // Total is correct (100%)
            if (ownershipWarningEl) ownershipWarningEl.style.display = 'none';
            if (ownershipSummaryEl) ownershipSummaryEl.className = 'alert alert-success';
        } else {
            // Total is not 100%
            if (ownershipWarningEl) {
                ownershipWarningEl.style.display = 'block';
                if (total > 100) {
                    ownershipWarningEl.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> 
                        Total exceeds 100%! You need to reduce by ${(total - 100).toFixed(2)}%
                    `;
                } else if (total < 100) {
                    ownershipWarningEl.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> 
                        Total is less than 100%! You need to add ${(100 - total).toFixed(2)}%
                    `;
                }
            }
            if (ownershipSummaryEl) ownershipSummaryEl.className = 'alert alert-danger';
        }
    }

    updateSubmitButtonState();
}

function updateSubmitButtonState() {
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('propertyRequestForm');
    
    if (!submitBtn || !form) return;

    // Check if landlords exist by looking at actual input fields
    const landlordNameInputs = document.querySelectorAll('input[name="landlord_names[]"]');
    const ownershipInputs = document.querySelectorAll('input[name="ownership_percentages[]"]');
    
    if (landlordNameInputs.length === 0) {
        submitBtn.disabled = true;
        return;
    }

    // Check basic form validity
    const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
    let allFieldsValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            allFieldsValid = false;
        }
    });

    // Check ownership percentages are filled and total 100%
    let ownershipValid = true;
    let totalOwnership = 0;
    
    ownershipInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        if (value <= 0) {
            ownershipValid = false;
        }
        totalOwnership += value;
    });
    
    if (ownershipValid && Math.abs(totalOwnership - 100) >= 0.01) {
        ownershipValid = false;
    }

    submitBtn.disabled = !(allFieldsValid && ownershipValid);
}

document.addEventListener('DOMContentLoaded', function() {
    // Event listeners for number inputs
    const numberOfUnitsInput = document.getElementById('number_of_units');
    const numberOfLandlordsInput = document.getElementById('number_of_landlords');

    if (numberOfUnitsInput) {
        numberOfUnitsInput.addEventListener('input', function() {
            const numberOfUnits = parseInt(this.value);
            if (numberOfUnits >= 1 && numberOfUnits <= 500) {
                generateUnitFields(numberOfUnits);
            }
        });
    }

    if (numberOfLandlordsInput) {
        numberOfLandlordsInput.addEventListener('input', function() {
            const numberOfLandlords = parseInt(this.value);
            if (numberOfLandlords >= 1 && numberOfLandlords <= 100) {
                generateLandlordFields(numberOfLandlords);
            }
        });
    }

    // Event listeners for form changes to enable/disable submit button
    const form = document.getElementById('propertyRequestForm');
    if (form) {
        form.addEventListener('input', updateSubmitButtonState);
        form.addEventListener('change', updateSubmitButtonState);

        // Form submission handler
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate ownership total - check for actual landlord fields, not just container
            const landlordNameInputs = document.querySelectorAll('input[name="landlord_names[]"]');
            const ownershipInputs = document.querySelectorAll('input[name="ownership_percentages[]"]');
            
            if (landlordNameInputs.length === 0) {
                alert('Please add at least one landlord before submitting.');
                return;
            }
            
            // Check if all landlord names are filled
            let allNamesValid = true;
            landlordNameInputs.forEach(input => {
                if (!input.value.trim()) {
                    allNamesValid = false;
                }
            });
            
            if (!allNamesValid) {
                alert('Please fill in all landlord names.');
                return;
            }
            
            // Check if all ownership percentages are filled
            let allOwnershipValid = true;
            let totalOwnership = 0;
            ownershipInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                if (value <= 0) {
                    allOwnershipValid = false;
                }
                totalOwnership += value;
            });
            
            if (!allOwnershipValid) {
                alert('Please fill in all ownership percentages with values greater than 0.');
                return;
            }
            
            if (Math.abs(totalOwnership - 100) >= 0.01) {
                alert(`Total ownership percentage must equal 100%. Current total: ${totalOwnership.toFixed(2)}%`);
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Property...';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('<?= site_url('landlord/add-property') ?>', {
    method: 'POST',
    body: formData,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Property added successfully! Other landlords will now see this property in their accounts.');
                    window.location.href = '<?= site_url('landlord/properties') ?>';
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    updateSubmitButtonState();
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                
                let errorMessage = 'Unable to submit form. ';
                
                if (error.message.includes('503')) {
                    errorMessage += 'Server is temporarily unavailable. Please check if the add-property endpoint exists in your Landlord controller.';
                } else if (error.message.includes('HTML instead of JSON')) {
                    errorMessage += 'Server returned an error page instead of JSON response. Check server error logs for PHP/CodeIgniter errors.';
                } else if (error.message.includes('Server error')) {
                    errorMessage += error.message + '. Check server logs for details.';
                } else {
                    errorMessage += 'Please try again or contact support.';
                }
                
                alert(errorMessage);
                submitBtn.innerHTML = originalText;
                updateSubmitButtonState();
            });
        });
    }

    // Initialize with default values (1 landlord = current user)
    generateLandlordFields(1);
    generateUnitFields(1);
    
    // Add initial expense field
    addExpenseField();
    
    // Initial button state check with longer delay to ensure DOM is ready
    setTimeout(updateSubmitButtonState, 1000);
});
</script>

<style>
/* Enhanced styling for the form sections */
.bg-light-success {
    background-color: rgba(28, 200, 138, 0.1) !important;
}

.border-success {
    border-color: #1cc88a !important;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.btn-outline-success:hover {
    color: #fff;
    background-color: #1cc88a;
    border-color: #1cc88a;
}

.btn-outline-primary:hover {
    color: #fff;
    background-color: #4e73df;
    border-color: #4e73df;
}

/* Section headers styling */
h6.text-success,
h6.text-primary,
h6.text-warning,
h6.text-info {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid;
}

h6.text-success {
    border-bottom-color: #1cc88a;
}

h6.text-primary {
    border-bottom-color: #4e73df;
}

h6.text-warning {
    border-bottom-color: #f6c23e;
}

h6.text-info {
    border-bottom-color: #36b9cc;
}

/* Alert enhancements */
.alert {
    border-left: 0.25rem solid;
}

.alert-success {
    border-left-color: #1cc88a;
}

.alert-info {
    border-left-color: #36b9cc;
}

.alert-light {
    border-left-color: #858796;
}

.alert-danger {
    border-left-color: #e74a3b;
}

/* Current user styling */
.border-primary {
    border-color: #4e73df !important;
}

.badge {
    font-size: 0.65em;
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Form validation styling */
.is-valid {
    border-color: #1cc88a;
}

.is-invalid {
    border-color: #e74a3b;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-lg-3 {
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .btn-group .btn {
        margin-bottom: 0.25rem;
    }

    .d-md-flex {
        flex-direction: column !important;
        gap: 0.5rem;
    }

    .me-md-2 {
        margin-right: 0 !important;
    }
}

@media (max-width: 576px) {
    .col-lg-4,
    .col-md-6 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .card {
        margin-bottom: 1rem;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Loading animations */
.fa-spinner {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Number input styling */
input[type="number"] {
    -moz-appearance: textfield;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Enhanced button styling */
.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:disabled {
    transform: none;
    cursor: not-allowed;
}

/* Input group styling */
.input-group-text {
    font-weight: 500;
}

/* Card header improvements */
.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.card-header h6 {
    color: #5a5c69;
    margin-bottom: 0;
}

/* Form section spacing */
.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e3e6f0;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
</style>

<?= $this->endSection() ?>