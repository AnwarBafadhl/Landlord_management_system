<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>My Tenants<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> My Tenants
        </h1>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">
                <i class="fas fa-home"></i> Active Leases: <?= count($tenants ?? []) ?>
            </span>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php
                                $total_rent = 0;
                                if (!empty($tenants)) {
                                    foreach ($tenants as $tenant) {
                                        $total_rent += ($tenant['rent_amount'] ?? 0) * (($tenant['ownership_percentage'] ?? 100) / 100);
                                    }
                                }
                                echo number_format($total_rent, 2);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-muted">
                                <i class="fas fa-percentage"></i> Your ownership share
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Expiring Soon
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $expiring_count = 0;
                                if (!empty($tenants)) {
                                    foreach ($tenants as $tenant) {
                                        if (!empty($tenant['lease_end'])) {
                                            $days_to_expiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24));
                                            if ($days_to_expiry <= 60 && $days_to_expiry > 0) {
                                                $expiring_count++;
                                            }
                                        }
                                    }
                                }
                                echo $expiring_count;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Within 60 days
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Avg Lease Length
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $avg_months = 0;
                                if (!empty($tenants)) {
                                    $total_months = 0;
                                    $count = 0;
                                    foreach ($tenants as $tenant) {
                                        if (!empty($tenant['lease_start']) && !empty($tenant['lease_end'])) {
                                            $months = (strtotime($tenant['lease_end']) - strtotime($tenant['lease_start'])) / (60 * 60 * 24 * 30);
                                            $total_months += $months;
                                            $count++;
                                        }
                                    }
                                    if ($count > 0) {
                                        $avg_months = round($total_months / $count);
                                    }
                                }
                                echo $avg_months;
                                ?> Months
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center mt-2">
                        <div class="col">
                            <small class="text-muted">
                                <i class="fas fa-chart-line"></i> Average duration
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search"></i> Search & Filter Tenants
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="search_tenants" class="form-label">Search Tenants</label>
                    <input type="text" class="form-control" id="search_tenants"
                        placeholder="Search by name, property, or email..." onkeyup="searchTenants()">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_lease_status" class="form-label">Lease Status</label>
                    <select class="form-control" id="filter_lease_status" onchange="filterTenants()">
                        <option value="all">All Leases</option>
                        <option value="active">Active</option>
                        <option value="expiring">Expiring Soon (60 days)</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_payment_status" class="form-label">Payment Status</label>
                    <select class="form-control" id="filter_payment_status" onchange="filterTenants()">
                        <option value="all">All Payments</option>
                        <option value="current">Current</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Tenant Overview
            </h6>
            <span class="badge bg-primary" id="tenant_count"><?= count($tenants ?? []) ?> tenants</span>
        </div>
        <div class="card-body">
            <?php if (!empty($tenants)): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tenantsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Tenant Details</th>
                                <th>Property</th>
                                <th>Lease Information</th>
                                <th>Rent & Ownership</th>
                                <th>Last Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenants as $tenant): ?>
                                <?php
                                // Calculate lease expiry status
                                $lease_status = 'active';
                                $days_to_expiry = 0;
                                if (!empty($tenant['lease_end'])) {
                                    $days_to_expiry = ceil((strtotime($tenant['lease_end']) - time()) / (60 * 60 * 24));
                                    if ($days_to_expiry <= 0) {
                                        $lease_status = 'expired';
                                    } elseif ($days_to_expiry <= 60) {
                                        $lease_status = 'expiring';
                                    }
                                }

                                // Mock payment status (you should get this from actual payment data)
                                $payment_status = $tenant['payment_status'] ?? 'current';
                                ?>
                                <tr class="tenant-row" data-lease-status="<?= $lease_status ?>"
                                    data-payment-status="<?= $payment_status ?>"
                                    data-search="<?= strtolower(esc(($tenant['first_name'] ?? '') . ' ' . ($tenant['last_name'] ?? '') . ' ' . ($tenant['email'] ?? '') . ' ' . ($tenant['property_name'] ?? ''))) ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="tenant-avatar me-3">
                                                <div class="avatar-circle">
                                                    <?= strtoupper(substr($tenant['first_name'] ?? 'T', 0, 1) . substr($tenant['last_name'] ?? 'T', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= esc(($tenant['first_name'] ?? '') . ' ' . ($tenant['last_name'] ?? '')) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope"></i> <?= esc($tenant['email'] ?? 'N/A') ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?= esc($tenant['phone'] ?? 'N/A') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-primary"><?= esc($tenant['property_name'] ?? 'N/A') ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= esc($tenant['property_address'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted">Start:</small>
                                            <strong><?= !empty($tenant['lease_start']) ? date('M d, Y', strtotime($tenant['lease_start'])) : 'N/A' ?></strong>
                                            <br>
                                            <small class="text-muted">End:</small>
                                            <strong><?= !empty($tenant['lease_end']) ? date('M d, Y', strtotime($tenant['lease_end'])) : 'N/A' ?></strong>
                                            <br>
                                            <?php if ($days_to_expiry > 0): ?>
                                                <small class="<?= $days_to_expiry <= 60 ? 'text-warning' : 'text-info' ?>">
                                                    <i class="fas fa-clock"></i> <?= $days_to_expiry ?> days remaining
                                                </small>
                                            <?php elseif ($days_to_expiry <= 0 && !empty($tenant['lease_end'])): ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i> Lease expired
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong
                                                class="text-success">$<?= number_format($tenant['rent_amount'] ?? 0, 2) ?></strong>
                                            <small class="text-muted">/month</small>
                                            <br>
                                            <span class="badge bg-info text-white">
                                                <?= $tenant['ownership_percentage'] ?? 100 ?>% ownership
                                            </span>
                                            <br>
                                            <small class="text-primary">
                                                Your share:
                                                $<?= number_format(($tenant['rent_amount'] ?? 0) * (($tenant['ownership_percentage'] ?? 100) / 100), 2) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if (!empty($tenant['last_payment_date'])): ?>
                                                <strong><?= date('M d, Y', strtotime($tenant['last_payment_date'])) ?></strong>
                                                <br>
                                                <small class="text-success">
                                                    $<?= number_format($tenant['last_payment_amount'] ?? 0, 2) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                    $days_ago = floor((time() - strtotime($tenant['last_payment_date'])) / (60 * 60 * 24));
                                                    echo $days_ago . ' days ago';
                                                    ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> No payments
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <!-- Lease Status -->
                                            <span
                                                class="badge bg-<?= $lease_status === 'active' ? 'success' : ($lease_status === 'expiring' ? 'warning' : 'danger') ?>">
                                                <i
                                                    class="fas fa-<?= $lease_status === 'active' ? 'check' : ($lease_status === 'expiring' ? 'clock' : 'times') ?>"></i>
                                                <?= ucfirst($lease_status) ?> Lease
                                            </span>
                                            <!-- Payment Status -->
                                            <span class="badge bg-<?= $payment_status === 'current' ? 'success' : 'danger' ?>">
                                                <i
                                                    class="fas fa-<?= $payment_status === 'current' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                                <?= ucfirst($payment_status) ?> Payment
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary"
                                                onclick="viewTenantDetails(<?= $tenant['id'] ?? 0 ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success"
                                                onclick="contactTenant(<?= $tenant['id'] ?? 0 ?>)" title="Contact Tenant">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <button class="btn btn-outline-info"
                                                onclick="viewPaymentHistory(<?= $tenant['id'] ?? 0 ?>)" title="Payment History">
                                                <i class="fas fa-credit-card"></i>
                                            </button>
                                            <?php if ($lease_status === 'expiring' || $lease_status === 'expired'): ?>
                                                <button class="btn btn-outline-warning"
                                                    onclick="renewLease(<?= $tenant['id'] ?? 0 ?>)" title="Renew Lease">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
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
                    <p class="text-muted">You don't have any active tenants yet. Add tenants to your properties to get
                        started.</p>
                    <a href="<?= site_url('landlord/properties') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Manage Properties
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tenant Details Modal -->
<div class="modal fade" id="tenantDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user"></i> Tenant Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tenantDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCurrentTenant()">
                    <i class="fas fa-edit"></i> Edit Tenant
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Contact Tenant Modal -->
<div class="modal fade" id="contactTenantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Contact Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="contactTenantForm">
                <div class="modal-body">
                    <input type="hidden" id="contact_tenant_id" name="tenant_id">
                    <div class="mb-3">
                        <label for="message_subject" class="form-label">Subject *</label>
                        <select class="form-control" id="message_subject" name="subject" required>
                            <option value="">Select a subject...</option>
                            <option value="Rent Payment Reminder">Rent Payment Reminder</option>
                            <option value="Lease Renewal">Lease Renewal</option>
                            <option value="Property Inspection">Property Inspection</option>
                            <option value="Maintenance Update">Maintenance Update</option>
                            <option value="General Notice">General Notice</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message_content" class="form-label">Message *</label>
                        <textarea class="form-control" id="message_content" name="message" rows="5"
                            placeholder="Enter your message..." required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="send_email" name="send_email" checked>
                        <label class="form-check-label" for="send_email">
                            Send via email
                        </label>
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

<script>
    let currentTenantId = null;

    function searchTenants() {
        const searchTerm = document.getElementById('search_tenants').value.toLowerCase();
        const rows = document.querySelectorAll('.tenant-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('tenant_count').textContent = visibleCount + ' tenants';
    }

    function filterTenants() {
        const leaseFilter = document.getElementById('filter_lease_status').value;
        const paymentFilter = document.getElementById('filter_payment_status').value;
        const rows = document.querySelectorAll('.tenant-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowLeaseStatus = row.getAttribute('data-lease-status');
            const rowPaymentStatus = row.getAttribute('data-payment-status');
            let showRow = true;

            // Lease status filter
            if (leaseFilter !== 'all' && rowLeaseStatus !== leaseFilter) {
                showRow = false;
            }

            // Payment status filter
            if (paymentFilter !== 'all' && rowPaymentStatus !== paymentFilter) {
                showRow = false;
            }

            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('tenant_count').textContent = visibleCount + ' tenants';
    }

    function clearFilters() {
        document.getElementById('search_tenants').value = '';
        document.getElementById('filter_lease_status').value = 'all';
        document.getElementById('filter_payment_status').value = 'all';

        const rows = document.querySelectorAll('.tenant-row');
        rows.forEach(row => {
            row.style.display = '';
        });

        document.getElementById('tenant_count').textContent = rows.length + ' tenants';
    }

    function viewTenantDetails(tenantId) {
        currentTenantId = tenantId;

        // Show loading
        document.getElementById('tenantDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading tenant details...</p>
        </div>
    `;

        new bootstrap.Modal(document.getElementById('tenantDetailsModal')).show();

        // Fetch tenant details (you'll need to implement this endpoint)
        fetch(`<?= site_url('landlord/tenant-details') ?>/${tenantId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                document.getElementById('tenantDetailsContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('tenantDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading tenant details: ${error.message}
            </div>
        `;
            });
    }

    function contactTenant(tenantId) {
        document.getElementById('contact_tenant_id').value = tenantId;
        new bootstrap.Modal(document.getElementById('contactTenantModal')).show();
    }

    function viewPaymentHistory(tenantId) {
        window.location.href = `<?= site_url('landlord/payments') ?>?tenant_id=${tenantId}`;
    }

    function renewLease(tenantId) {
        if (confirm('Do you want to start the lease renewal process for this tenant?')) {
            // Implement lease renewal logic
            window.location.href = `<?= site_url('landlord/lease/renew') ?>/${tenantId}`;
        }
    }

    function editCurrentTenant() {
        if (currentTenantId) {
            window.location.href = `<?= site_url('landlord/tenants/edit') ?>/${currentTenantId}`;
        }
    }

    // Contact tenant form submission
    document.getElementById('contactTenantForm').addEventListener('submit', function (e) {
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
                    bootstrap.Modal.getInstance(document.getElementById('contactTenantModal')).hide();
                    showAlert('Message sent successfully!', 'success');
                    this.reset();
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error: ' + error.message, 'danger');
            });
    });

    // Show alert function
    function showAlert(message, type) {
        const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

        const container = document.querySelector('.container-fluid');
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHTML;
        container.insertBefore(tempDiv.firstElementChild, container.firstElementChild);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
</script>

<style>
    /* Enhanced Tenant Page Styling */
    .tenant-avatar .avatar-circle {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, .075);
        transform: translateX(2px);
        transition: all 0.2s ease;
    }

    .tenant-row {
        transition: all 0.2s ease;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .text-gray-300 {
        color: #dddfeb !important;
    }

    .mr-2 {
        margin-right: 0.5rem !important;
    }

    .no-gutters {
        margin-right: 0;
        margin-left: 0;
    }

    .no-gutters>.col,
    .no-gutters>[class*="col-"] {
        padding-right: 0;
        padding-left: 0;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.765625rem;
        border-radius: 0.2rem;
    }

    .gap-1 {
        gap: 0.25rem !important;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .btn-group {
            flex-direction: column;
            gap: 0.25rem;
        }

        .btn-group .btn {
            border-radius: 0.25rem !important;
        }

        .tenant-avatar .avatar-circle {
            width: 35px;
            height: 35px;
            font-size: 0.8rem;
        }

        .d-sm-flex {
            flex-direction: column;
            align-items: start !important;
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem 0.5rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            font-size: 0.8rem;
        }
    }

    /* Centered Tenant Cards Styling */
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .text-gray-300 {
        color: #dddfeb !important;
    }

    .mr-2 {
        margin-right: 0.5rem !important;
    }

    .no-gutters {
        margin-right: 0;
        margin-left: 0;
    }

    .no-gutters>.col,
    .no-gutters>[class*="col-"] {
        padding-right: 0;
        padding-left: 0;
    }

    /* Enhanced card hover effects */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Responsive adjustments for 3 centered cards */
    @media (max-width: 1200px) {
        .row.justify-content-center>[class*="col-xl-3"] {
            max-width: 300px;
            flex: 0 0 300px;
        }
    }

    @media (max-width: 992px) {
        .row.justify-content-center>[class*="col-"] {
            max-width: 350px;
        }
    }

    @media (max-width: 768px) {
        .row.justify-content-center {
            margin: 0 -0.5rem;
        }

        .row.justify-content-center>[class*="col-"] {
            padding: 0 0.5rem;
            max-width: none;
        }
    }
</style>

<?= $this->endSection() ?>