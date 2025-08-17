<?php
// Make sure $currentUser exists (fallback to session)
$currentUser = $currentUser ?? (session('user') ?? []);

$firstName = $currentUser['first_name'] ?? $currentUser['firstname'] ?? '';
$lastName  = $currentUser['last_name']  ?? $currentUser['lastname']  ?? '';
$email     = $currentUser['email']      ?? (session('email') ?? '');
$username  = $currentUser['username']   ?? '';

$fullName  = trim($firstName . ' ' . $lastName);
if ($fullName === '') {
    $fullName = $username ?: ($email ? strtok($email, '@') : '');
}

// Validation + “old input” flags without calling old()
$validation = isset($validation) ? $validation : \Config\Services::validation();
$hasErrors  = $validation && !empty($validation->getErrors());

// Check if there is any old input saved in session/flashdata
$oldInput = session()->getFlashdata('_ci_old_input');
if ($oldInput === null) {
    $oldInput = session('_ci_old_input'); // fallback
}
$hasOld = !empty($oldInput);
?>

<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Add New Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Add New Property
        </h1>
        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Conditions Agreement Card -->
            <div class="card shadow mb-4 border-warning">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Shareholders Agreement Conditions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Important:</strong> You must read and agree to these conditions before adding your
                        property.
                    </div>

                    <div class="conditions-list">
                        <div class="condition-item mb-3 p-3 border rounded">
                            <div class="form-check">
                                <input class="form-check-input condition-checkbox" type="checkbox" id="condition1"
                                    required>
                                <label class="form-check-label" for="condition1">
                                    <strong>Non-Operational Involvement:</strong> Shareholders have no involvement in
                                    the property's operation at all.
                                </label>
                            </div>
                        </div>

                        <div class="condition-item mb-3 p-3 border rounded">
                            <div class="form-check">
                                <input class="form-check-input condition-checkbox" type="checkbox" id="condition2"
                                    required>
                                <label class="form-check-label" for="condition2">
                                    <strong>Income Distribution:</strong> Any financial income from the property will be
                                    distributed to shareholders after deducting expenses.
                                </label>
                            </div>
                        </div>

                        <div class="condition-item mb-3 p-3 border rounded">
                            <div class="form-check">
                                <input class="form-check-input condition-checkbox" type="checkbox" id="condition3"
                                    required>
                                <label class="form-check-label" for="condition3">
                                    <strong>Violation Refund:</strong> In case of any violation, the shareholder's
                                    contribution amount will be refunded.
                                </label>
                            </div>
                        </div>

                        <div class="condition-item mb-3 p-3 border rounded">
                            <div class="form-check">
                                <input class="form-check-input condition-checkbox" type="checkbox" id="condition4"
                                    required>
                                <label class="form-check-label" for="condition4">
                                    <strong>Share Transfer Restriction:</strong> Shareholders are not allowed to sell
                                    their shares to anyone outside the current shareholders.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success mt-3" id="allConditionsAgreed" style="display: none;">
                        <i class="fas fa-check-circle"></i> All conditions have been accepted. You may now proceed to
                        add your property.
                    </div>
                </div>
            </div>
            
            <!-- Property Information Form -->
            <div class="card shadow mb-4" id="propertyFormCard"
                style="<?= ($hasErrors || $hasOld) ? '' : 'display:none;' ?>">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($hasErrors): ?>
                        <div class="alert alert-danger">
                            <strong>Please correct the following:</strong>
                            <ul class="mb-0">
                                <?php foreach ($validation->getErrors() as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form id="propertyRequestForm" method="post" action="<?= site_url('landlord/add-property') ?>">
                        <?= csrf_field() ?>

                        <!-- Basic Property Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name" required
                                    placeholder="e.g., Sunset Villa, Downtown Complex">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="property_value" class="form-label">Property Value (SAR) *</label>
                                <input type="number" class="form-control" id="property_value" name="property_value"
                                    required min="1" step="0.01" placeholder="e.g., 500000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="3"
                                required
                                placeholder="Enter the complete property address including street, city, and region"></textarea>
                        </div>

                        <!-- Share Information -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="total_shares" class="form-label">Total Number of Shares *</label>
                                <input type="number" class="form-control" id="total_shares" name="total_shares" required
                                    min="1" max="10000" value="100" onchange="calculateShareValue()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="share_value" class="form-label">Share Value (SAR) *</label>
                                <input type="number" class="form-control" id="share_value" name="share_value"
                                    step="0.01" readonly>
                                <small class="text-muted">Calculated automatically: Property Value ÷ Total
                                    Shares</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contribution_duration" class="form-label">Contribution Duration (Months)
                                    *</label>
                                <input type="number" class="form-control" id="contribution_duration"
                                    name="contribution_duration" required min="1" max="360"
                                    placeholder="e.g., 12, 24, 36">
                            </div>
                        </div>

                        <!-- Management Information -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="management_company" class="form-label">Management Company *</label>
                                <input type="text" class="form-control" id="management_company"
                                    name="management_company" required placeholder="e.g., ABC Property Management">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="management_percentage" class="form-label">Management Percentage (%)
                                    *</label>
                                <input type="number" class="form-control" id="management_percentage"
                                    name="management_percentage" required min="0" max="50" step="0.1"
                                    placeholder="e.g., 5, 10, 15">
                            </div>
                        </div>

                        <!-- Property Units Section -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-info">
                                    <i class="fas fa-door-open"></i> Property Units
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Add all units/apartments within this property. Each unit can
                                    be individually managed and rented.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="total_units" class="form-label">Total Number of Units *</label>
                                        <input type="number" class="form-control" id="total_units" name="total_units"
                                            required min="1" max="500" value="1" onchange="updateUnitsFields()">
                                        <small class="text-muted">Enter the total number of units in this
                                            property</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Unit Naming Pattern</label>
                                        <select class="form-control" id="unit_naming_pattern"
                                            onchange="applyNamingPattern()">
                                            <option value="manual">Manual Entry</option>
                                            <option value="numbers">Numbers (1, 2, 3...)</option>
                                            <option value="letters">Letters (A, B, C...)</option>
                                            <option value="floors">Floor + Number (1A, 1B, 2A...)</option>
                                        </select>
                                        <small class="text-muted">Choose a pattern or enter manually</small>
                                    </div>
                                </div>

                                <div id="unitsContainer">
                                    <!-- Units will be dynamically generated here -->
                                    <div class="unit-row row mb-2">
                                        <div class="col-md-10">
                                            <input type="text" class="form-control unit-name-input" name="unit_names[]"
                                                placeholder="Unit Name (e.g., Unit 1, Apt A, 1st Floor)" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger btn-block"
                                                onclick="removeUnitField(this)" style="display: none;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb"></i>
                                        Units help organize rental management and track individual property performance.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Owner Information Section -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-success">
                                    <i class="fas fa-users"></i> Owners Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> As the property creator, you are the first owner. You can add
                                    additional owners one by one after creating the property.
                                </div>

                                <!-- First Owner (Current User) -->
                                <div class="owner-section border rounded p-3 mb-3">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user"></i> Owner #1 (You)
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="owner1_name" class="form-label">Owner Name *</label>
                                            <input type="text" id="owner1_name" name="owners[0][name]"
                                                class="form-control" value="<?= esc($fullName) ?>" readonly>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="owner1_email" class="form-label">Email *</label>
                                            <input type="email" id="owner1_email" name="owners[0][email]"
                                                class="form-control" value="<?= esc($email) ?>" readonly>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="owner1_shares" class="form-label">Number of Shares *</label>
                                            <input type="number" class="form-control" id="owner1_shares"
                                                name="owners[0][shares]" required min="1" value="1"
                                                onchange="calculateOwnershipPercentage(0)">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Ownership Percentage</label>
                                            <input type="text" class="form-control ownership-percentage"
                                                id="owner1_percentage" name="owners[0][percentage]" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="owner-section border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-users"></i> Additional Owners
                                        </h6>
                                        <button type="button" id="btnAddOwner" class="btn btn-sm btn-outline-primary"
                                            disabled onclick="addOwnerRow()">
                                            <i class="fas fa-plus"></i> Add Owner
                                        </button>
                                    </div>

                                    <div id="ownersContainer"></div>

                                    <small class="text-muted d-block mt-2">
                                        Total requested shares cannot exceed Total Number of Shares.
                                    </small>
                                    <div id="sharesWarning" class="text-danger small mt-1" style="display:none;">
                                        Owners' total shares exceed Total Number of Shares.
                                    </div>
                                </div>

                                <script>
                                    let ownerIndex = 1; // 0 is the current user

                                    function owner1Valid() {
                                        const n = document.getElementById('owner1_name')?.value.trim() || '';
                                        const e = document.getElementById('owner1_email')?.value.trim() || '';
                                        const s = parseInt(document.getElementById('owner1_shares')?.value || '0', 10);
                                        return n !== '' && e !== '' && s >= 1;
                                    }

                                    function getTotalRequestedShares() {
                                        const inputs = document.querySelectorAll('[name^="owners"][name$="[shares]"]');
                                        let sum = 0;
                                        inputs.forEach(i => { sum += parseInt(i.value || '0', 10); });
                                        return sum;
                                    }

                                    function validateSharesAgainstTotal() {
                                        const totalShares = parseInt(document.getElementById('total_shares').value || '1', 10);
                                        const sumShares = getTotalRequestedShares();
                                        const warn = document.getElementById('sharesWarning');
                                        if (sumShares > totalShares) {
                                            warn.style.display = 'block';
                                            return false;
                                        } else {
                                            warn.style.display = 'none';
                                            return true;
                                        }
                                    }

                                    function updateAddOwnerButtonState() {
                                        const btnAdd = document.getElementById('btnAddOwner');
                                        btnAdd.disabled = !owner1Valid();  // only enable when owner #1 is valid
                                    }

                                    function addOwnerRow() {
                                        const container = document.getElementById('ownersContainer');
                                        const row = document.createElement('div');
                                        row.className = 'row g-2 align-items-end mb-2';
                                        row.innerHTML = `
    <div class="col-md-4">
      <label class="form-label">Owner Name</label>
      <input type="text" class="form-control" name="owners[${ownerIndex}][name]" placeholder="Full name">
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="owners[${ownerIndex}][email]" placeholder="name@example.com">
    </div>
    <div class="col-md-3">
      <label class="form-label">Shares</label>
      <input type="number" class="form-control" name="owners[${ownerIndex}][shares]" min="1" value="1" oninput="updateSubmitButtonState(); validateSharesAgainstTotal();">
    </div>
    <div class="col-md-1 text-end">
      <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove(); updateSubmitButtonState(); validateSharesAgainstTotal();">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  `;
                                        container.appendChild(row);
                                        ownerIndex++;
                                        validateSharesAgainstTotal();
                                        updateSubmitButtonState();
                                    }

                                    function updateSubmitButtonState() {
                                        const form = document.getElementById('propertyRequestForm');
                                        const submitBtn = document.getElementById('submitBtn');

                                        // existing checks
                                        const conditionsAgreed = Array.from(document.querySelectorAll('.condition-checkbox')).every(cb => cb.checked);
                                        if (!conditionsAgreed) { submitBtn.disabled = true; return; }

                                        const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
                                        const allFilled = Array.from(requiredFields).every(field => (field.value || '').trim() !== '');

                                        const ownerShares = parseInt(document.getElementById('owner1_shares').value || '0', 10);
                                        const validShares = ownerShares >= 1;

                                        const sharesOk = validateSharesAgainstTotal();

                                        // enable/disable “Add owner” button progressively
                                        updateAddOwnerButtonState();

                                        submitBtn.disabled = !(allFilled && validShares && sharesOk);
                                    }

                                    // wire up events
                                    document.addEventListener('DOMContentLoaded', function () {
                                        // enable progressive behavior
                                        ['owner1_shares', 'owner1_name', 'owner1_email', 'total_shares', 'property_value']
                                            .forEach(id => { const el = document.getElementById(id); if (el) el.addEventListener('input', () => { updateAddOwnerButtonState(); updateSubmitButtonState(); }); });

                                        // existing listeners you already have
                                        document.querySelectorAll('.condition-checkbox').forEach(cb => cb.addEventListener('change', () => { checkConditions(); updateSubmitButtonState(); }));

                                        const totalSharesEl = document.getElementById('total_shares');
                                        if (totalSharesEl) totalSharesEl.addEventListener('input', () => { validateSharesAgainstTotal(); });

                                        // initial state
                                        updateAddOwnerButtonState();
                                        validateSharesAgainstTotal();
                                        updateSubmitButtonState();
                                    });
                                </script>

                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-plus-circle"></i> Add Property
                                </button>
                                <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary btn-lg ml-2">
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
    // Check if all conditions are agreed
      function checkConditions() {
    const boxes = document.querySelectorAll('.condition-checkbox');
    const allChecked = Array.from(boxes).every(b => b.checked);

    const ok   = document.getElementById('allConditionsAgreed');
    const card = document.getElementById('propertyFormCard');
    const btn  = document.getElementById('submitBtn');

    if (!ok || !card || !btn) return;

    if (allChecked) {
      ok.style.display = 'block';
      card.style.display = 'block';
      if (typeof updateSubmitButtonState === 'function') updateSubmitButtonState();
    } else {
      ok.style.display = 'none';
      card.style.display = 'none';
      btn.disabled = true;
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    // wire the 4 checkboxes
    document.querySelectorAll('.condition-checkbox')
      .forEach(cb => cb.addEventListener('change', checkConditions));

    // run once in case user had a validation error and came back with some checked
    checkConditions();
  });

    // Calculate share value when total shares or property value changes
    function calculateShareValue() {
        const pv = parseFloat(document.getElementById('property_value').value) || 0;
        const ts = parseInt(document.getElementById('total_shares').value) || 1;
        document.getElementById('share_value').value = (pv / ts).toFixed(2);
        calculateOwnershipPercentage(0);
    }

    // Update units fields based on total units count
    function updateUnitsFields() {
        const totalUnits = parseInt(document.getElementById('total_units').value) || 1;
        const container = document.getElementById('unitsContainer');
        container.innerHTML = '';

        for (let i = 1; i <= totalUnits; i++) {
            const row = document.createElement('div');
            row.className = 'unit-row row mb-2';
            row.innerHTML = `
      <div class="col-md-10">
        <input type="text" class="form-control unit-name-input" name="unit_names[]"
          placeholder="Unit Name (e.g., Unit ${i}, Apt ${String.fromCharCode(64 + i)}, ${Math.ceil(i / 2)}${i % 2 === 1 ? 'A' : 'B'})" required>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-outline-danger btn-block" onclick="removeUnitField(this)" ${totalUnits === 1 ? 'style="display:none;"' : ''}>
          <i class="fas fa-minus"></i>
        </button>
      </div>
    `;
            container.appendChild(row);
        }

        applyNamingPattern();
        updateSubmitButtonState();
    }

    // Apply automatic naming patterns
    function applyNamingPattern() {
        const pattern = document.getElementById('unit_naming_pattern').value;
        const unitInputs = document.querySelectorAll('.unit-name-input');

        if (pattern === 'manual') { unitInputs.forEach(i => i.value = ''); return; }

        unitInputs.forEach((input, idx) => {
            const n = idx + 1;
            if (pattern === 'numbers') input.value = `Unit ${n}`;
            if (pattern === 'letters') input.value = `Unit ${String.fromCharCode(64 + n)}`;
            if (pattern === 'floors') input.value = `${Math.ceil(n / 2)}${n % 2 === 1 ? 'A' : 'B'}`;
        });
    }

    function removeUnitField(btn) {
        const container = document.getElementById('unitsContainer');
        const row = btn.closest('.unit-row');
        if (container.children.length > 1) {
            row.remove();
            document.getElementById('total_units').value = container.children.length;
            updateSubmitButtonState();
        } else {
            alert('At least one unit is required.');
        }
    }

    function addUnitField() {
        const container = document.getElementById('unitsContainer');
        const count = container.children.length + 1;
        const row = document.createElement('div');
        row.className = 'unit-row row mb-2';
        row.innerHTML = `
    <div class="col-md-10">
      <input type="text" class="form-control unit-name-input" name="unit_names[]" placeholder="Unit Name (e.g., Unit ${count})" required>
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-outline-danger btn-block" onclick="removeUnitField(this)">
        <i class="fas fa-minus"></i>
      </button>
    </div>`;
        container.appendChild(row);
        document.getElementById('total_units').value = count;
        updateSubmitButtonState();
    }

    function calculateOwnershipPercentage(ownerIndex) {
        const totalShares = parseInt(document.getElementById('total_shares').value) || 1;
        const ownerShares = parseInt(document.getElementById(`owner${ownerIndex + 1}_shares`).value) || 0;
        const pct = (ownerShares / totalShares) * 100;
        document.getElementById(`owner${ownerIndex + 1}_percentage`).value = pct.toFixed(2) + '%';
        updateSubmitButtonState();
    }

    function updateSubmitButtonState() {
        const form = document.getElementById('propertyRequestForm');
        const submitBtn = document.getElementById('submitBtn');
        const conditionsAgreed = Array.from(document.querySelectorAll('.condition-checkbox')).every(cb => cb.checked);
        if (!conditionsAgreed) { submitBtn.disabled = true; return; }

        const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
        const allFilled = Array.from(requiredFields).every(f => (f.value || '').toString().trim() !== '');
        const ownerShares = parseInt(document.getElementById('owner1_shares').value) || 0;
        submitBtn.disabled = !(allFilled && ownerShares >= 1);
    }

    /* --- run once when DOM is ready --- */
    document.addEventListener('DOMContentLoaded', function () {
        // Wire value changes
        document.getElementById('property_value').addEventListener('input', calculateShareValue);
        document.getElementById('total_shares').addEventListener('input', calculateShareValue);
        document.getElementById('unit_naming_pattern').addEventListener('change', applyNamingPattern);
        document.getElementById('total_units').addEventListener('change', updateUnitsFields);

        // Form input watchers
        const form = document.getElementById('propertyRequestForm');
        if (form) {
            form.addEventListener('input', updateSubmitButtonState);
            form.addEventListener('change', updateSubmitButtonState);

            // Submit UX
            form.addEventListener('submit', function (e) {
                // allow normal submission; no preventDefault unless you really need async
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Property...';
                submitBtn.disabled = true;
            });
        }

        // Initial state
        calculateShareValue();
        updateUnitsFields();
        updateSubmitButtonState();
    });
</script>

<style>
    .condition-item {
        transition: all 0.3s ease;
    }

    .condition-item:hover {
        background-color: #f8f9fa;
    }

    .condition-checkbox:checked+label {
        color: #28a745;
        font-weight: 500;
    }

    .owner-section {
        background-color: #f8f9fa;
        border: 2px solid #e9ecef !important;
    }

    .ownership-percentage {
        background-color: #e9ecef;
        font-weight: bold;
        color: #495057;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .alert {
        border: none;
        border-radius: 0.35rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hasErrors = <?= ($hasErrors || $hasOld) ? 'true' : 'false' ?>;
        if (hasErrors) {
            const agreementAlert = document.getElementById('allConditionsAgreed');
            const propertyForm = document.getElementById('propertyFormCard');
            const submitBtn = document.getElementById('submitBtn');

            if (agreementAlert) agreementAlert.style.display = 'block';
            if (propertyForm) propertyForm.style.display = 'block';
            if (submitBtn) submitBtn.disabled = false;

            // run initial calculations so %/share value fill in after a failed submit
            if (typeof calculateShareValue === 'function') calculateShareValue();
            if (typeof updateUnitsFields === 'function') updateUnitsFields();
            if (typeof updateSubmitButtonState === 'function') updateSubmitButtonState();
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const hasOld    = <?= $hasOld   ? 'true' : 'false' ?>;
  const hasErrors = <?= $hasErrors? 'true' : 'false' ?>;

  if (hasOld || hasErrors) {
    document.querySelectorAll('.condition-checkbox').forEach(cb => cb.checked = true);
    const ok   = document.getElementById('allConditionsAgreed');
    const card = document.getElementById('propertyFormCard');
    if (ok)   ok.style.display   = 'block';
    if (card) card.style.display = 'block';
    if (typeof updateSubmitButtonState === 'function') updateSubmitButtonState();
  }
});
</script>

<?= $this->endSection() ?>