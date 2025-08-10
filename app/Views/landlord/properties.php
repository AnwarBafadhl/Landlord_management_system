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
            <a href="<?= site_url('landlord/properties/add') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Request New Property
            </a>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Occupied
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($properties) ? array_reduce($properties, function ($carry, $prop) {
                                    return $carry + ($prop['lease_status'] === 'active' ? 1 : 0);
                                }, 0) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
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
                                Vacant
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($properties) ? array_reduce($properties, function ($carry, $prop) {
                                    return $carry + ($prop['lease_status'] !== 'active' ? 1 : 0);
                                }, 0) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
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

    <!-- Properties Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Properties Overview</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('all')">All Properties</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('occupied')">Occupied Only</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('vacant')">Vacant Only</a></li>
                </ul>
            </div>
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
                                <tr class="property-row" data-status="<?= $property['lease_status'] ?>">
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
                                            <strong class="text-success">$<?= number_format($property['rent_amount'] ?? $property['base_rent'], 2) ?></strong>
                                            <small class="text-muted">/month</small>
                                            <br>
                                            <small class="text-muted">Your share:</small>
                                            <strong class="text-primary">$<?= number_format(($property['rent_amount'] ?? $property['base_rent']) * $property['ownership_percentage'] / 100, 2) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            <?= $property['ownership_percentage'] ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $property['lease_status'] === 'active' ? 'success' : 'warning' ?>">
                                            <?= $property['lease_status'] === 'active' ? 'Occupied' : 'Vacant' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('landlord/properties/view/' . $property['id']) ?>"
                                                class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactAdminModal">
                        <i class="fas fa-envelope"></i> Contact Admin
                    </button>
                </div>
            <?php endif; ?>
            <!-- Contact Admin Modal -->
            <div class="modal fade" id="contactAdminModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-envelope"></i> Send Message to Administrator
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="contactAdminForm">
                            <?= csrf_field() ?>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Send a message to the administrator.</strong> You will receive email notifications when the admin replies.
                                </div>

                                <div class="mb-3">
                                    <label for="message_subject" class="form-label">Subject *</label>
                                    <select class="form-control" id="message_subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="Property Request">Property Addition Request</option>
                                        <option value="Property Issue">Property Issue/Problem</option>
                                        <option value="Account Support">Account Support</option>
                                        <option value="Technical Issue">Technical Issue</option>
                                        <option value="Billing Question">Billing/Payment Question</option>
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="customSubjectGroup" style="display: none;">
                                    <label for="custom_subject" class="form-label">Custom Subject *</label>
                                    <input type="text" class="form-control" id="custom_subject" name="custom_subject"
                                        placeholder="Enter your subject" maxlength="200">
                                </div>

                                <div class="mb-3">
                                    <label for="admin_message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="admin_message" name="message" rows="6" required
                                        placeholder="Type your message here..." maxlength="2000"></textarea>
                                    <small class="text-muted">Maximum 2000 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="priority_level" class="form-label">Priority Level</label>
                                    <select class="form-control" id="priority_level" name="priority">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <small class="text-muted">Select urgency level for your message</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
                            <input type="number" class="form-control" id="rent_amount" name="rent_amount" step="0.01" min="0" required>
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
                        <input type="number" class="form-control" id="security_deposit" name="security_deposit" step="0.01" min="0">
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
    function filterProperties(status) {
        const rows = document.querySelectorAll('.property-row');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all') {
                row.style.display = '';
            } else if (status === 'occupied' && rowStatus === 'active') {
                row.style.display = '';
            } else if (status === 'vacant' && rowStatus !== 'active') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function showAddTenantModal(propertyId) {
        document.getElementById('property_id').value = propertyId;
        new bootstrap.Modal(document.getElementById('addTenantModal')).show();
    }

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

    // Auto-populate lease end date (1 year from start)
    document.getElementById('lease_start').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(startDate.setFullYear(startDate.getFullYear() + 1));
        document.getElementById('lease_end').value = endDate.toISOString().split('T')[0];
    });


    document.addEventListener('DOMContentLoaded', function() {

        // Contact Admin Form Submission
        document.getElementById('contactAdminForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('<?= site_url('landlord/send-admin-message') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <strong>Success!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                        // Add alert to modal body
                        this.querySelector('.modal-body').insertBefore(alertDiv, this.querySelector('.modal-body').firstChild);

                        // Reset form
                        this.reset();
                        document.getElementById('customSubjectGroup').style.display = 'none';

                        // Close modal after 3 seconds
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('contactAdminModal')).hide();
                        }, 3000);
                    } else {
                        // Show error message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                        this.querySelector('.modal-body').insertBefore(alertDiv, this.querySelector('.modal-body').firstChild);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error!</strong> Failed to send message. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

                    this.querySelector('.modal-body').insertBefore(alertDiv, this.querySelector('.modal-body').firstChild);
                })
                .finally(() => {
                    // Restore button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Show/hide custom subject field
        document.getElementById('message_subject').addEventListener('change', function() {
            const customSubjectGroup = document.getElementById('customSubjectGroup');
            const customSubjectInput = document.getElementById('custom_subject');

            if (this.value === 'Other') {
                customSubjectGroup.style.display = 'block';
                customSubjectInput.required = true;
            } else {
                customSubjectGroup.style.display = 'none';
                customSubjectInput.required = false;
                customSubjectInput.value = '';
            }
        });

        // Character counter for message
        document.getElementById('admin_message').addEventListener('input', function() {
            const maxLength = 2000;
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;

            let counterElement = this.parentNode.querySelector('.char-counter');
            if (!counterElement) {
                counterElement = document.createElement('small');
                counterElement.className = 'char-counter text-muted';
                this.parentNode.appendChild(counterElement);
            }

            counterElement.textContent = `${remaining} characters remaining`;

            if (remaining < 100) {
                counterElement.className = 'char-counter text-warning';
            } else if (remaining < 0) {
                counterElement.className = 'char-counter text-danger';
            } else {
                counterElement.className = 'char-counter text-muted';
            }
        });

    });

    // Existing functions (filterProperties, showAddTenantModal, etc.)
</script>
<?= $this->endSection() ?>