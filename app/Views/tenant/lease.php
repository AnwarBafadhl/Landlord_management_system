<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>My Lease<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-contract"></i> My Lease
        </h1>
        <?php if (isset($lease) && $lease): ?>
            <div class="btn-group">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Lease
                </button>
                <button class="btn btn-success" onclick="downloadLease()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($lease) && $lease): ?>
        <!-- Lease Information Card -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-contract"></i> Lease Agreement Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Property Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-building"></i> Property Information
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Property Name:</strong></td>
                                        <td><?= esc($lease['property_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td><?= esc($lease['property_address'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Property Type:</strong></td>
                                        <td><?= ucfirst($lease['property_type'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-calendar-alt"></i> Lease Terms
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Start Date:</strong></td>
                                        <td><?= isset($lease['lease_start']) ? date('F d, Y', strtotime($lease['lease_start'])) : 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>End Date:</strong></td>
                                        <td><?= isset($lease['lease_end']) ? date('F d, Y', strtotime($lease['lease_end'])) : 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lease Duration:</strong></td>
                                        <td>
                                            <?php 
                                            if (isset($lease['lease_start']) && isset($lease['lease_end'])) {
                                                $start = new DateTime($lease['lease_start']);
                                                $end = new DateTime($lease['lease_end']);
                                                $interval = $start->diff($end);
                                                echo $interval->m + ($interval->y * 12) . ' months';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-dollar-sign"></i> Financial Terms
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Monthly Rent:</strong></td>
                                        <td><span class="h5 text-success">$<?= number_format($lease['rent_amount'] ?? 0, 2) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Security Deposit:</strong></td>
                                        <td>$<?= number_format($lease['security_deposit'] ?? 0, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Due Date:</strong></td>
                                        <td>1st of each month</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-info-circle"></i> Lease Status
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge badge-<?= ($lease['status'] ?? '') === 'active' ? 'success' : 'warning' ?> p-2">
                                                <?= ucfirst($lease['status'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Days Remaining:</strong></td>
                                        <td>
                                            <?php 
                                            if (isset($lease['lease_end'])) {
                                                $daysRemaining = max(0, ceil((strtotime($lease['lease_end']) - time()) / (60 * 60 * 24)));
                                                echo $daysRemaining . ' days';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Renewal Status:</strong></td>
                                        <td>
                                            <?php if (isset($daysRemaining) && $daysRemaining < 60): ?>
                                                <span class="badge badge-warning">Renewal Available</span>
                                            <?php else: ?>
                                                <span class="text-muted">Not Yet Available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Landlord Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary">
                                    <i class="fas fa-user-tie"></i> Landlord Information
                                </h6>
                                <?php if (isset($landlords) && !empty($landlords)): ?>
                                    <div class="row">
                                        <?php foreach ($landlords as $landlord): ?>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6><?= esc(($landlord['first_name'] ?? '') . ' ' . ($landlord['last_name'] ?? '')) ?></h6>
                                                        <p class="mb-1">
                                                            <i class="fas fa-envelope"></i> 
                                                            <a href="mailto:<?= esc($landlord['email'] ?? '') ?>"><?= esc($landlord['email'] ?? 'N/A') ?></a>
                                                        </p>
                                                        <?php if (!empty($landlord['phone'])): ?>
                                                            <p class="mb-1">
                                                                <i class="fas fa-phone"></i> 
                                                                <a href="tel:<?= esc($landlord['phone']) ?>"><?= esc($landlord['phone']) ?></a>
                                                            </p>
                                                        <?php endif; ?>
                                                        <p class="mb-0">
                                                            <i class="fas fa-percentage"></i> 
                                                            <?= $landlord['ownership_percentage'] ?? 0 ?>% Ownership
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Landlord information not available.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Lease Terms & Conditions -->
                        <?php if (!empty($lease['lease_terms'])): ?>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary">
                                        <i class="fas fa-list-ul"></i> Terms & Conditions
                                    </h6>
                                    <div class="bg-light p-3 rounded">
                                        <?= nl2br(esc($lease['lease_terms'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lease Renewal Notice -->
        <?php if (isset($daysRemaining) && $daysRemaining < 60): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-<?= $daysRemaining < 30 ? 'danger' : 'warning' ?> shadow">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Lease Renewal Notice
                        </h5>
                        <p>
                            Your lease expires in <strong><?= $daysRemaining ?> days</strong> on 
                            <?= date('F d, Y', strtotime($lease['lease_end'])) ?>.
                        </p>
                        <hr>
                        <div class="d-flex gap-2">
                            <button class="btn btn-<?= $daysRemaining < 30 ? 'danger' : 'warning' ?>" onclick="requestRenewal()">
                                <i class="fas fa-redo"></i> Request Lease Renewal
                            </button>
                            <button class="btn btn-outline-primary" onclick="contactLandlord()">
                                <i class="fas fa-envelope"></i> Contact Landlord
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Lease Found -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                        <h4>No Active Lease Found</h4>
                        <p class="text-muted">You don't have an active lease in our system at the moment.</p>
                        <p class="text-muted">This could mean:</p>
                        <ul class="list-unstyled text-muted mb-4">
                            <li><i class="fas fa-check text-info"></i> Your lease is being processed</li>
                            <li><i class="fas fa-check text-info"></i> Your lease hasn't been uploaded yet</li>
                            <li><i class="fas fa-check text-info"></i> There's a temporary system issue</li>
                        </ul>
                        <div class="mt-4">
                            <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-primary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                            <a href="mailto:support@propertymanagement.com" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope"></i> Contact Support
                            </a>
                            <button class="btn btn-outline-info" onclick="location.reload()">
                                <i class="fas fa-sync"></i> Refresh Page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?= site_url('tenant/payments/make') ?>" class="btn btn-success btn-block">
                                <i class="fas fa-credit-card"></i><br>
                                Make Payment
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-tools"></i><br>
                                Submit Maintenance
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= site_url('tenant/payments') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-history"></i><br>
                                Payment History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Renewal Request Modal -->
<div class="modal fade" id="renewalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Lease Renewal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="renewalForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="renewal_duration" class="form-label">Preferred Renewal Duration</label>
                        <select class="form-select" id="renewal_duration" name="renewal_duration" required>
                            <option value="">Select Duration</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                            <option value="24">24 Months</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="renewal_message" class="form-label">Additional Message (Optional)</label>
                        <textarea class="form-control" id="renewal_message" name="message" rows="3" 
                                  placeholder="Any special requests or notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitRenewalRequest()">
                    <i class="fas fa-paper-plane"></i> Send Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function downloadLease() {
    // In a real application, this would generate and download a PDF
    alert('PDF download functionality will be implemented soon.');
}

function requestRenewal() {
    new bootstrap.Modal(document.getElementById('renewalModal')).show();
}

function contactLandlord() {
    window.location.href = '<?= site_url('tenant/messages') ?>?compose=landlord';
}

function submitRenewalRequest() {
    const form = document.getElementById('renewalForm');
    const formData = new FormData(form);
    formData.append('type', 'lease_renewal');
    formData.append('subject', 'Lease Renewal Request');
    formData.append('recipient', 'landlord');
    formData.append('message_type', 'lease');
    
    // Show loading state
    const sendBtn = event.target;
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    sendBtn.disabled = true;
    
    fetch('<?= site_url('tenant/messages/send') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Renewal request sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('renewalModal')).hide();
            form.reset();
        } else {
            alert('Error: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    })
    .finally(() => {
        // Restore button
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}

// Print styles
const printStyle = document.createElement('style');
printStyle.textContent = `
    @media print {
        .btn, .modal, .navbar, .sidebar { display: none !important; }
        .card { border: 1px solid #ddd !important; }
        .bg-light { background-color: #f8f9fa !important; }
    }
`;
document.head.appendChild(printStyle);
</script>
<?= $this->endSection() ?>