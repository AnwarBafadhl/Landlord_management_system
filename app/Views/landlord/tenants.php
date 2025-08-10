<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Tenant Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> Tenant Management
        </h1>
        <div>
            <button class="btn btn-primary" onclick="showAddTenantModal()">
                <i class="fas fa-user-plus"></i> Add New Tenant
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tenants
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($tenants) ? count($tenants) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Leases
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($tenants) ? array_reduce($tenants, function($carry, $tenant) { return $carry + ($tenant['lease_status'] === 'active' ? 1 : 0); }, 0) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Expiring Soon
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $expiringSoon = 0;
                                if (isset($tenants)) {
                                    foreach ($tenants as $tenant) {
                                        if (!empty($tenant['lease_end'])) {
                                            $daysToExpiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24));
                                            if ($daysToExpiry <= 60 && $daysToExpiry > 0) {
                                                $expiringSoon++;
                                            }
                                        }
                                    }
                                }
                                echo $expiringSoon;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Monthly Rent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= isset($tenants) ? number_format(array_reduce($tenants, function($carry, $tenant) { 
                                    return $carry + (($tenant['rent_amount'] ?? 0) * ($tenant['ownership_percentage'] ?? 0) / 100); 
                                }, 0), 2) : '0.00' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="property_filter" class="form-label">Property</label>
                    <select class="form-control" id="property_filter" name="property_id">
                        <option value="">All Properties</option>
                        <?php if (!empty($properties)): ?>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= $property['id'] ?>">
                                    <?= esc($property['property_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lease_status_filter" class="form-label">Lease Status</label>
                    <select class="form-control" id="lease_status_filter" name="lease_status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="payment_status_filter" class="form-label">Payment Status</label>
                    <select class="form-control" id="payment_status_filter" name="payment_status">
                        <option value="">All Payment Status</option>
                        <option value="current">Current</option>
                        <option value="late">Late</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= site_url('landlord/tenants') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tenant Directory</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportTenants('pdf')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportTenants('excel')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($tenants)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tenantsTable">
                        <thead>
                            <tr>
                                <th>Tenant Information</th>
                                <th>Property</th>
                                <th>Lease Details</th>
                                <th>Financial</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenants as $tenant): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?php if (!empty($tenant['profile_photo'])): ?>
                                                    <img src="<?= base_url('uploads/profiles/' . $tenant['profile_photo']) ?>" 
                                                         alt="<?= esc($tenant['first_name']) ?>" 
                                                         class="rounded-circle" width="50" height="50">
                                                <?php else: ?>
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <span class="text-white font-weight-bold">
                                                            <?= strtoupper(substr($tenant['first_name'], 0, 1) . substr($tenant['last_name'], 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?= esc($tenant['first_name'] . ' ' . $tenant['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card"></i> ID: <?= $tenant['id'] ?>
                                                </small>
                                                <?php if (!empty($tenant['date_of_birth'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        Age: <?= date_diff(date_create($tenant['date_of_birth']), date_create('now'))->y ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= esc($tenant['property_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> <?= esc($tenant['property_address']) ?>
                                        </small>
                                        <?php if (!empty($tenant['unit_number'])): ?>
                                            <br>
                                            <small class="text-info">Unit: <?= esc($tenant['unit_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($tenant['lease_start'])): ?>
                                            <div>
                                                <strong>Start:</strong> <?= date('M d, Y', strtotime($tenant['lease_start'])) ?>
                                                <br>
                                                <strong>End:</strong> <?= date('M d, Y', strtotime($tenant['lease_end'])) ?>
                                                <br>
                                                <?php 
                                                $daysToExpiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24));
                                                ?>
                                                <small class="<?= $daysToExpiry <= 60 ? 'text-warning' : 'text-info' ?>">
                                                    <?= $daysToExpiry > 0 ? $daysToExpiry . ' days left' : 'Expired' ?>
                                                </small>
                                                <?php if (!empty($tenant['security_deposit'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        Deposit: $<?= number_format($tenant['security_deposit'], 2) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No active lease</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-success">$<?= number_format($tenant['rent_amount'], 2) ?></strong>
                                            <small class="text-muted">/month</small>
                                            <br>
                                            <small class="text-muted">Your share:</small>
                                            <strong class="text-primary">
                                                $<?= number_format($tenant['rent_amount'] * $tenant['ownership_percentage'] / 100, 2) ?>
                                            </strong>
                                            <br>
                                            <?php 
                                            $paymentStatus = $tenant['payment_status'] ?? 'current';
                                            $statusClass = $paymentStatus === 'current' ? 'success' : ($paymentStatus === 'late' ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge badge-<?= $statusClass ?>">
                                                <?= ucfirst($paymentStatus) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted">Email:</small>
                                            <br>
                                            <a href="mailto:<?= esc($tenant['email']) ?>" class="text-decoration-none">
                                                <?= esc($tenant['email']) ?>
                                            </a>
                                            <br>
                                            <small class="text-muted">Phone:</small>
                                            <br>
                                            <a href="tel:<?= esc($tenant['phone']) ?>" class="text-decoration-none">
                                                <?= esc($tenant['phone'] ?? 'N/A') ?>
                                            </a>
                                            <?php if (!empty($tenant['emergency_contact'])): ?>
                                                <br>
                                                <small class="text-info">
                                                    <i class="fas fa-phone-alt"></i> Emergency: <?= esc($tenant['emergency_contact']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tenant['lease_status'] === 'active' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($tenant['lease_status']) ?>
                                        </span>
                                        <?php if ($daysToExpiry <= 30 && $daysToExpiry > 0): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Expiring Soon
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewTenantDetails(<?= $tenant['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="editTenant(<?= $tenant['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="sendMessage(<?= $tenant['id'] ?>)" title="Send Message">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-dark dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewPaymentHistory(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-credit-card"></i> Payment History
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="renewLease(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-file-contract"></i> Renew Lease
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="terminateLease(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-times-circle"></i> Terminate Lease
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-500">No Tenants Found</h4>
                    <p class="text-muted">No tenants match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lease Expiration Alerts -->
    <?php 
    $expiringTenants = [];
    if (isset($tenants)) {
        $expiringTenants = array_filter($tenants, function($tenant) {
            if (empty($tenant['lease_end'])) return false;
            $daysToExpiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24));
            return $daysToExpiry <= 60 && $daysToExpiry > 0;
        });
    }
    ?>
    <?php if (!empty($expiringTenants)): ?>
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-exclamation-triangle"></i> Lease Expiration Alerts
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($expiringTenants as $tenant): ?>
                        <?php $daysToExpiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24)); ?>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning">
                                <strong><?= esc($tenant['first_name'] . ' ' . $tenant['last_name']) ?></strong>
                                <br>
                                <small><?= esc($tenant['property_name']) ?></small>
                                <br>
                                <span class="badge badge-warning">
                                    Expires in <?= $daysToExpiry ?> days (<?= date('M d, Y', strtotime($tenant['lease_end'])) ?>)
                                </span>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-primary" onclick="renewLease(<?= $tenant['id'] ?>)">
                                        <i class="fas fa-file-contract"></i> Renew
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="sendMessage(<?= $tenant['id'] ?>)">
                                        <i class="fas fa-envelope"></i> Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTenantForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">Personal Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact</label>
                                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                            </div>
                        </div>
                        
                        <!-- Lease Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-success">Lease Information</h6>
                            <div class="mb-3">
                                <label for="property_id" class="form-label">Property *</label>
                                <select class="form-control" id="property_id" name="property_id" required>
                                    <option value="">Select Property</option>
                                    <?php if (!empty($properties)): ?>
                                        <?php foreach ($properties as $property): ?>
                                            <?php if ($property['lease_status'] !== 'active'): ?>
                                                <option value="<?= $property['id'] ?>">
                                                    <?= esc($property['property_name']) ?> - <?= esc($property['address']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lease_start" class="form-label">Lease Start *</label>
                                    <input type="date" class="form-control" id="lease_start" name="lease_start" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lease_end" class="form-label">Lease End *</label>
                                    <input type="date" class="form-control" id="lease_end" name="lease_end" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rent_amount" class="form-label">Monthly Rent *</label>
                                    <input type="number" class="form-control" id="rent_amount" name="rent_amount" 
                                           step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="security_deposit" class="form-label">Security Deposit</label>
                                    <input type="number" class="form-control" id="security_deposit" name="security_deposit" 
                                           step="0.01" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="unit_number" class="form-label">Unit Number</label>
                                <input type="text" class="form-control" id="unit_number" name="unit_number">
                            </div>
                            <div class="mb-3">
                                <label for="lease_notes" class="form-label">Lease Notes</label>
                                <textarea class="form-control" id="lease_notes" name="lease_notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Tenant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tenant Details Modal -->
<div class="modal fade" id="tenantDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tenant Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tenantDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printTenantDetails()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendMessageForm">
                <div class="modal-body">
                    <input type="hidden" id="message_tenant_id" name="tenant_id">
                    <div class="mb-3">
                        <label for="message_subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="message_subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message_type" class="form-label">Message Type</label>
                        <select class="form-control" id="message_type" name="message_type">
                            <option value="general">General</option>
                            <option value="lease_renewal">Lease Renewal</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="notice">Notice</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message_content" class="form-label">Message *</label>
                        <textarea class="form-control" id="message_content" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_email" name="send_email" checked>
                            <label class="form-check-label" for="send_email">
                                Send via email notification
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lease Renewal Modal -->
<div class="modal fade" id="renewLeaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renew Lease</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renewLeaseForm">
                <div class="modal-body">
                    <input type="hidden" id="renew_tenant_id" name="tenant_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_lease_start" class="form-label">New Lease Start *</label>
                            <input type="date" class="form-control" id="new_lease_start" name="lease_start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_lease_end" class="form-label">New Lease End *</label>
                            <input type="date" class="form-control" id="new_lease_end" name="lease_end" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_rent_amount" class="form-label">New Monthly Rent *</label>
                            <input type="number" class="form-control" id="new_rent_amount" name="rent_amount" 
                                   step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rent_increase" class="form-label">Rent Increase</label>
                            <input type="text" class="form-control" id="rent_increase" name="rent_increase" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="renewal_notes" class="form-label">Renewal Notes</label>
                        <textarea class="form-control" id="renewal_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-contract"></i> Renew Lease
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddTenantModal() {
    new bootstrap.Modal(document.getElementById('addTenantModal')).show();
}

function viewTenantDetails(tenantId) {
    fetch('<?= site_url('landlord/tenants/details') ?>/' + tenantId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('tenantDetailsContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('tenantDetailsModal')).show();
    })
    .catch(error => {
        alert('Error loading tenant details: ' + error.message);
    });
}

function editTenant(tenantId) {
    window.location.href = '<?= site_url('landlord/tenants/edit') ?>/' + tenantId;
}

function sendMessage(tenantId) {
    document.getElementById('message_tenant_id').value = tenantId;
    new bootstrap.Modal(document.getElementById('sendMessageModal')).show();
}

function viewPaymentHistory(tenantId) {
    window.location.href = '<?= site_url('landlord/payments') ?>?tenant_id=' + tenantId;
}

function renewLease(tenantId) {
    document.getElementById('renew_tenant_id').value = tenantId;
    // Set default new lease start to current lease end + 1 day
    fetch('<?= site_url('landlord/tenants/get-lease-info') ?>/' + tenantId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const currentEnd = new Date(data.lease_end);
            const newStart = new Date(currentEnd.getTime() + (24 * 60 * 60 * 1000));
            const newEnd = new Date(newStart.getTime() + (365 * 24 * 60 * 60 * 1000));
            
            document.getElementById('new_lease_start').value = newStart.toISOString().split('T')[0];
            document.getElementById('new_lease_end').value = newEnd.toISOString().split('T')[0];
            document.getElementById('new_rent_amount').value = data.current_rent;
        }
    });
    new bootstrap.Modal(document.getElementById('renewLeaseModal')).show();
}

function terminateLease(tenantId) {
    if (confirm('Are you sure you want to terminate this lease? This action cannot be undone.')) {
        fetch('<?= site_url('landlord/tenants/terminate') ?>/' + tenantId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function exportTenants(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    formData.append('format', format);
    const params = new URLSearchParams(formData).toString();
    window.open('<?= site_url('landlord/tenants/export') ?>?' + params, '_blank');
}

function printTenantDetails() {
    const content = document.getElementById('tenantDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Tenant Details</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="p-4">
                ${content}
                <script>window.print(); window.close();</script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Form submissions
document.getElementById('addTenantForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('landlord/tenants/add') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('landlord/tenants/send-message') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
            alert('Message sent successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

document.getElementById('renewLeaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('landlord/tenants/renew-lease') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

// Auto-populate lease end date (1 year from start)
document.getElementById('lease_start').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate.setFullYear(startDate.getFullYear() + 1));
    document.getElementById('lease_end').value = endDate.toISOString().split('T')[0];
});

// Calculate rent increase for renewal
document.getElementById('new_rent_amount').addEventListener('input', function() {
    const currentRent = parseFloat(document.getElementById('new_rent_amount').dataset.currentRent || 0);
    const newRent = parseFloat(this.value || 0);
    const increase = newRent - currentRent;
    const percentage = currentRent > 0 ? ((increase / currentRent) * 100).toFixed(1) : 0;
    
    document.getElementById('rent_increase').value = increase >= 0 
        ? `+${increase.toFixed(2)} (${percentage}%)`
        : `-${Math.abs(increase).toFixed(2)} (${percentage}%)`;
});

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData).toString();
    window.location.href = '<?= site_url('landlord/tenants') ?>?' + params;
});
</script>

<?= $this->endSection() ?>