<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user"></i> My Profile
        </h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="enableEdit()">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
            <button type="button" class="btn btn-warning" onclick="showChangePasswordModal()">
                <i class="fas fa-key"></i> Change Password
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php 
    $success = session()->getFlashdata('success');
    $error = session()->getFlashdata('error');
    $validation = session()->getFlashdata('validation');
    ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" id="success-alert">
            <i class="fas fa-check-circle"></i>
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" id="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($validation): ?>
        <div class="alert alert-danger alert-dismissible fade show" id="validation-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-edit"></i> Personal Information
                    </h6>
                    <div id="editButtons" style="display: none;">
                        <button type="button" class="btn btn-sm btn-success" onclick="saveProfile()">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('tenant/profile/update', ['id' => 'profileForm']) ?>
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           autocomplete="given-name"
                                           value="<?= old('first_name', esc($user['first_name'] ?? '')) ?>" 
                                           readonly
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           autocomplete="family-name"
                                           value="<?= old('last_name', esc($user['last_name'] ?? '')) ?>" 
                                           readonly
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           autocomplete="email"
                                           value="<?= old('email', esc($user['email'] ?? '')) ?>" 
                                           readonly
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           autocomplete="tel"
                                           value="<?= old('phone', esc($user['phone'] ?? '')) ?>" 
                                           readonly
                                           placeholder="(123) 456-7890">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      autocomplete="street-address"
                                      readonly
                                      placeholder="Your current address"><?= old('address', esc($user['address'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-group" id="formButtons" style="display: none;">
                            <button type="submit" class="btn btn-primary btn-lg" id="updateBtn">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="cancelEdit()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Account Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; background: #f8f9fc; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                        <h5><?= esc($user['first_name'] ?? '') ?> <?= esc($user['last_name'] ?? '') ?></h5>
                        <p class="text-muted"><?= esc($user['email'] ?? '') ?></p>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Username:</strong></p>
                            <p class="text-muted"><?= esc($user['username'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Role:</strong></p>
                            <p class="text-muted">
                                <span class="badge badge-info"><?= ucfirst($user['role'] ?? 'tenant') ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Member Since:</strong></p>
                            <p class="text-muted">
                                <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Account Status:</strong></p>
                            <p class="text-muted">
                                <span class="badge badge-<?= ($user['is_active'] ?? true) ? 'success' : 'warning' ?>">
                                    <?= ($user['is_active'] ?? true) ? 'Active' : 'Inactive' ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <p class="mb-1"><strong>Last Updated:</strong></p>
                            <p class="text-muted">
                                <?= isset($user['updated_at']) ? date('M d, Y g:i A', strtotime($user['updated_at'])) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                        <a href="<?= site_url('tenant/payments') ?>" class="btn btn-outline-success">
                            <i class="fas fa-credit-card"></i> View Payments
                        </a>
                        <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-outline-warning">
                            <i class="fas fa-tools"></i> Maintenance Requests
                        </a>
                        <a href="<?= site_url('tenant/lease') ?>" class="btn btn-outline-info">
                            <i class="fas fa-file-contract"></i> View Lease
                        </a>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Security Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Use a strong, unique password
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Keep your contact information updated
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Never share your login credentials
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success"></i>
                            Log out when using shared computers
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key"></i> Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('tenant/profile/change-password', ['id' => 'changePasswordForm']) ?>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="modal_current_password" class="form-label">Current Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="modal_current_password" 
                               name="current_password" 
                               autocomplete="current-password"
                               required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="modal_new_password" class="form-label">New Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="modal_new_password" 
                               name="new_password" 
                               autocomplete="new-password"
                               required>
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="modal_confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="modal_confirm_password" 
                               name="confirm_password" 
                               autocomplete="new-password"
                               required>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" id="strengthBar" style="width: 0%"></div>
                        </div>
                        <small id="strengthText" class="text-muted"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="changePasswordBtn">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
let editMode = false;

// Enable edit mode
function enableEdit() {
    console.log('enableEdit function called'); // Debug
    editMode = true;
    
    // Enable form fields
    const fields = ['first_name', 'last_name', 'email', 'phone', 'address'];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            console.log('Enabling field:', field); // Debug
            element.removeAttribute('readonly');
            element.classList.add('border-primary');
        } else {
            console.log('Field not found:', field); // Debug
        }
    });
    
    // Show edit buttons and form buttons
    const editButtons = document.getElementById('editButtons');
    const formButtons = document.getElementById('formButtons');
    
    if (editButtons) {
        editButtons.style.display = 'block';
        console.log('Edit buttons shown'); // Debug
    } else {
        console.log('Edit buttons element not found'); // Debug
    }
    
    if (formButtons) {
        formButtons.style.display = 'block';
        console.log('Form buttons shown'); // Debug
    } else {
        console.log('Form buttons element not found'); // Debug
    }
    
    // Hide main edit button
    const editBtn = document.querySelector('[onclick="enableEdit()"]');
    if (editBtn) {
        editBtn.style.display = 'none';
        console.log('Main edit button hidden'); // Debug
    } else {
        console.log('Main edit button not found'); // Debug
    }
}

// Cancel edit mode
function cancelEdit() {
    location.reload(); // Simple way to revert changes
}

// Save profile (inline editing)
function saveProfile() {
    const formData = new FormData(document.getElementById('profileForm'));
    
    // Show loading state
    const saveBtn = document.querySelector('[onclick="saveProfile()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('<?= site_url('tenant/profile/update') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                Profile updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
            
            // Exit edit mode
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Show change password modal
function showChangePasswordModal() {
    console.log('showChangePasswordModal called'); // Debug
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

// Password strength checker for modal
document.getElementById('modal_new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length > 0) {
        strengthDiv.style.display = 'block';
        
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength += 20;
        else feedback.push('at least 8 characters');
        
        if (/[a-z]/.test(password)) strength += 20;
        else feedback.push('lowercase letter');
        
        if (/[A-Z]/.test(password)) strength += 20;
        else feedback.push('uppercase letter');
        
        if (/\d/.test(password)) strength += 20;
        else feedback.push('number');
        
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        else feedback.push('special character');
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 40) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Weak - Add: ' + feedback.join(', ');
        } else if (strength < 80) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Medium - Add: ' + feedback.join(', ');
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong password';
        }
    } else {
        strengthDiv.style.display = 'none';
    }
});

// Change password modal form submission
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('modal_new_password').value;
    const confirmPassword = document.getElementById('modal_confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    const formData = new FormData(this);
    const changeBtn = document.getElementById('changePasswordBtn');
    const originalText = changeBtn.innerHTML;
    
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
    changeBtn.disabled = true;
    
    fetch('<?= site_url('tenant/profile/change-password') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                Password changed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
            
            // Hide modal and reset form
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
            this.reset();
            document.getElementById('passwordStrength').style.display = 'none';
            
            // Auto-hide alert after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        changeBtn.innerHTML = originalText;
        changeBtn.disabled = false;
    });
});

// Modal password confirmation validation
document.getElementById('modal_confirm_password').addEventListener('input', function() {
    const password = document.getElementById('modal_new_password').value;
    const confirmPassword = this.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Auto-hide alerts after 5 seconds (only once)
let alertsHidden = false;
setTimeout(function() {
    if (!alertsHidden) {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
        alertsHidden = true;
    }
}, 5000);

// Phone number formatting (only when in edit mode)
document.getElementById('phone').addEventListener('input', function(e) {
    if (!editMode) return; // Only format when editing
    
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
    }
    e.target.value = value;
});
</script>
<?= $this->endSection() ?>