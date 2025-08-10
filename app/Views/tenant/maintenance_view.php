<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Maintenance Request Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Maintenance Request Details
        </h1>
        <div class="btn-group">
            <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-secondary">
                <i class="fas fa-list"></i> All Requests
            </a>
            <?php if ($request && in_array($request['status'], ['pending', 'assigned'])): ?>
                <button class="btn btn-primary" onclick="contactLandlord()">
                    <i class="fas fa-envelope"></i> Contact Landlord
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($request): ?>
        <!-- Request Status Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : ($request['priority'] === 'urgent' ? 'danger' : 'info')) ?> shadow">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">
                                <i class="fas fa-<?= $request['status'] === 'completed' ? 'check-circle' : ($request['status'] === 'in_progress' ? 'spinner' : 'clock') ?>"></i>
                                Status: <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                            </h5>
                            <p class="mb-0">
                                Priority: <strong><?= ucfirst($request['priority']) ?></strong> | 
                                Requested: <?= date('M d, Y g:i A', strtotime($request['requested_date'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?> p-2 fs-6">
                                <?= ucfirst($request['priority']) ?> Priority
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Request Details -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> Request Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-primary">Request Details</h6>
                                <p class="mb-1"><strong>Request ID:</strong> #<?= str_pad($request['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                <p class="mb-1"><strong>Title:</strong> <?= esc($request['title']) ?></p>
                                <p class="mb-0"><strong>Category:</strong> <?= ucfirst(str_replace('_', ' ', $request['category'] ?? 'General')) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Property Information</h6>
                                <p class="mb-1"><strong><?= esc($request['property_name']) ?></strong></p>
                                <p class="mb-0"><?= esc($request['property_address']) ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary">Description</h6>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(esc($request['description'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($request['access_instructions'])): ?>
                            <div class="mb-4">
                                <h6 class="text-primary"><i class="fas fa-key"></i> Access Instructions</h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(esc($request['access_instructions'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Work Notes (if any) -->
                        <?php if (!empty($request['work_notes'])): ?>
                            <div class="mb-4">
                                <h6 class="text-primary"><i class="fas fa-sticky-note"></i> Work Notes</h6>
                                <div class="alert alert-info">
                                    <?= nl2br(esc($request['work_notes'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Materials Used (if completed) -->
                        <?php if (!empty($request['materials_used'])): ?>
                            <div class="mb-4">
                                <h6 class="text-primary"><i class="fas fa-tools"></i> Materials Used</h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(esc($request['materials_used'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Images -->
                <?php if (!empty($images)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-images"></i> Photos
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($images as $image): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <img src="<?= base_url('uploads/' . $image['image_path']) ?>" 
                                                 class="card-img-top" 
                                                 style="height: 200px; object-fit: cover; cursor: pointer;"
                                                 onclick="showImageModal('<?= base_url('uploads/' . $image['image_path']) ?>', '<?= esc($image['description']) ?>')">
                                            <div class="card-body p-2">
                                                <small class="text-muted">
                                                    <?= ucfirst($image['image_type']) ?> - 
                                                    <?= date('M d, Y', strtotime($image['created_at'])) ?>
                                                </small>
                                                <?php if (!empty($image['description'])): ?>
                                                    <p class="small mb-0"><?= esc($image['description']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status History -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history"></i> Status History
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Request Submitted</h6>
                                    <small class="text-muted"><?= date('M d, Y g:i A', strtotime($request['requested_date'])) ?></small>
                                    <p class="small mb-0">Initial request submitted by tenant</p>
                                </div>
                            </div>

                            <?php if ($request['assigned_date']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Request Assigned</h6>
                                        <small class="text-muted"><?= date('M d, Y g:i A', strtotime($request['assigned_date'])) ?></small>
                                        <?php if (!empty($request['staff_first_name'])): ?>
                                            <p class="small mb-0">Assigned to <?= esc($request['staff_first_name'] . ' ' . $request['staff_last_name']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($request['status'] === 'in_progress'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Work In Progress</h6>
                                        <small class="text-muted">Current Status</small>
                                        <p class="small mb-0">Maintenance work has begun</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($request['completed_date']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Request Completed</h6>
                                        <small class="text-muted"><?= date('M d, Y g:i A', strtotime($request['completed_date'])) ?></small>
                                        <p class="small mb-0">Work has been completed successfully</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Request Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard"></i> Request Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Request ID</small>
                            <p class="mb-0">#<?= str_pad($request['id'], 6, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Current Status</small>
                            <p class="mb-0">
                                <span class="badge badge-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : 'secondary') ?> p-2">
                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                </span>
                            </p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Priority Level</small>
                            <p class="mb-0">
                                <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?> p-2">
                                    <?= ucfirst($request['priority']) ?>
                                </span>
                            </p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Submitted Date</small>
                            <p class="mb-0"><?= date('M d, Y g:i A', strtotime($request['requested_date'])) ?></p>
                        </div>

                        <?php if ($request['estimated_cost'] > 0): ?>
                            <div class="mb-3">
                                <small class="text-muted">Estimated Cost</small>
                                <p class="mb-0">$<?= number_format($request['estimated_cost'], 2) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['actual_cost'] > 0): ?>
                            <div class="mb-3">
                                <small class="text-muted">Actual Cost</small>
                                <p class="mb-0">$<?= number_format($request['actual_cost'], 2) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['completed_date']): ?>
                            <div class="mb-0">
                                <small class="text-muted">Completion Time</small>
                                <p class="mb-0">
                                    <?php 
                                    $start = new DateTime($request['requested_date']);
                                    $end = new DateTime($request['completed_date']);
                                    $interval = $start->diff($end);
                                    echo $interval->days . ' days';
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assigned Staff (if any) -->
                <?php if (!empty($request['staff_first_name'])): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user-hard-hat"></i> Assigned Staff
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle fa-3x text-muted"></i>
                                </div>
                                <h6><?= esc($request['staff_first_name'] . ' ' . $request['staff_last_name']) ?></h6>
                                <p class="text-muted mb-2">Maintenance Technician</p>
                                <?php if (!empty($request['staff_phone'])): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-phone text-primary"></i>
                                        <a href="tel:<?= esc($request['staff_phone']) ?>"><?= esc($request['staff_phone']) ?></a>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($request['staff_email'])): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-envelope text-primary"></i>
                                        <a href="mailto:<?= esc($request['staff_email']) ?>"><?= esc($request['staff_email']) ?></a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (in_array($request['status'], ['pending', 'assigned', 'in_progress'])): ?>
                                <button class="btn btn-outline-primary" onclick="contactLandlord()">
                                    <i class="fas fa-envelope"></i> Contact Landlord
                                </button>
                                
                                <?php if (!empty($request['staff_first_name'])): ?>
                                    <button class="btn btn-outline-info" onclick="contactStaff()">
                                        <i class="fas fa-user-hard-hat"></i> Contact Staff
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> New Request
                            </a>
                            
                            <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> All Requests
                            </a>

                            <?php if ($request['status'] === 'completed'): ?>
                                <button class="btn btn-outline-warning" onclick="showFeedbackModal()">
                                    <i class="fas fa-star"></i> Rate Service
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card shadow border-danger">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle"></i> Emergency Contact
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">For urgent issues that need immediate attention:</p>
                        <div class="text-center">
                            <a href="tel:+1234567890" class="btn btn-danger">
                                <i class="fas fa-phone"></i> (123) 456-7890
                            </a>
                        </div>
                        <small class="text-muted d-block mt-2 text-center">
                            Available 24/7 for emergencies
                        </small>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Request Not Found -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                        <h4>Request Not Found</h4>
                        <p class="text-muted">The maintenance request you're looking for could not be found or you don't have permission to view it.</p>
                        <div class="mt-4">
                            <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All Requests
                            </a>
                            <a href="<?= site_url('tenant/dashboard') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" style="max-height: 500px;">
                <p id="modalImageDescription" class="mt-3 text-muted"></p>
            </div>
        </div>
    </div>
</div>

<!-- Contact Landlord Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Landlord</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" name="request_id" value="<?= $request['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="Regarding Maintenance Request #<?= str_pad($request['id'] ?? 0, 6, '0', STR_PAD_LEFT) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendMessage()">Send Message</button>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate Our Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <input type="hidden" name="request_id" value="<?= $request['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Overall Rating</label>
                        <div class="rating">
                            <input type="radio" name="rating" value="5" id="star5">
                            <label for="star5">⭐</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4">⭐</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3">⭐</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2">⭐</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1">⭐</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" 
                                  placeholder="How was your experience with our maintenance service?"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitFeedback()">Submit Rating</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating input {
    display: none;
}

.rating label {
    cursor: pointer;
    font-size: 24px;
    color: #ddd;
    transition: color 0.3s;
}

.rating label:hover,
.rating label:hover ~ label,
.rating input:checked ~ label {
    color: #ffc107;
}
</style>

<script>
function showImageModal(src, description) {
    document.getElementById('modalImage').src = src;
    document.getElementById('modalImageDescription').textContent = description || '';
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function contactLandlord() {
    new bootstrap.Modal(document.getElementById('contactModal')).show();
}

function contactStaff() {
    // This would open a contact form for the maintenance staff
    alert('Contact staff functionality will be implemented soon.');
}

function showFeedbackModal() {
    new bootstrap.Modal(document.getElementById('feedbackModal')).show();
}

function sendMessage() {
    const formData = new FormData(document.getElementById('contactForm'));
    
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
            alert('Message sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function submitFeedback() {
    const formData = new FormData(document.getElementById('feedbackForm'));
    
    fetch('<?= site_url('tenant/maintenance/feedback') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thank you for your feedback!');
            bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
<?= $this->endSection() ?>