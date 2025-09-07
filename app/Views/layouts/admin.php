<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Landlord Management System - Admin Panel">
    <meta name="author" content="Landlord Management System">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">

    <title><?= $this->renderSection('title') ?> - Admin Panel</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --muted: #858796;
            --ink: #5a5c69;
            --paper: #fff;
            --canvas: #f8f9fc;
            --hairline: #e3e6f0;
            --shadow: 0 0.15rem 1.25rem rgba(58, 59, 69, .15);
            --sidebar-w: 260px;
        }

        /* Normalize */
        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            font-family: 'Nunito', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--canvas);
            color: var(--ink);
        }

        #wrapper {
            min-height: 100vh
        }

        /* === Sidebar (fixed) === */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1040;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--primary) 0%, #224abe 100%);
            color: #fff;
            overflow-y: auto;
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, .06);
            transition: left .25s ease;
        }

        .sidebar-brand {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            color: #fff;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-decoration: none;
        }

        .sidebar .navbar-nav {
            padding: .5rem 0 1rem;
            margin: 0;
            list-style: none;
        }

        .sidebar .sidebar-divider {
            margin: .25rem 1rem;
            border-color: rgba(255, 255, 255, .15)
        }

        .sidebar .nav-item {
            margin: .15rem .75rem
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .675rem .875rem;
            color: rgba(255, 255, 255, .85);
            border-radius: .5rem;
            font-weight: 600;
            transition: background .2s, transform .15s;
        }

        .sidebar .nav-link i {
            width: 1.15rem;
            text-align: center
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            transform: translateX(2px)
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, .18);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
        }

        /* === Main content offset (prevents overlap) === */
        .main-content {
            margin-left: var(--sidebar-w);
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: var(--paper);
            box-shadow: var(--shadow);
            padding: .75rem 1rem;
        }

        .topbar .nav-link {
            color: var(--muted);
            font-weight: 600
        }

        .topbar .nav-link:hover {
            color: var(--ink)
        }

        /* Cards */
        .card {
            border: 0;
            border-radius: .65rem;
            background: var(--paper);
            box-shadow: var(--shadow)
        }

        .card-header {
            background: linear-gradient(180deg, #fff, #fafbff);
            border-bottom: 1px solid var(--hairline);
            border-top-left-radius: .65rem;
            border-top-right-radius: .65rem;
        }

        /* Accent stripe */
        .border-left-primary,
        .border-left-success,
        .border-left-info,
        .border-left-warning,
        .border-left-danger {
            position: relative;
            overflow: hidden
        }

        .border-left-primary::before,
        .border-left-success::before,
        .border-left-info::before,
        .border-left-warning::before,
        .border-left-danger::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: .35rem;
            border-radius: .35rem 0 0 .35rem;
        }

        .border-left-primary::before {
            background: var(--primary)
        }

        .border-left-success::before {
            background: var(--success)
        }

        .border-left-info::before {
            background: var(--info)
        }

        .border-left-warning::before {
            background: var(--warning)
        }

        .border-left-danger::before {
            background: var(--danger)
        }

        /* Tables */
        .table {
            color: var(--ink)
        }

        .table thead th {
            border-top: 0;
            border-bottom: 1px solid var(--hairline);
            text-transform: uppercase;
            font-size: .78rem;
            letter-spacing: .04em;
            color: var(--muted)
        }

        .table-hover>tbody>tr:hover {
            background: #fafbff
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fcfdff
        }

        /* Badges (support bg-* and badge-*) */
        .badge {
            border-radius: .5rem;
            font-weight: 700;
            letter-spacing: .2px
        }

        .bg-success,
        .badge-success {
            background: var(--success) !important
        }

        .bg-danger,
        .badge-danger {
            background: var(--danger) !important
        }

        .bg-warning,
        .badge-warning {
            background: var(--warning) !important;
            color: #212529
        }

        .bg-info,
        .badge-info {
            background: var(--info) !important
        }

        .bg-primary,
        .badge-primary {
            background: var(--primary) !important
        }

        /* Buttons */
        .btn {
            border-radius: .5rem;
            font-weight: 700
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary)
        }

        .btn-primary:hover {
            background: #3f5cc4;
            border-color: #3f5cc4
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success)
        }

        /* Alerts & utilities */
        .alert {
            border: 0;
            border-radius: .65rem
        }

        .text-gray-800 {
            color: var(--ink) !important
        }

        .text-gray-300 {
            color: #cfd4e7 !important
        }

        /* Spinner (for buttons) */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            vertical-align: -3px;
            margin-right: .4rem
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* === Mobile (sidebar becomes off-canvas) === */
        @media (max-width: 991.98px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-w));
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            /* full width content */
        }

        .card.h-100 {
            height: auto !important;
        }

        /* Spacing & sensible minimum for small stat cards */
        .stats-row .stat-card {
            min-height: 140px;
        }

        /* Make the two lower columns (“Recent …” & “Pending …”) equal height */
        .row-stretch>[class*="col-"] {
            display: flex;
        }

        .row-stretch .card {
            flex: 1 1 auto;
        }

        /* Ensure card content doesn’t squeeze into a vertical strip */
        .card,
        .card-body,
        .card-header {
            min-width: 0;
            /* fix flex overflow quirks */
            word-break: normal;
            overflow-wrap: anywhere;
        }

        /* Tighten default card padding a bit */
        .card-body {
            padding: 1rem 1.25rem;
        }

        /* Optional: nicer gap for all dashboard rows */
        .container-fluid .row {
            --bs-gutter-x: 1rem;
        }

        /* Keep stat cards on a neat responsive grid */
        @media (min-width: 1200px) {
            .stats-row>[class*="col-"] {
                margin-bottom: 0 !important;
            }
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <a class="sidebar-brand" href="<?= site_url('admin/dashboard') ?>">
                <i class="fas fa-home"></i>
                <span>Property Manager</span>
            </a>

            <hr class="sidebar-divider my-0" style="border-color: rgba(255, 255, 255, 0.15);">

            <ul class="navbar-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'admin/dashboard' ? 'active' : '' ?>"
                        href="<?= site_url('admin/dashboard') ?>">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">

                <!-- User Management -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/users') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/users') ?>">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>

                <!-- Property Management -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/properties') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/properties') ?>">
                        <i class="fas fa-fw fa-building"></i>
                        <span>Properties</span>
                    </a>
                </li>

                <!-- Financial Management -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/financials') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/financials') ?>">
                        <i class="fas fa-fw fa-dollar-sign"></i>
                        <span>Payments</span>
                    </a>
                </li>

                <!-- Maintenance -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/maintenance') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/maintenance') ?>">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Maintenance</span>
                    </a>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/reports') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/reports') ?>">
                        <i class="fas fa-fw fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider" style="border-color: rgba(255, 255, 255, 0.15);">

                <!-- System Settings -->
                <li class="nav-item">
                    <a class="nav-link <?= strpos(uri_string(), 'admin/settings') === 0 ? 'active' : '' ?>"
                        href="<?= site_url('admin/settings') ?>">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Settings</span>
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
                            <a class="dropdown-item" href="<?= site_url('admin/profile') ?>">
                                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" href="<?= site_url('admin/settings') ?>">
                                <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                                Settings
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
                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <?= session()->getFlashdata('info') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

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
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
            // optional: prevent background scroll when menu is open on mobile
            if (window.innerWidth < 992) document.body.style.overflow =
                document.getElementById('sidebar').classList.contains('active') ? 'hidden' : '';
        });


        // Auto-hide alerts after 5 seconds
        setTimeout(function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        alert.remove();
                    }, 500);
                }
            });
        }, 5000);

        // Loading states for forms
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                    submitBtn.disabled = true;

                    // Re-enable after 10 seconds in case of error
                    setTimeout(function () {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });

        // Confirm delete actions
        document.querySelectorAll('[data-confirm-delete]').forEach(function (element) {
            element.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });

        // Auto-refresh for real-time data (every 5 minutes)
        if (window.location.pathname.includes('/dashboard')) {
            setInterval(function () {
                location.reload();
            }, 300000); // 5 minutes
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>