<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Register' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            margin: auto;
        }

        .register-form {
            padding: 2rem;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: transform 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .role-selection {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .role-option {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .role-option input[type="radio"] {
            margin-right: 0.75rem;
            transform: scale(1.2);
        }

        .role-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }

        .role-icon.landlord {
            background: #1cc88a;
        }

        .role-icon.tenant {
            background: #36b9cc;
        }

        .role-icon.maintenance {
            background: #f6c23e;
            color: #333;
        }

        .conditional-fields {
            display: none;
            background: #f8f9fc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .conditional-fields.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="register-container">
                    <div class="register-form">
                        <div class="register-header">
                            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                            <p>Register for Property Management System</p>
                        </div>

                        <!-- Flash Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Validation Errors -->
                        <?php if (session()->getFlashdata('validation')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form action="<?= site_url('register') ?>" method="post" id="registerForm">
                            <?= csrf_field() ?>


                            <!-- Role Selection -->
                            <div class="role-selection">
                                <h6 class="mb-3"><i class="fas fa-users"></i> Select Account Type</h6>

                                <div class="role-option" onclick="selectRole('landlord')">
                                    <input type="radio" name="role" value="landlord" id="role_landlord" required>
                                    <div class="role-icon landlord">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <strong>Landlord</strong>
                                        <br>
                                        <small class="text-muted">Manage properties and tenants</small>
                                    </div>
                                </div>

                                <div class="role-option" onclick="selectRole('tenant')">
                                    <input type="radio" name="role" value="tenant" id="role_tenant" required>
                                    <div class="role-icon tenant">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <strong>Tenant</strong>
                                        <br>
                                        <small class="text-muted">Pay rent and submit maintenance requests</small>
                                    </div>
                                </div>

                                <div class="role-option" onclick="selectRole('maintenance')">
                                    <input type="radio" name="role" value="maintenance" id="role_maintenance" required>
                                    <div class="role-icon maintenance">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <div>
                                        <strong>Maintenance</strong>
                                        <br>
                                        <small class="text-muted">Handle repair and service requests</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            placeholder="First Name" value="<?= old('first_name') ?>" required>
                                        <label for="first_name">
                                            <i class="fas fa-user"></i> First Name
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            placeholder="Last Name" value="<?= old('last_name') ?>" required>
                                        <label for="last_name">
                                            <i class="fas fa-user"></i> Last Name
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username"
                                            placeholder="Username" value="<?= old('username') ?>" required>
                                        <label for="username">
                                            <i class="fas fa-at"></i> Username
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Email" value="<?= old('email') ?>" required>
                                        <label for="email">
                                            <i class="fas fa-envelope"></i> Email Address
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" required>
                                        <label for="password">
                                            <i class="fas fa-lock"></i> Password
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                            placeholder="Confirm Password" required>
                                        <label for="confirm_password">
                                            <i class="fas fa-lock"></i> Confirm Password
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="Phone Number" value="<?= old('phone') ?>">
                                        <label for="phone">
                                            <i class="fas fa-phone"></i> Phone Number
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="address" name="address"
                                            placeholder="Address" style="height: 58px;"><?= old('address') ?></textarea>
                                        <label for="address">
                                            <i class="fas fa-map-marker-alt"></i> Address
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Landlord-specific fields -->
                            <div id="landlord_fields" class="conditional-fields">
                                <h6 class="mb-3"><i class="fas fa-building"></i> Landlord Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="bank_account" name="bank_account"
                                                placeholder="Bank Account" value="<?= old('bank_account') ?>">
                                            <label for="bank_account">
                                                <i class="fas fa-university"></i> Bank Account Number
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="bank_name" name="bank_name"
                                                placeholder="Bank Name" value="<?= old('bank_name') ?>">
                                            <label for="bank_name">
                                                <i class="fas fa-university"></i> Bank Name
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Bank information is used for rent collection and will be verified before activation.
                                </div>
                            </div>

                            <!-- Tenant-specific fields -->
                            <div id="tenant_fields" class="conditional-fields">
                                <h6 class="mb-3"><i class="fas fa-home"></i> Tenant Information</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Your account will be activated once a landlord assigns you to a property.
                                </div>
                            </div>

                            <!-- Maintenance-specific fields -->
                            <div id="maintenance_fields" class="conditional-fields">
                                <h6 class="mb-3"><i class="fas fa-tools"></i> Maintenance Staff Information</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Please provide any relevant certifications or experience details in the address field.
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and
                                    <a href="#" class="text-primary">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-register">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Already have an account?
                                <a href="<?= site_url('auth/login') ?>" class="text-primary">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function selectRole(role) {
            // Check the radio button
            document.getElementById('role_' + role).checked = true;

            // Hide all conditional fields
            document.querySelectorAll('.conditional-fields').forEach(function(field) {
                field.classList.remove('show');
            });

            // Show relevant fields
            document.getElementById(role + '_fields').classList.add('show');

            // Update required fields based on role
            updateRequiredFields(role);
        }

        function updateRequiredFields(role) {
            // Remove required attribute from all conditional fields
            document.querySelectorAll('.conditional-fields input').forEach(function(input) {
                input.removeAttribute('required');
            });

            // Add required attribute for landlord fields
            if (role === 'landlord') {
                document.getElementById('bank_account').setAttribute('required', 'required');
                document.getElementById('bank_name').setAttribute('required', 'required');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert && !alert.classList.contains('alert-info')) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }
            });
        }, 5000);

        // Add loading state to register button
        document.getElementById('registerForm').addEventListener('submit', function() {
            const button = document.querySelector('.btn-register');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            button.disabled = true;
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Select default role if coming back with old input
        <?php if (old('role')): ?>
            selectRole('<?= old('role') ?>');
        <?php endif; ?>
    </script>
</body>

</html>