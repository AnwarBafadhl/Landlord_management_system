
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
            max-width: 800px;
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
            margin-bottom: 1.5rem;
        }

        .role-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .role-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .role-option.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .role-option input[type="radio"] {
            margin: 0;
        }

        .role-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .role-icon.landlord { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .role-icon.tenant { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); }
        .role-icon.maintenance { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); }

        .terms-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
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
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h2>Create Your Account</h2>
                            <p class="text-muted">Join our Property Management System</p>
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
                                        <small class="text-muted">Access your rental information</small>
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
                                        <small class="text-muted">Manage maintenance tasks</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
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

                            <!-- Optional Contact Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="Phone Number" value="<?= old('phone') ?>">
                                        <label for="phone">
                                            <i class="fas fa-phone"></i> Phone Number (Optional)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="address" name="address"
                                            placeholder="Address" value="<?= old('address') ?>">
                                        <label for="address">
                                            <i class="fas fa-map-marker-alt"></i> Address (Optional)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="terms-section">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        <i class="fas fa-check"></i>
                                        I agree to the <a href="#" class="text-primary">Terms of Service</a> 
                                        and <a href="#" class="text-primary">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-register text-white">
                                    <i class="fas fa-user-plus"></i> Create Account & Get Started
                                </button>
                            </div>
                        </form>

                        <!-- Login Link -->
                        <div class="login-link">
                            <p class="mb-0">Already have an account? 
                                <a href="<?= site_url('auth/login') ?>">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </a>
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
            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');

            // Check the radio button
            document.getElementById('role_' + role).checked = true;
        }

        // Handle form submission
        document.getElementById('registerForm').addEventListener('submit', function() {
            const button = document.querySelector('.btn-register');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Your Account...';
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