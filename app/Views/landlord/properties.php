<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>My Properties<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> My Properties
        </h1>
        <div>
            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">Add Property</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Properties
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($properties) ? count($properties) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= isset($properties) ? number_format(array_reduce($properties, function ($carry, $prop) {
                                    return $carry + (($prop['rent_amount'] ?? $prop['base_rent']) * $prop['ownership_percentage'] / 100);
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

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter Properties</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="search_properties" class="form-label">Search Properties</label>
                    <input type="text" class="form-control" id="search_properties"
                        placeholder="Search by name, address, or tenant..." onkeyup="searchProperties()">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-control" id="filter_status" onchange="filterProperties()">
                        <option value="all">All Properties</option>
                        <option value="active">With Active Lease</option>
                        <option value="vacant">Vacant</option>
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

    <!-- Properties Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Properties Overview</h6>
            <span class="badge badge-primary" id="property_count"><?= isset($properties) ? count($properties) : 0 ?>
                properties</span>
        </div>
        <div class="card-body">
            <?php if (!empty($properties)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="propertiesTable">
                        <thead>
                            <tr>
                                <th>Property Details</th>
                                <th>Current Tenant</th>
                                <th>Lease Information</th>
                                <th>Financial</th>
                                <th>Ownership</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr class="property-row" data-status="<?= $property['lease_status'] ?>"
                                    data-ownership="<?= $property['ownership_percentage'] ?>"
                                    data-search="<?= strtolower(esc($property['property_name'] . ' ' . $property['address'] . ' ' . ($property['tenant_first_name'] ?? '') . ' ' . ($property['tenant_last_name'] ?? ''))) ?>">
                                    <td>
                                        <div>
                                            <strong><?= esc($property['property_name']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> <?= esc($property['address']) ?>
                                            </small>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-bed"></i> <?= $property['bedrooms'] ?? 'N/A' ?> bed |
                                                <i class="fas fa-bath"></i> <?= $property['bathrooms'] ?? 'N/A' ?> bath
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($property['tenant_first_name']): ?>
                                            <div>
                                                <strong><?= esc($property['tenant_first_name'] . ' ' . $property['tenant_last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope"></i> <?= esc($property['tenant_email'] ?? 'N/A') ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?= esc($property['tenant_phone'] ?? 'N/A') ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-user-slash"></i> Vacant
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($property['lease_start']): ?>
                                            <div>
                                                <small class="text-muted">Start:</small>
                                                <strong><?= date('M d, Y', strtotime($property['lease_start'])) ?></strong>
                                                <br>
                                                <small class="text-muted">End:</small>
                                                <strong><?= date('M d, Y', strtotime($property['lease_end'])) ?></strong>
                                                <br>
                                                <?php
                                                $daysToExpiry = ceil((strtotime($property['lease_end']) - time()) / (60 * 60 * 24));
                                                ?>
                                                <small class="<?= $daysToExpiry <= 60 ? 'text-warning' : 'text-info' ?>">
                                                    <?= $daysToExpiry > 0 ? $daysToExpiry . ' days left' : 'Expired' ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No active lease</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong
                                                class="text-success">$<?= number_format($property['rent_amount'] ?? $property['base_rent'], 2) ?></strong>
                                            <small class="text-muted">/month</small>
                                            <br>
                                            <small class="text-muted">Your share:</small>
                                            <strong
                                                class="text-primary">$<?= number_format(($property['rent_amount'] ?? $property['base_rent']) * $property['ownership_percentage'] / 100, 2) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            <?= $property['ownership_percentage'] ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-<?= $property['lease_status'] === 'active' ? 'success' : 'warning' ?>">
                                            <?= $property['lease_status'] === 'active' ? 'Occupied' : 'Vacant' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="viewPropertyDetails(<?= $property['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="viewMaintenanceHistory(<?= $property['id'] ?>)"
                                                title="Maintenance History">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                            <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>"
                                                class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($property['lease_status'] !== 'active'): ?>
                                                <button class="btn btn-sm btn-outline-success"
                                                    onclick="showAddTenantModal(<?= $property['id'] ?>)" title="Add Tenant">
                                                    <i class="fas fa-user-plus"></i>
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
                    <i class="fas fa-building fa-4x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-500">No Properties Found</h4>
                    <p class="text-muted">Contact your administrator to add properties to your account.</p>
                    <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Property Details Modal -->
<div class="modal fade" id="propertyDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Property Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="propertyDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCurrentProperty()">
                    <i class="fas fa-edit"></i> Edit Property
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance History Modal -->
<div class="modal fade" id="maintenanceHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Maintenance History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="maintenanceHistoryContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="addMaintenanceRequest()">
                    <i class="fas fa-plus"></i> Add Maintenance Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTenantForm">
                <div class="modal-body">
                    <input type="hidden" id="property_id" name="property_id">
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
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="rent_amount" class="form-label">Monthly Rent *</label>
                            <input type="number" class="form-control" id="rent_amount" name="rent_amount" step="0.01"
                                min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lease_start" class="form-label">Lease Start *</label>
                            <input type="date" class="form-control" id="lease_start" name="lease_start" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lease_end" class="form-label">Lease End *</label>
                            <input type="date" class="form-control" id="lease_end" name="lease_end" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="security_deposit" class="form-label">Security Deposit</label>
                        <input type="number" class="form-control" id="security_deposit" name="security_deposit"
                            step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentPropertyId = null;

    function searchProperties() {
        const searchTerm = document.getElementById('search_properties').value.toLowerCase();
        const rows = document.querySelectorAll('.property-row');
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

        document.getElementById('property_count').textContent = visibleCount + ' properties';
    }

    function filterProperties() {
        const statusFilter = document.getElementById('filter_status').value;
        const ownershipFilter = document.getElementById('filter_ownership').value;
        const rows = document.querySelectorAll('.property-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowOwnership = parseInt(row.getAttribute('data-ownership'));
            let showRow = true;

            // Status filter
            if (statusFilter === 'active' && rowStatus !== 'active') {
                showRow = false;
            } else if (statusFilter === 'vacant' && rowStatus === 'active') {
                showRow = false;
            }

            // Ownership filter
            if (ownershipFilter === 'high' && rowOwnership < 75) {
                showRow = false;
            } else if (ownershipFilter === 'medium' && (rowOwnership < 50 || rowOwnership >= 75)) {
                showRow = false;
            } else if (ownershipFilter === 'low' && rowOwnership >= 50) {
                showRow = false;
            }

            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('property_count').textContent = visibleCount + ' properties';
    }

    function clearFilters() {
        document.getElementById('search_properties').value = '';
        document.getElementById('filter_status').value = 'all';
        document.getElementById('filter_ownership').value = 'all';

        const rows = document.querySelectorAll('.property-row');
        rows.forEach(row => {
            row.style.display = '';
        });

        document.getElementById('property_count').textContent = rows.length + ' properties';
    }

    function viewPropertyDetails(propertyId) {
        currentPropertyId = propertyId;

        // Show loading
        document.getElementById('propertyDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading property details...</p>
        </div>
    `;

        new bootstrap.Modal(document.getElementById('propertyDetailsModal')).show();

        // Fetch property details
        fetch(`<?= site_url('landlord/property-details') ?>/${propertyId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                document.getElementById('propertyDetailsContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('propertyDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading property details: ${error.message}
            </div>
        `;
            });
    }

    function viewMaintenanceHistory(propertyId) {
        currentPropertyId = propertyId;

        // Show loading
        document.getElementById('maintenanceHistoryContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading maintenance history...</p>
        </div>
    `;

        new bootstrap.Modal(document.getElementById('maintenanceHistoryModal')).show();

        // Fetch maintenance history
        fetch(`<?= site_url('landlord/maintenance-history') ?>/${propertyId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                document.getElementById('maintenanceHistoryContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('maintenanceHistoryContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading maintenance history: ${error.message}
            </div>
        `;
            });
    }

    function editCurrentProperty() {
        if (currentPropertyId) {
            window.location.href = `<?= site_url('landlord/properties/edit') ?>/${currentPropertyId}`;
        }
    }

    function addMaintenanceRequest() {
        if (currentPropertyId) {
            window.location.href = `<?= site_url('landlord/add-maintenance') ?>?property_id=${currentPropertyId}`;
        }
    }

    function showAddTenantModal(propertyId) {
        document.getElementById('property_id').value = propertyId;
        new bootstrap.Modal(document.getElementById('addTenantModal')).show();
    }

    document.getElementById('addTenantForm').addEventListener('submit', function (e) {
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

    // Auto-populate lease end date (1 year from start)
    document.getElementById('lease_start').addEventListener('change', function () {
        const startDate = new Date(this.value);
        const endDate = new Date(startDate.setFullYear(startDate.getFullYear() + 1));
        document.getElementById('lease_end').value = endDate.toISOString().split('T')[0];
    });
</script>

<style>
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .btn-group .btn {
        margin-right: 0;
    }

    .property-row {
        transition: all 0.3s ease;
    }

    .property-row:hover {
        background-color: #f8f9fc;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
            border-radius: 0.25rem !important;
        }
    }
</style>

<?= $this->endSection() ?>