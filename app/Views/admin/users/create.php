<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Create User<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus"></i> Create New User
        </h1>
        <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <!-- Create User Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('admin/users/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= old('first_name') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= old('last_name') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= old('username') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= old('email') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="landlord" <?= old('role') === 'landlord' ? 'selected' : '' ?>>Landlord</option>
                                <option value="maintenance" <?= old('role') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= old('phone') ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= old('address') ?></textarea>
                        </div>

                        <div id="landlord_fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_account">Bank Account</label>
                                        <input type="text" class="form-control" id="bank_account" name="bank_account" 
                                               value="<?= old('bank_account') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name">Bank Name</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                               value="<?= old('bank_name') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Account Active
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
                            </button>
                            <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide landlord fields based on role
document.getElementById('role').addEventListener('change', function() {
    const landlordFields = document.getElementById('landlord_fields');
    if (this.value === 'landlord') {
        landlordFields.style.display = 'block';
    } else {
        landlordFields.style.display = 'none';
    }
});

// Trigger on page load for old values
document.addEventListener('DOMContentLoaded', function() {
    const role = document.getElementById('role').value;
    if (role === 'landlord') {
        document.getElementById('landlord_fields').style.display = 'block';
    }
});

// Password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (password !== this.value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?= $this->endSection() ?>