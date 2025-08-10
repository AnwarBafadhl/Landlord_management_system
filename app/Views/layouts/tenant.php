<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Landlord Management System - Tenant Panel">
    <meta name="author" content="Landlord Management System">

    <title><?= $this->renderSection('title') ?> - Tenant Panel</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #36b9cc;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-color);
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-color) 10%, #2c9faf 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 0.35rem;
            margin: 0.125rem 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 0.5rem;
        }

        .sidebar-brand {
            height: 4.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            font-size: 1rem;
            padding: 0 1rem;
        }

        .sidebar-brand:hover {
            color: #fff;
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation */
        .topbar {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.75rem 1.5rem;
        }

        .topbar .navbar-nav .nav-item .nav-link {
            color: var(--secondary-color);
            padding: 0.75rem 1rem;
        }

        /* Cards */
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.35rem;
        }

        .card-header {
            background-color: var(--light-color);
            border-bottom: 1px solid #e3e6f0;
        }

        /* Border left cards */
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid var(--danger-color) !important;
        }

        /* Text colors */
        .text-primary { color: var(--primary-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-info { color: var(--info-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-gray-800 { color: #5a5c69 !important; }
        .text-gray-300 { color: #dddfeb !important; }

        /* Badges */
        .badge-success { background-color: var(--success-color); }
        .badge-danger { background-color: var(--danger-color); }
        .badge-warning { background-color: var(--warning-color); }
        .badge-info { background-color: var(--info-color); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: -100%;
                z-index: 1000;
                transition: all 0.3s;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <a class="sidebar-brand" href="<?= site_url('tenant/dashboard') ?>">
                <i class="fas fa-home"></i>
                <span>Tenant Portal</span>
            </a>

            <hr class="sidebar-divider my-0" style="border-color: rgba(255, 255, 255, 0.15);">

            <ul class="navbar-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'tenant/dashboard' ? 'active' : '' ?>" 
                       href="<?= site_url('tenant/dashboard') ?>">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">

                <!-- Payments -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'tenant/payments') === 0 ? 'active' : '' ?>" 
                       href="<?= site_url('tenant/payments') ?>">
                        <i class="fas fa-fw fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>

                <!-- Maintenance -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'tenant/maintenance') === 0 ? 'active' : '' ?>" 
                       href="<?= site_url('tenant/maintenance') ?>">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Maintenance</span>
                    </a>
                </li>

                <!-- Lease Information -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'tenant/lease') === 0 ? 'active' : '' ?>" 
                       href="<?= site_url('tenant/lease') ?>">
                        <i class="fas fa-fw fa-file-contract"></i>
                        <span>Lease Info</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">

                <!-- Profile -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'tenant/profile') === 0 ? 'active' : '' ?>" 
                       href="<?= site_url('tenant/profile') ?>">
                        <i class="fas fa-fw fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('auth/logout') ?>">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand topbar">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <ul class="navbar-nav ms-auto">
                    <!-- User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline text-gray-600 small">
                                <?= session()->get('full_name') ?>
                            </span>
                            <i class="fas fa-user-circle fa-2x text-gray-300"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="<?= site_url('tenant/profile') ?>">
                                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" href="<?= site_url('tenant/lease') ?>">
                                <i class="fas fa-file-contract fa-sm fa-fw me-2 text-gray-400"></i>
                                Lease Info
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= site_url('auth/logout') ?>">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid py-4">
                <!-- REMOVED: Flash Messages section - Let individual views handle their own messages -->
                
                <!-- Main Content -->
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Loading states for forms
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 10 seconds in case of error
                    setTimeout(function() {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>