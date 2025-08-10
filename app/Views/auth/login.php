<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?></title>
    
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
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            margin: auto;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
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
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: transform 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .side-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .side-panel h3 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .side-panel p {
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            opacity: 0.9;
        }
        
        .feature-list i {
            margin-right: 0.5rem;
            color: #fff;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <!-- Login Form -->
                        <div class="col-lg-6">
                            <div class="login-form">
                                <div class="login-header">
                                    <h2><i class="fas fa-home"></i> Property Manager</h2>
                                    <p>Sign in to your account</p>
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

                                <!-- Login Form -->
                                <form action="<?= site_url('auth/attempt-login') ?>" method="post">
                                    <?= csrf_field() ?>
                                    
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" 
                                               placeholder="Username or Email" value="<?= old('username') ?>" required>
                                        <label for="username">
                                            <i class="fas fa-user"></i> Username or Email
                                        </label>
                                    </div>

                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Password" required>
                                        <label for="password">
                                            <i class="fas fa-lock"></i> Password
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-login">
                                            <i class="fas fa-sign-in-alt"></i> Sign In
                                        </button>
                                    </div>
                                </form>

                                <div class="forgot-password">
                                    <a href="<?= site_url('auth/forgot-password') ?>">
                                        Forgot your password?
                                    </a>
                                </div>

                                <div class="text-center mt-3">
                                    <p class="mb-0">Don't have an account?</p>
                                    <a href="<?= site_url('register') ?>" class="text-primary">Register</a>
                                </div>
                            </div>
                        </div>

                        <!-- Side Panel -->
                        <div class="col-lg-6">
                            <div class="side-panel">
                                <h3>Welcome Back!</h3>
                                <p>
                                    Manage your properties, tenants, and maintenance requests with our 
                                    comprehensive landlord management system.
                                </p>

                                <ul class="feature-list">
                                    <li><i class="fas fa-building"></i> Property Management</li>
                                    <li><i class="fas fa-users"></i> Tenant Management</li>
                                    <li><i class="fas fa-dollar-sign"></i> Payment Tracking</li>
                                    <li><i class="fas fa-tools"></i> Maintenance Requests</li>
                                    <li><i class="fas fa-chart-bar"></i> Financial Reports</li>
                                    <li><i class="fas fa-mobile-alt"></i> Mobile Friendly</li>
                                </ul>

                                <div class="mt-4">
                                    <small>
                                        <i class="fas fa-shield-alt"></i>
                                        Your data is secure and protected
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Credentials Notice -->
    <div class="position-fixed bottom-0 start-0 p-3">
        <div class="card bg-dark text-white" style="max-width: 300px;">
            <div class="card-body">
                <h6 class="card-title">Demo Credentials</h6>
                <small>
                    <strong>Admin:</strong> admin / admin123<br>
                    <strong>Landlord:</strong> landlord / password<br>
                    <strong>Tenant:</strong> tenant / password<br>
                    <strong>Maintenance:</strong> maintenance / password
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }
            });
        }, 5000);

        // Add loading state to login button
        document.querySelector('form').addEventListener('submit', function() {
            const button = document.querySelector('.btn-login');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            button.disabled = true;
        });
    </script>
</body>
</html>