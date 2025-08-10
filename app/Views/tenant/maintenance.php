<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Maintenance Requests<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Maintenance Requests
        </h1>
        <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Submit New Request
        </a>
    </div>

    <!-- Maintenance Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> My Maintenance Requests
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <div class="card mb-3 border-left-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title">
                                                <?= esc($request['title']) ?>
                                                <span class="badge badge-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : 'info') ?> ml-2">
                                                    <?= ucfirst($request['priority']) ?>
                                                </span>
                                            </h5>
                                            <p class="card-text"><?= esc($request['description']) ?></p>
                                            
                                            <div class="row text-sm">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fas fa-calendar"></i> 
                                                        <strong>Requested:</strong> <?= date('M d, Y g:i A', strtotime($request['requested_date'])) ?>
                                                    </p>
                                                    <?php if ($request['assigned_date']): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-user-check"></i> 
                                                            <strong>Assigned:</strong> <?= date('M d, Y', strtotime($request['assigned_date'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($request['completed_date']): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-check-circle"></i> 
                                                            <strong>Completed:</strong> <?= date('M d, Y', strtotime($request['completed_date'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if (!empty($request['staff_first_name'])): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-user-hard-hat"></i> 
                                                            <strong>Assigned to:</strong> <?= esc($request['staff_first_name'] . ' ' . $request['staff_last_name']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($request['estimated_cost'] > 0): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-dollar-sign"></i> 
                                                            <strong>Estimated Cost:</strong> $<?= number_format($request['estimated_cost'], 2) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($request['actual_cost'] > 0): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-receipt"></i> 
                                                            <strong>Actual Cost:</strong> $<?= number_format($request['actual_cost'], 2) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($request['work_notes'])): ?>
                                                <div class="mt-3">
                                                    <h6><i class="fas fa-sticky-note"></i> Work Notes:</h6>
                                                    <p class="bg-light p-2 rounded"><?= esc($request['work_notes']) ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($request['materials_used'])): ?>
                                                <div class="mt-3">
                                                    <h6><i class="fas fa-tools"></i> Materials Used:</h6>
                                                    <p class="bg-light p-2 rounded"><?= esc($request['materials_used']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 text-center">
                                            <div class="mb-3">
                                                <span class="badge badge-<?= $request['status'] === 'completed' ? 'success' : ($request['status'] === 'in_progress' ? 'warning' : 'secondary') ?> p-2">
                                                    <i class="fas fa-<?= $request['status'] === 'completed' ? 'check-circle' : ($request['status'] === 'in_progress' ? 'spinner' : 'clock') ?>"></i>
                                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="btn-group-vertical">
                                                <a href="<?= site_url('tenant/maintenance/view/' . $request['id']) ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                <?php if (in_array($request['status'], ['pending', 'assigned'])): ?>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="contactLandlord(<?= $request['id'] ?>)">
                                                        <i class="fas fa-envelope"></i> Contact Landlord
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                            <h4>No Maintenance Requests</h4>
                            <p class="text-muted">You haven't submitted any maintenance requests yet.</p>
                            <a href="<?= site_url('tenant/maintenance/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Submit Your First Request
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Statistics -->
    <?php if (!empty($requests)): ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($requests, function($r) { return $r['status'] === 'pending'; })) ?></h4>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($requests, function($r) { return $r['status'] === 'in_progress'; })) ?></h4>
                        <p class="mb-0">In Progress</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($requests, function($r) { return $r['status'] === 'completed'; })) ?></h4>
                        <p class="mb-0">Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($requests, function($r) { return $r['priority'] === 'urgent'; })) ?></h4>
                        <p class="mb-0">Urgent</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                    <input type="hidden" id="requestId" name="request_id">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
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

<script>
function contactLandlord(requestId) {
    document.getElementById('requestId').value = requestId;
    document.getElementById('subject').value = 'Regarding Maintenance Request #' + requestId;
    new bootstrap.Modal(document.getElementById('contactModal')).show();
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
</script>
<?= $this->endSection() ?>