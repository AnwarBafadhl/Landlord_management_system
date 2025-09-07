<?= $this->extend('layouts/maintenance') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-cog"></i> My Profile
        </h1>
        <div>
            <a href="<?= site_url('maintenance/dashboard') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Validation Error:</strong>
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i> Profile Information
                    </h6>
                </div>
                <div class="card-body">
                    <?= form_open('maintenance/profile/update', ['id' => 'profileForm']) ?>
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= esc($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= esc($user['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= esc($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= esc($user['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= esc($user['address'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= esc($user['username'] ?? '') ?>" readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" name="role" 
                                   value="<?= ucfirst($user['role'] ?? '') ?>" readonly>
                            <small class="text-muted">Role cannot be changed</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                    
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <!-- Account Security -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Account Security
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-key fa-3x text-muted mb-2"></i>
                        <p class="text-muted">Change your account password</p>
                    </div>
                    
                    <button type="button" class="btn btn-warning btn-block" onclick="openChangePasswordModal()">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-life-ring"></i> Help & Support
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-headset fa-3x text-muted mb-2"></i>
                        <p class="text-muted">Need help? Contact our support team</p>
                    </div>
                    
                    <button type="button" class="btn btn-info btn-block" onclick="openHelpModal()">
                        <i class="fas fa-envelope"></i> Send Help Message
                    </button>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Member Since:</strong>
                        </div>
                        <div class="col-sm-6">
                            <?= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : '—' ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Last Updated:</strong>
                        </div>
                        <div class="col-sm-6">
                            <?= !empty($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : '—' ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="badge bg-<?= ($user['is_active'] ?? 0) ? 'success' : 'danger' ?>">
                                <?= ($user['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
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
                <h5 class="modal-title"><i class="fas fa-lock"></i> Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('maintenance/profile/change-password', ['id' => 'changePasswordForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password *</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password *</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" 
                           minlength="6" required>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           minlength="6" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Change Password
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Help Message Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-life-ring"></i> Send Help Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('', ['id' => 'helpForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="help_subject" class="form-label">Subject *</label>
                    <select class="form-select" id="help_subject" name="subject" required>
                        <option value="">Select a subject...</option>
                        <option value="Technical Issue">Technical Issue</option>
                        <option value="Account Problem">Account Problem</option>
                        <option value="Work Assignment">Work Assignment Question</option>
                        <option value="Schedule Issue">Schedule Issue</option>
                        <option value="Payment/Cost Question">Payment/Cost Question</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3" id="customSubjectDiv" style="display: none;">
                    <label for="custom_subject" class="form-label">Custom Subject *</label>
                    <input type="text" class="form-control" id="custom_subject" name="custom_subject" 
                           maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="help_priority" class="form-label">Priority</label>
                    <select class="form-select" id="help_priority" name="priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="help_message" class="form-label">Message *</label>
                    <textarea class="form-control" id="help_message" name="message" rows="5" 
                              minlength="10" maxlength="2000" required 
                              placeholder="Please describe your issue in detail..."></textarea>
                    <small class="text-muted">Minimum 10 characters, maximum 2000 characters</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info" id="helpSubmitBtn">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    function toast(msg, type = 'info') {
        const cls = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const div = document.createElement('div');
        div.className = `alert ${cls} alert-dismissible fade show notification-alert position-fixed`;
        div.style.top = '20px'; div.style.right = '20px'; div.style.zIndex = '9999'; div.style.minWidth = '300px';
        div.innerHTML = `<i class="fas ${icon}"></i> ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div); 
        setTimeout(() => div.remove(), 4000);
    }

    function openChangePasswordModal() {
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    }

    function openHelpModal() {
        document.getElementById('help_subject').value = '';
        document.getElementById('custom_subject').value = '';
        document.getElementById('help_priority').value = 'normal';
        document.getElementById('help_message').value = '';
        document.getElementById('customSubjectDiv').style.display = 'none';
        new bootstrap.Modal(document.getElementById('helpModal')).show();
    }

    // Show/hide custom subject field
    document.getElementById('help_subject').addEventListener('change', function() {
        const customDiv = document.getElementById('customSubjectDiv');
        const customInput = document.getElementById('custom_subject');
        
        if (this.value === 'Other') {
            customDiv.style.display = 'block';
            customInput.required = true;
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });

    // Handle help form submission
    document.getElementById('helpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('helpSubmitBtn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch('<?= site_url('maintenance/help') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('helpModal')).hide();
                toast(data.message || 'Message sent successfully', 'success');
                // Reset form
                this.reset();
                document.getElementById('customSubjectDiv').style.display = 'none';
            } else {
                toast(data.message || 'Failed to send message', 'error');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            toast('An error occurred while sending the message', 'error');
        });
    });

    // Handle change password form submission
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate passwords match
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            toast('Passwords do not match', 'error');
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('<?= site_url('maintenance/profile/change-password') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.redirected) {
                // Handle redirect (success case)
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success !== undefined) {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                    toast('Password changed successfully', 'success');
                    this.reset();
                } else {
                    toast(data.message || 'Failed to change password', 'error');
                }
            }
        })
        .catch(error => {
            toast('An error occurred while changing password', 'error');
            console.error('Password change error:', error);
        });
    });

    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && newPassword !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

<style>
    .notification-alert {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        border: none;
    }
    
    .btn-block {
        display: block;
        width: 100%;
    }
</style>

<?= $this->endSection() ?>