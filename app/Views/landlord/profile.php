<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle"></i> My Profile
        </h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" id="mainEditBtn" onclick="enableEdit()">
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
            <strong>Success!</strong> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" id="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Error!</strong> <?= $error ?>
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

    <!-- Profile Content -->
    <div class="row">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-edit"></i> Personal Information
                    </h6>
                    <div id="editButtons" style="display: none;">
                        <button type="button" class="btn btn-sm btn-success" onclick="saveProfile()">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('landlord/profile/update', ['id' => 'profileForm']) ?>
                        <?= csrf_field() ?>

                        <!-- Basic Information -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-user"></i> Basic Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       autocomplete="given-name"
                                       value="<?= old('first_name', esc($user['first_name'] ?? '')) ?>" 
                                       readonly
                                       required>
                                <div class="invalid-feedback">
                                    Please provide a valid first name.
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       autocomplete="family-name"
                                       value="<?= old('last_name', esc($user['last_name'] ?? '')) ?>" 
                                       readonly
                                       required>
                                <div class="invalid-feedback">
                                    Please provide a valid last name.
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <h6 class="text-success border-bottom pb-2 mb-3 mt-4">
                            <i class="fas fa-phone"></i> Contact Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           autocomplete="email"
                                           value="<?= old('email', esc($user['email'] ?? '')) ?>" 
                                           readonly
                                           required>
                                </div>
                                <small class="text-muted">Email cannot be changed for security reasons</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           autocomplete="tel"
                                           value="<?= old('phone', esc($user['phone'] ?? '')) ?>" 
                                           readonly
                                           placeholder="(123) 456-7890"
                                           maxlength="14">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="3" 
                                          autocomplete="street-address"
                                          readonly
                                          placeholder="Your current address"><?= old('address', esc($user['address'] ?? '')) ?></textarea>
                            </div>
                        </div>

                        <!-- Form Buttons -->
                        <div class="form-group mt-4" id="formButtons" style="display: none;">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary btn-lg me-md-2" onclick="cancelEdit()">
                                    <i class="fas fa-times"></i> Cancel Changes
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg" id="updateBtn">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 text-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-circle"></i> Account Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);">
                            <span class="text-white font-weight-bold" style="font-size: 2rem;">
                                <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="font-weight-bold"><?= esc($user['first_name'] ?? 'Unknown') ?> <?= esc($user['last_name'] ?? 'User') ?></h5>
                        <p class="text-muted mb-1"><?= esc($user['email'] ?? 'No email') ?></p>
                        <span class="badge badge-success">Verified Landlord</span>
                    </div>

                    <hr>

                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="info-item">
                                <strong class="text-primary">Username</strong>
                                <p class="text-muted mb-0"><?= esc($user['username'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <strong class="text-success">Status</strong>
                                <p class="mb-0">
                                    <span class="badge badge-<?= ($user['status'] ?? 'active') === 'active' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($user['status'] ?? 'Active') ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="info-item">
                                <strong class="text-info">Member Since</strong>
                                <p class="text-muted mb-0 small">
                                    <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <strong class="text-warning">Last Updated</strong>
                                <p class="text-muted mb-0 small">
                                    <?= isset($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : 'N/A' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Portfolio Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-item">
                                <h3 class="text-primary mb-1"><?= $stats['total_properties'] ?? 0 ?></h3>
                                <small class="text-muted">Properties</small>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-item">
                                <h3 class="text-success mb-1"><?= $stats['total_tenants'] ?? 0 ?></h3>
                                <small class="text-muted">Tenants</small>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-item">
                                <h3 class="text-warning mb-1"><?= $stats['total_income'] ?? '$0' ?></h3>
                                <small class="text-muted">Total Income</small>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-warning" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-item">
                                <h3 class="text-info mb-1"><?= round($stats['avg_occupancy'] ?? 0, 1) ?>%</h3>
                                <small class="text-muted">Occupancy</small>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-info" style="width: <?= round($stats['avg_occupancy'] ?? 0, 1) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Navigation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('landlord/dashboard') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-building me-2"></i> Properties
                        </a>
                        <a href="<?= site_url('landlord/tenants') ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-users me-2"></i> Tenants
                        </a>
                        <a href="<?= site_url('landlord/payments') ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-credit-card me-2"></i> Payments
                        </a>
                        <a href="<?= site_url('landlord/maintenance') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tools me-2"></i> Maintenance
                        </a>
                        <a href="<?= site_url('landlord/reports') ?>" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Security & Privacy -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Security & Privacy
                    </h6>
                </div>
                <div class="card-body">
                    <div class="security-checklist">
                        <div class="security-item mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Two-factor authentication enabled</small>
                        </div>
                        <div class="security-item mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Secure password requirements</small>
                        </div>
                        <div class="security-item mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Encrypted data transmission</small>
                        </div>
                        <div class="security-item mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Regular security updates</small>
                        </div>
                        <div class="security-item mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Profile information protection</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-sm" onclick="showChangePasswordModal()">
                            <i class="fas fa-key me-2"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i> Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('landlord/profile/change-password', ['id' => 'changePasswordForm']) ?>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Security Tip:</strong> Use a strong password with at least 8 characters, including uppercase, lowercase, numbers, and symbols.
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_current_password" class="form-label">Current Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="modal_current_password" 
                                   name="current_password" 
                                   autocomplete="current-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('modal_current_password')">
                                <i class="fas fa-eye" id="modal_current_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_new_password" class="form-label">New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="modal_new_password" 
                                   name="new_password" 
                                   autocomplete="new-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('modal_new_password')">
                                <i class="fas fa-eye" id="modal_new_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_confirm_password" class="form-label">Confirm New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="modal_confirm_password" 
                                   name="confirm_password" 
                                   autocomplete="new-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('modal_confirm_password')">
                                <i class="fas fa-eye" id="modal_confirm_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <label class="form-label small">Password Strength</label>
                        <div class="progress mb-2" style="height: 8px;">
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
    editMode = true;
    
    // Enable form fields (except email for security)
    const fields = ['first_name', 'last_name', 'phone', 'address'];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.removeAttribute('readonly');
            element.classList.add('border-primary');
            element.classList.add('shadow-sm');
            
            // Add focus effect
            element.addEventListener('focus', function() {
                this.classList.add('border-success');
            });
            element.addEventListener('blur', function() {
                this.classList.remove('border-success');
            });
        }
    });
    
    // Show edit buttons and form buttons
    document.getElementById('editButtons').style.display = 'block';
    document.getElementById('formButtons').style.display = 'block';
    
    // Hide main edit button
    document.getElementById('mainEditBtn').style.display = 'none';
    
    // Focus on first field
    document.getElementById('first_name').focus();
    
    // Show edit notification
    showNotification('Edit mode enabled! Make your changes and click Save.', 'info');
}

// Cancel edit mode
function cancelEdit() {
    if (editMode) {
        if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
            location.reload();
        }
    } else {
        location.reload();
    }
}

// Save profile (inline editing)
function saveProfile() {
    // Validate required fields
    const requiredFields = ['first_name', 'last_name'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields.', 'error');
        return;
    }
    
    const formData = new FormData(document.getElementById('profileForm'));
    
    // Show loading state
    const saveBtn = document.querySelector('[onclick="saveProfile()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('<?= site_url('landlord/profile/update') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            // Delay reload to show success message
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Show change password modal
function showChangePasswordModal() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Enhanced notification system
function showNotification(message, type) {
    // Remove existing notifications
    const existingAlerts = document.querySelectorAll('.notification-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show notification-alert position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="fas ${icon}"></i>
        <strong>${type === 'error' ? 'Error!' : type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Info!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Password strength checker
document.getElementById('modal_new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length > 0) {
        strengthDiv.style.display = 'block';
        
        let strength = 0;
        let feedback = [];
        
        // Check criteria
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
            strengthText.textContent = 'Strong password!';
        }
    } else {
        strengthDiv.style.display = 'none';
    }
});

// Change password form submission
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('modal_new_password').value;
    const confirmPassword = document.getElementById('modal_confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match!', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showNotification('Password must be at least 6 characters long', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const changeBtn = document.getElementById('changePasswordBtn');
    const originalText = changeBtn.innerHTML;
    
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
    changeBtn.disabled = true;
    
    fetch('<?= site_url('landlord/profile/change-password') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password changed successfully!', 'success');
            
            // Hide modal and reset form
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
            this.reset();
            document.getElementById('passwordStrength').style.display = 'none';
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        changeBtn.innerHTML = originalText;
        changeBtn.disabled = false;
    });
});

// Password confirmation validation
document.getElementById('modal_confirm_password').addEventListener('input', function() {
    const password = document.getElementById('modal_new_password').value;
    const confirmPassword = this.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        if (confirmPassword) this.classList.add('is-valid');
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    if (!editMode) return;
    
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
    }
    e.target.value = value;
});

// Form validation
function validateForm() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        if (alert && !alert.classList.contains('notification-alert')) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 7000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save (when in edit mode)
    if ((e.ctrlKey || e.metaKey) && e.key === 's' && editMode) {
        e.preventDefault();
        saveProfile();
    }
    
    // Escape to cancel edit
    if (e.key === 'Escape' && editMode) {
        e.preventDefault();
        cancelEdit();
    }
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>

<style>
/* Enhanced Profile Styling */
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
}

.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.form-control.border-primary {
    border-color: #4e73df !important;
    background-color: #f8f9fc;
}

.form-control.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Section Headers */
h6.border-bottom {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
}

/* Input Groups */
.input-group-text {
    background-color: #f8f9fc;
    border-color: #d1d3e2;
    color: #5a5c69;
    font-weight: 500;
}

/* Avatar Circle */
.avatar-circle {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.avatar-circle:hover {
    transform: scale(1.05);
}

/* Statistics */
.stat-item {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s ease;
}

.stat-item:hover {
    background-color: #f8f9fc;
}

.progress-sm {
    height: 4px;
}

/* Info Items */
.info-item {
    padding: 0.25rem;
    border-radius: 0.25rem;
}

/* Security Checklist */
.security-checklist .security-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid #f8f9fc;
}

.security-checklist .security-item:last-child {
    border-bottom: none;
}

/* Buttons */
.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-secondary:hover,
.btn-outline-dark:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Modal Enhancements */
.modal-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.modal-title {
    color: #5a5c69;
    font-weight: 600;
}

/* Password Strength */
.password-strength .progress {
    height: 8px;
    border-radius: 4px;
}

/* Badges */
.badge {
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

/* Alerts */
.alert {
    border-left: 0.25rem solid;
    border-radius: 0.375rem;
}

.alert-success {
    border-left-color: #1cc88a;
    background-color: rgba(28, 200, 138, 0.1);
}

.alert-info {
    border-left-color: #36b9cc;
    background-color: rgba(54, 185, 204, 0.1);
}

.alert-warning {
    border-left-color: #f6c23e;
    background-color: rgba(246, 194, 62, 0.1);
}

.alert-danger {
    border-left-color: #e74a3b;
    background-color: rgba(231, 74, 59, 0.1);
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        width: 100%;
    }
    
    .card-body {
        padding: 1.5rem 1rem;
    }
    
    .row.text-center .col-6 {
        margin-bottom: 1rem;
    }
    
    .d-grid.gap-2 .btn {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .h3 {
        font-size: 1.5rem;
    }
    
    .avatar-circle {
        width: 80px !important;
        height: 80px !important;
    }
    
    .avatar-circle span {
        font-size: 1.5rem !important;
    }
    
    .stat-item h3 {
        font-size: 1.5rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}

/* Animation Classes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.5s ease-out;
}

.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }

/* Loading States */
.fa-spinner {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Focus States */
.form-control:focus {
    transform: translateY(-1px);
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25), 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Notification Positioning */
.notification-alert {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: none;
}
</style>

<?= $this->endSection() ?>