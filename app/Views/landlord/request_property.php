<?= $this->extend('layouts/landlord') ?>

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
                        The property will be added immediately to your account.
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
                                <label for="number_of_landlords" class="form-label">Number of Landlords *</label>
                                <input type="number" class="form-control" id="number_of_landlords"
                                    name="number_of_landlords" min="1" max="100" required
                                    placeholder="Enter number (e.g., 1, 5, 23)">
                                <small class="text-muted">Enter any number from 1 to 100</small>
                            </div>
                        </div>

                        <!-- Property Address -->
                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="3"
                                required
                                placeholder="Enter the complete property address including street, city, state, and ZIP code"></textarea>
                        </div>

                        <!-- Units Section -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="number_of_units" class="form-label">Number of Units *</label>
                                <input type="number" class="form-control" id="number_of_units" name="number_of_units" 
                                       min="1" max="500" required placeholder="Enter number of units (e.g., 1, 5, 20)">
                                <small class="text-muted">Enter any number from 1 to 500</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit_type" class="form-label">Unit Type</label>
                                <select class="form-control" id="unit_type" name="unit_type">
                                    <option value="apartment">Apartment</option>
                                    <option value="condo">Condo</option>
                                    <option value="studio">Studio</option>
                                    <option value="house">House</option>
                                    <option value="room">Room</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Units Details Section (Dynamic) -->
                        <div id="unitsSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-success">
                                    <i class="fas fa-home"></i> Units Information
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="autoGenerateUnitNames()">
                                        <i class="fas fa-magic"></i> Auto Generate Names
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="setUniformRent()">
                                        <i class="fas fa-equals"></i> Set Same Rent
                                    </button>
                                </div>
                            </div>

                            <div id="unitsContainer">
                                <!-- Unit fields will be generated here -->
                            </div>

                            <!-- Units Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="alert alert-info" id="unitsSummary">
                                        <strong>Total Units:</strong> <span id="totalUnits">0</span><br>
                                        <strong>Total Monthly Income:</strong> $<span id="totalMonthlyIncome">0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-light">
                                        <small>
                                            <strong>Quick Tips:</strong><br>
                                            • Use "Auto Generate Names" for standard naming<br>
                                            • Use "Set Same Rent" for uniform pricing<br>
                                            • All units must have names and rent amounts
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Landlords Section (Dynamic) -->
                        <div id="landlordsSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-users"></i> Landlord Information
                                </h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="distributeBtn"
                                    style="display: none;" onclick="distributeEqually()">
                                    <i class="fas fa-equals"></i> Distribute Equally
                                </button>
                            </div>

                            <div id="landlordsContainer">
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
                                <div class="col-md-6">
                                    <div class="alert alert-light">
                                        <small>
                                            <strong>Quick Tips:</strong><br>
                                            • Use "Distribute Equally" to auto-calculate<br>
                                            • Total must equal exactly 100%<br>
                                            • You can enter decimals (e.g., 33.33%)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <h6 class="mb-3 text-warning">
                            <i class="fas fa-dollar-sign"></i> Financial Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expenses" class="form-label">Monthly Expenses *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="expenses" name="expenses" min="0"
                                        step="0.01" required placeholder="500.00">
                                    <div class="input-group-text">SAR</div>
                                </div>
                                <small class="text-muted">Include maintenance, taxes, insurance, utilities, etc.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estimated_property_value" class="form-label">Estimated Property Value</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="estimated_property_value" 
                                           name="estimated_property_value" min="0" step="1000" placeholder="500000.00">
                                    <div class="input-group-text">SAR</div>
                                </div>
                                <small class="text-muted">Optional: Current market value estimate</small>
                            </div>
                        </div>
                        
                        <!-- Income Summary (Read-only display) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Monthly Income:</strong><br>
                                            <span class="h5 text-success">SAR <span id="displayTotalIncome">0.00</span></span>
                                            <br><small class="text-muted">From all units combined</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Monthly Expenses:</strong><br>
                                            <span class="h5 text-warning">SAR <span id="displayExpenses">0.00</span></span>
                                            <br><small class="text-muted">Operating costs</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Net Monthly Income:</strong><br>
                                            <span class="h5 text-primary">SAR <span id="displayNetIncome">0.00</span></span>
                                            <br><small class="text-muted">Income - Expenses</small>
                                        </div>
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
                                <label for="management_company" class="form-label">Property Management Company *</label>
                                <input type="text" class="form-control" id="management_company"
                                    name="management_company" required
                                    placeholder="e.g., ABC Property Management or Self-Managed">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="management_percentage" class="form-label">Management Company Percentage *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="management_percentage"
                                        name="management_percentage" min="0" max="50" step="0.1" required
                                        placeholder="5.0">
                                    <div class="input-group-text">%</div>
                                </div>
                                <small class="text-muted">Enter 0 if self-managed</small>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" onclick="history.back()">
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

<!-- Set Uniform Rent Modal -->
<div class="modal fade" id="uniformRentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-equals"></i> Set Uniform Rent
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="uniform_rent_amount" class="form-label">Rent Amount for All Units *</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="uniform_rent_amount" 
                               min="1" step="0.01" placeholder="1200.00" required>
                        <div class="input-group-text">SAR</div>
                    </div>
                    <small class="text-muted">This amount will be applied to all units</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="applyUniformRent()">
                    <i class="fas fa-check"></i> Apply to All Units
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Generate landlord fields based on number entered
        document.getElementById('number_of_landlords').addEventListener('input', function () {
            const numberOfLandlords = parseInt(this.value);

            // Validate input
            if (numberOfLandlords < 1) {
                this.value = 1;
                return;
            }
            if (numberOfLandlords > 100) {
                this.value = 100;
                showFieldWarning(this, 'Maximum 100 landlords allowed');
                return;
            }

            generateLandlordFields(numberOfLandlords);
        });

        // Generate unit fields based on number entered
        document.getElementById('number_of_units').addEventListener('input', function () {
            const numberOfUnits = parseInt(this.value);

            // Validate input
            if (numberOfUnits < 1) {
                this.value = 1;
                return;
            }
            if (numberOfUnits > 500) {
                this.value = 500;
                showFieldWarning(this, 'Maximum 500 units allowed');
                return;
            }

            generateUnitFields(numberOfUnits);
        });

        // Also trigger on blur to handle copy-paste
        document.getElementById('number_of_landlords').addEventListener('blur', function () {
            const numberOfLandlords = parseInt(this.value);
            if (numberOfLandlords && numberOfLandlords >= 1 && numberOfLandlords <= 100) {
                generateLandlordFields(numberOfLandlords);
            }
        });

        document.getElementById('number_of_units').addEventListener('blur', function () {
            const numberOfUnits = parseInt(this.value);
            if (numberOfUnits && numberOfUnits >= 1 && numberOfUnits <= 500) {
                generateUnitFields(numberOfUnits);
            }
        });

        function generateUnitFields(numberOfUnits) {
            const unitsSection = document.getElementById('unitsSection');
            const unitsContainer = document.getElementById('unitsContainer');

            if (numberOfUnits > 0) {
                unitsSection.style.display = 'block';
                unitsContainer.innerHTML = '';

                // Create unit fields (show in groups for better organization)
                const unitGroups = Math.ceil(numberOfUnits / 4); // 4 units per row group

                for (let group = 0; group < unitGroups; group++) {
                    const groupDiv = document.createElement('div');
                    groupDiv.className = 'row mb-3';

                    const startIndex = group * 4;
                    const endIndex = Math.min(startIndex + 4, numberOfUnits);

                    for (let i = startIndex; i < endIndex; i++) {
                        const unitIndex = i + 1;
                        const colDiv = document.createElement('div');
                        colDiv.className = numberOfUnits <= 4 ? 'col-md-6 mb-3' : 'col-lg-3 col-md-6 mb-3';

                        colDiv.innerHTML = `
                        <div class="card h-100 border-success">
                            <div class="card-header py-2 bg-light-success">
                                <h6 class="mb-0 text-success">Unit ${unitIndex}</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="mb-2">
                                    <label for="unit_name_${unitIndex}" class="form-label small">Unit Name/Number *</label>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="unit_name_${unitIndex}" 
                                           name="unit_names[]" required
                                           placeholder="e.g., Apt 1A, Unit 101">
                                </div>
                                <div class="mb-0">
                                    <label for="unit_rent_${unitIndex}" class="form-label small">Monthly Rent *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control rent-input" 
                                               id="unit_rent_${unitIndex}" 
                                               name="unit_rents[]" 
                                               min="1" step="0.01" 
                                               placeholder="1200.00" required>
                                        <div class="input-group-text">SAR</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        groupDiv.appendChild(colDiv);
                    }

                    unitsContainer.appendChild(groupDiv);
                }

                // Add event listeners to rent inputs
                addRentCalculation();
                calculateTotalIncome();
            } else {
                unitsSection.style.display = 'none';
            }
        }

        function generateLandlordFields(numberOfLandlords) {
            const landlordsSection = document.getElementById('landlordsSection');
            const landlordsContainer = document.getElementById('landlordsContainer');
            const distributeBtn = document.getElementById('distributeBtn');

            if (numberOfLandlords > 0) {
                landlordsSection.style.display = 'block';
                landlordsContainer.innerHTML = '';

                // Calculate suggested percentage
                const suggestedPercentage = (100 / numberOfLandlords).toFixed(2);

                // Create landlord fields (show in groups if many)
                const landlordGroups = Math.ceil(numberOfLandlords / 6); // 6 landlords per row group

                for (let group = 0; group < landlordGroups; group++) {
                    const groupDiv = document.createElement('div');
                    groupDiv.className = 'row mb-3';

                    const startIndex = group * 6;
                    const endIndex = Math.min(startIndex + 6, numberOfLandlords);

                    for (let i = startIndex; i < endIndex; i++) {
                        const landlordIndex = i + 1;
                        const colDiv = document.createElement('div');
                        colDiv.className = numberOfLandlords <= 6 ? 'col-md-6 mb-3' : 'col-lg-4 col-md-6 mb-3';

                        colDiv.innerHTML = `
                        <div class="card h-100">
                            <div class="card-header py-2">
                                <h6 class="mb-0 text-primary">Landlord ${landlordIndex}</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="mb-2">
                                    <label for="landlord_name_${landlordIndex}" class="form-label small">Name *</label>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="landlord_name_${landlordIndex}" 
                                           name="landlord_names[]" required
                                           placeholder="Full name">
                                </div>
                                <div class="mb-0">
                                    <label for="ownership_percentage_${landlordIndex}" class="form-label small">Ownership % *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control ownership-input" 
                                               id="ownership_percentage_${landlordIndex}" 
                                               name="ownership_percentages[]" 
                                               min="0" max="100" step="0.01" 
                                               value="${suggestedPercentage}" required>
                                        <div class="input-group-text">%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        groupDiv.appendChild(colDiv);
                    }

                    landlordsContainer.appendChild(groupDiv);
                }

                // Show distribute button if more than 1 landlord
                if (numberOfLandlords > 1) {
                    distributeBtn.style.display = 'block';
                } else {
                    distributeBtn.style.display = 'none';
                }

                // Add event listeners to ownership inputs
                addOwnershipValidation();
                calculateTotalOwnership();
            } else {
                landlordsSection.style.display = 'none';
                distributeBtn.style.display = 'none';
            }
        }

        // Add rent calculation
        function addRentCalculation() {
            const rentInputs = document.querySelectorAll('.rent-input');
            rentInputs.forEach(input => {
                input.addEventListener('input', function () {
                    calculateTotalIncome();
                    calculateFinancialSummary();
                });

                input.addEventListener('blur', function () {
                    calculateTotalIncome();
                    calculateFinancialSummary();
                });
            });
        }

        // Add expense tracking
        document.getElementById('expenses').addEventListener('input', function() {
            calculateFinancialSummary();
        });

        document.getElementById('expenses').addEventListener('blur', function() {
            calculateFinancialSummary();
        });

        // Calculate total monthly income
        function calculateTotalIncome() {
            const rentInputs = document.querySelectorAll('.rent-input');
            let total = 0;

            rentInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            document.getElementById('totalUnits').textContent = rentInputs.length;
            document.getElementById('totalMonthlyIncome').textContent = total.toFixed(2);
            
            // Update the display in financial summary
            document.getElementById('displayTotalIncome').textContent = total.toFixed(2);
        }

        // Calculate financial summary
        function calculateFinancialSummary() {
            const totalIncome = parseFloat(document.getElementById('totalMonthlyIncome').textContent) || 0;
            const expenses = parseFloat(document.getElementById('expenses').value) || 0;
            const netIncome = totalIncome - expenses;

            document.getElementById('displayExpenses').textContent = expenses.toFixed(2);
            document.getElementById('displayNetIncome').textContent = netIncome.toFixed(2);

            // Color coding for net income
            const netIncomeElement = document.getElementById('displayNetIncome');
            const netIncomeContainer = netIncomeElement.closest('.h5');
            
            if (netIncome > 0) {
                netIncomeContainer.className = 'h5 text-success';
            } else if (netIncome < 0) {
                netIncomeContainer.className = 'h5 text-danger';
            } else {
                netIncomeContainer.className = 'h5 text-secondary';
            }
        }

        // Auto-generate unit names
        window.autoGenerateUnitNames = function () {
            const unitNameInputs = document.querySelectorAll('input[name="unit_names[]"]');
            const unitType = document.getElementById('unit_type').value;
            
            unitNameInputs.forEach((input, index) => {
                const unitNumber = index + 1;
                let unitName = '';
                
                switch (unitType) {
                    case 'apartment':
                        // Generate apartment names like "Apt 1A", "Apt 1B", etc.
                        const floor = Math.floor(index / 4) + 1;
                        const letter = String.fromCharCode(65 + (index % 4)); // A, B, C, D
                        unitName = `Apt ${floor}${letter}`;
                        break;
                    case 'condo':
                        unitName = `Condo ${unitNumber}`;
                        break;
                    case 'studio':
                        unitName = `Studio ${unitNumber}`;
                        break;
                    case 'house':
                        unitName = `House ${unitNumber}`;
                        break;
                    case 'room':
                        unitName = `Room ${unitNumber}`;
                        break;
                    default:
                        unitName = `Unit ${String(unitNumber).padStart(3, '0')}`;
                }
                
                input.value = unitName;
            });
        };

        // Set uniform rent for all units
        window.setUniformRent = function () {
            new bootstrap.Modal(document.getElementById('uniformRentModal')).show();
        };

        // Apply uniform rent
        window.applyUniformRent = function () {
            const uniformAmount = document.getElementById('uniform_rent_amount').value;
            
            if (!uniformAmount || parseFloat(uniformAmount) <= 0) {
                alert('Please enter a valid rent amount');
                return;
            }
            
            const rentInputs = document.querySelectorAll('.rent-input');
            rentInputs.forEach(input => {
                input.value = uniformAmount;
            });
            
            calculateTotalIncome();
            calculateFinancialSummary();
            bootstrap.Modal.getInstance(document.getElementById('uniformRentModal')).hide();
        };

        // Add ownership validation
        function addOwnershipValidation() {
            const ownershipInputs = document.querySelectorAll('.ownership-input');
            ownershipInputs.forEach(input => {
                input.addEventListener('input', function () {
                    // Validate individual input
                    const value = parseFloat(this.value);
                    if (value > 100) {
                        this.value = 100;
                        showFieldWarning(this, 'Individual ownership cannot exceed 100%');
                    } else if (value < 0) {
                        this.value = 0;
                        showFieldWarning(this, 'Ownership percentage cannot be negative');
                    }

                    // Calculate total after validation
                    calculateTotalOwnership();
                });

                input.addEventListener('blur', function () {
                    calculateTotalOwnership();
                });
            });
        }

        // Calculate total ownership percentage
        function calculateTotalOwnership() {
            const ownershipInputs = document.querySelectorAll('.ownership-input');
            let total = 0;

            ownershipInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            const totalElement = document.getElementById('totalOwnership');
            const warningElement = document.getElementById('ownershipWarning');
            const summaryElement = document.getElementById('ownershipSummary');
            const submitBtn = document.getElementById('submitBtn');

            if (totalElement) {
                totalElement.textContent = total.toFixed(2);

                // Check if total equals 100% (with small tolerance for floating point)
                if (Math.abs(total - 100) < 0.01) {
                    // Total is correct (100%)
                    warningElement.style.display = 'none';
                    summaryElement.className = 'alert alert-success';
                    updateSubmitButtonState();
                } else {
                    // Total is not 100%
                    warningElement.style.display = 'block';
                    summaryElement.className = 'alert alert-danger';
                    submitBtn.disabled = true;

                    if (total > 100) {
                        warningElement.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> 
                        Total exceeds 100%! You need to reduce by ${(total - 100).toFixed(2)}%
                    `;
                    } else if (total < 100) {
                        warningElement.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> 
                        Total is less than 100%! You need to add ${(100 - total).toFixed(2)}%
                    `;
                    }
                }
            }
        }

        // Update submit button state based on form validity
        function updateSubmitButtonState() {
            const form = document.getElementById('propertyRequestForm');
            const submitBtn = document.getElementById('submitBtn');
            const totalOwnership = parseFloat(document.getElementById('totalOwnership')?.textContent || '0');

            // Check if form is valid and ownership equals 100%
            const isFormValid = form.checkValidity();
            const isOwnershipValid = Math.abs(totalOwnership - 100) < 0.01;

            submitBtn.disabled = !(isFormValid && isOwnershipValid);
        }

        // Auto-distribute percentages equally
        window.distributeEqually = function () {
            const ownershipInputs = document.querySelectorAll('.ownership-input');
            const numberOfLandlords = ownershipInputs.length;

            if (numberOfLandlords > 0) {
                const basePercentage = Math.floor((100 / numberOfLandlords) * 100) / 100; // Round down to 2 decimal places
                const remainder = 100 - (basePercentage * numberOfLandlords);
                const extraCents = Math.round(remainder * 100);

                ownershipInputs.forEach((input, index) => {
                    let percentage = basePercentage;

                    // Distribute the extra cents to the first few landlords
                    if (index < extraCents) {
                        percentage += 0.01;
                    }

                    input.value = percentage.toFixed(2);
                });

                calculateTotalOwnership();
            }
        };

        // Form submission handler
        document.getElementById('propertyRequestForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate number of landlords
            const numberOfLandlords = parseInt(document.getElementById('number_of_landlords').value);
            if (!numberOfLandlords || numberOfLandlords < 1) {
                alert('Please enter a valid number of landlords (minimum 1).');
                return;
            }

            // Validate number of units
            const numberOfUnits = parseInt(document.getElementById('number_of_units').value);
            if (!numberOfUnits || numberOfUnits < 1) {
                alert('Please enter a valid number of units (minimum 1).');
                return;
            }

            // Validate units have names and rents
            const unitNames = document.querySelectorAll('input[name="unit_names[]"]');
            const unitRents = document.querySelectorAll('input[name="unit_rents[]"]');
            
            for (let i = 0; i < unitNames.length; i++) {
                if (!unitNames[i].value.trim()) {
                    alert(`Please enter a name for Unit ${i + 1}.`);
                    unitNames[i].focus();
                    return;
                }
                if (!unitRents[i].value || parseFloat(unitRents[i].value) <= 0) {
                    alert(`Please enter a valid rent amount for Unit ${i + 1}.`);
                    unitRents[i].focus();
                    return;
                }
            }

            // Check if landlords section is visible and validate ownership
            const landlordsSection = document.getElementById('landlordsSection');
            if (landlordsSection.style.display !== 'none') {
                const totalOwnership = parseFloat(document.getElementById('totalOwnership').textContent);
                if (Math.abs(totalOwnership - 100) >= 0.01) {
                    alert('Total ownership percentage must equal 100% before submitting.');
                    return;
                }
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message briefly
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                        <i class="fas fa-check-circle"></i>
                        <strong>Success!</strong> ${data.message} Redirecting to properties page...
                    `;

                        this.insertBefore(alertDiv, this.firstChild);
                        this.scrollIntoView({ behavior: 'smooth' });

                        // Redirect immediately to properties page
                        setTimeout(() => {
                            window.location.href = '<?= site_url('landlord/properties') ?>';
                        }, 1500);
                    } else {
                        // Show error message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error!</strong> ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                        this.insertBefore(alertDiv, this.firstChild);
                        this.scrollIntoView({ behavior: 'smooth' });

                        // Restore button
                        submitBtn.innerHTML = originalText;
                        updateSubmitButtonState();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> ${error.message}. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                    this.insertBefore(alertDiv, this.firstChild);
                    this.scrollIntoView({ behavior: 'smooth' });

                    // Restore button
                    submitBtn.innerHTML = originalText;
                    updateSubmitButtonState();
                });
        });

        // Helper function to show field warnings
        function showFieldWarning(field, message) {
            // Remove existing warning
            let warning = field.closest('.input-group')?.parentNode?.querySelector('.text-danger') ||
                field.parentNode.querySelector('.text-danger');
            if (warning) {
                warning.remove();
            }

            // Add new warning
            warning = document.createElement('small');
            warning.className = 'text-danger d-block mt-1';
            warning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;

            if (field.closest('.input-group')) {
                field.closest('.input-group').parentNode.appendChild(warning);
            } else {
                field.parentNode.appendChild(warning);
            }

            // Remove warning after 3 seconds
            setTimeout(() => {
                if (warning && warning.parentNode) {
                    warning.remove();
                }
            }, 3000);
        }

        // Form validation feedback
        document.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
            field.addEventListener('blur', function () {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
                updateSubmitButtonState();
            });

            field.addEventListener('input', function () {
                if (this.value.trim() !== '') {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
                updateSubmitButtonState();
            });
        });

    }); // End of DOMContentLoaded
</script>

<style>
/* Enhanced styling for the units section */
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

/* Modal styling */
.modal-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.modal-title {
    color: #5a5c69;
    font-weight: 600;
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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