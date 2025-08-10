<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Request New Property<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Request New Property
        </h1>
        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Main Request Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building"></i> Property Request Form
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This form will submit a request to the administrator to add a new property to your account.
                        You will be notified via email once the property has been reviewed and added to the system.
                    </div>

                    <form id="propertyRequestForm">
                        <?= csrf_field() ?>

                        <!-- Basic Property Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control" id="property_name" name="property_name" required
                                    placeholder="e.g., Sunset Apartments, Downtown Condo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ownership_percentage" class="form-label">Ownership Percentage *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="ownership_percentage" name="ownership_percentage"
                                        min="0" max="100" step="0.1" value="100" required>
                                    <div class="input-group-text">%</div>
                                </div>
                                <small class="text-muted">Enter your ownership percentage (0-100%)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="property_address" class="form-label">Property Address *</label>
                            <textarea class="form-control" id="property_address" name="property_address" rows="3" required
                                placeholder="Enter the complete property address including street, city, state, and ZIP code"></textarea>
                        </div>

                        <!-- Property Details -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="property_type" class="form-label">Property Type</label>
                                <select class="form-control" id="property_type" name="property_type">
                                    <option value="">Select Type</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">Single Family House</option>
                                    <option value="condo">Condominium</option>
                                    <option value="townhouse">Townhouse</option>
                                    <option value="duplex">Duplex</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <select class="form-control" id="bedrooms" name="bedrooms">
                                    <option value="">Select</option>
                                    <option value="0">Studio</option>
                                    <option value="1">1 Bedroom</option>
                                    <option value="2">2 Bedrooms</option>
                                    <option value="3">3 Bedrooms</option>
                                    <option value="4">4 Bedrooms</option>
                                    <option value="5">5+ Bedrooms</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <select class="form-control" id="bathrooms" name="bathrooms">
                                    <option value="">Select</option>
                                    <option value="1">1 Bathroom</option>
                                    <option value="2">2 Bathrooms</option>
                                    <option value="3">3 Bathrooms</option>
                                    <option value="4">4 Bathrooms</option>
                                    <option value="5">5+ Bathrooms</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estimated_rent" class="form-label">Estimated Monthly Rent</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="estimated_rent" name="estimated_rent"
                                        min="0" step="0.01" placeholder="1200.00">
                                    <div class="input-group-text">SAR</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="square_feet" class="form-label">Square Feet</label>
                                <input type="number" class="form-control" id="square_feet" name="square_feet"
                                    min="0" placeholder="1200">
                            </div>
                        </div>

                        <!-- Property Description -->
                        <div class="mb-3">
                            <label for="property_description" class="form-label">Property Description</label>
                            <textarea class="form-control" id="property_description" name="property_description" rows="4"
                                placeholder="Provide additional details about the property, its condition, special features, etc."></textarea>
                        </div>

                        <!-- Message to Admin -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Additional Message to Administrator</label>
                            <textarea class="form-control" id="message" name="message" rows="3"
                                placeholder="Any additional information or special requests for the administrator..."></textarea>
                        </div>

                        <!-- Important Notice -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Please ensure all information is accurate.
                            The administrator will review your request and may contact you via email for additional information or documentation.
                        </div>

                        <!-- Form Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" onclick="history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Process Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i> What Happens Next?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="text-primary mb-3">
                                <i class="fas fa-paper-plane fa-3x"></i>
                            </div>
                            <h6 class="font-weight-bold">1. Submit Request</h6>
                            <p class="text-muted small">Fill out and submit the property request form above</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-warning mb-3">
                                <i class="fas fa-search fa-3x"></i>
                            </div>
                            <h6 class="font-weight-bold">2. Admin Review</h6>
                            <p class="text-muted small">Administrator reviews your request and may contact you via email for more information</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h6 class="font-weight-bold">3. Property Added</h6>
                            <p class="text-muted small">Once approved, the property will appear in your properties list and you'll receive an email confirmation</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous Requests -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-history"></i> Recent Property Requests
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($previous_requests)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Address</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($previous_requests as $request): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($request['property_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($request['property_type'] ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <small><?= esc(substr($request['property_address'], 0, 50)) ?><?= strlen($request['property_address']) > 50 ? '...' : '' ?></small>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($request['created_at'])) ?>
                                                <br>
                                                <small class="text-muted"><?= date('h:i A', strtotime($request['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'under_review' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $badgeColor = $statusColors[$request['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $badgeColor ?>">
                                                    <?= ucwords(str_replace('_', ' ', $request['status'])) ?>
                                                </span>
                                                <?php if (!empty($request['admin_notes'])): ?>
                                                    <br>
                                                    <small class="text-muted" title="<?= esc($request['admin_notes']) ?>">
                                                        <i class="fas fa-comment"></i> Has notes
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?= $request['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="editRequest(<?= $request['id'] ?>)" title="Edit Request">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <h6>No Previous Property Requests</h6>
                            <p>This will be your first property request. Fill out the form above to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {

        // Form submission handler
        document.getElementById('propertyRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;

            // Remove any existing alerts
            const existingAlerts = this.querySelectorAll('.alert-success, .alert-danger');
            existingAlerts.forEach(alert => alert.remove());

            const formData = new FormData(this);

            fetch('<?= site_url('landlord/submit-property-request') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
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

                        this.insertBefore(alertDiv, this.firstChild);
                        this.reset();
                        this.scrollIntoView({
                            behavior: 'smooth'
                        });

                        // Redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = '<?= site_url('landlord/properties') ?>';
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

                        this.insertBefore(alertDiv, this.firstChild);
                        this.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> ${error.message}. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                    this.insertBefore(alertDiv, this.firstChild);
                    this.scrollIntoView({
                        behavior: 'smooth'
                    });
                })
                .finally(() => {
                    // Restore button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Ownership percentage validation
        const ownershipField = document.getElementById('ownership_percentage');
        if (ownershipField) {
            ownershipField.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (value > 100) {
                    this.value = 100;
                    showFieldWarning(this, 'Ownership percentage cannot exceed 100%');
                } else if (value < 0) {
                    this.value = 0;
                    showFieldWarning(this, 'Ownership percentage cannot be negative');
                }
            });
        }

        // Helper function to show field warnings
        function showFieldWarning(field, message) {
            // Remove existing warning
            let warning = field.closest('.input-group').parentNode.querySelector('.text-danger');
            if (warning) {
                warning.remove();
            }

            // Add new warning
            warning = document.createElement('small');
            warning.className = 'text-danger d-block mt-1';
            warning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            field.closest('.input-group').parentNode.appendChild(warning);

            // Remove warning after 3 seconds
            setTimeout(() => {
                if (warning && warning.parentNode) {
                    warning.remove();
                }
            }, 3000);
        }

        // Form validation feedback
        document.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });

            field.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Functions for request modal (if they exist on the page)
        window.viewRequest = function(requestId) {
            const modal = document.getElementById('requestDetailsModal');
            const content = document.getElementById('requestDetailsContent');

            if (modal && content) {
                fetch('<?= site_url('landlord/property-request-details') ?>/' + requestId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        content.innerHTML = html;
                        new bootstrap.Modal(modal).show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading request details. Please try again.');
                    });
            }
        };

        window.editRequest = function(requestId) {
            // You can implement edit functionality if needed
            alert('Edit functionality can be implemented here. Request ID: ' + requestId);
        };

    }); // End of DOMContentLoaded
</script>

<?= $this->endSection() ?>