<?= $this->extend('layouts/maintenance') ?>
<?= $this->section('title') ?>Work Orders<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-tools"></i> Work Orders</h1>
        <a href="<?= site_url('maintenance/dashboard') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <form class="row g-2" method="get" action="<?= site_url('maintenance/requests') ?>">
                <div class="col-md-4">
                    <label class="form-label">Status (My Work)</label>
                    <select class="form-select" name="status">
                        <?php
                        $statuses = ['', 'approved', 'in_progress', 'completed', 'cancelled'];
                        $labels = ['All', 'Approved', 'In Progress', 'Completed', 'Cancelled'];
                        foreach ($statuses as $i => $st):
                            $sel = ($current_status ?? '') === $st ? 'selected' : '';
                            ?>
                            <option value="<?= esc($st) ?>" <?= $sel ?>><?= $labels[$i] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority (Both tabs)</label>
                    <select class="form-select" name="priority">
                        <?php
                        $priorities = ['', 'low', 'normal', 'high', 'urgent'];
                        $plabels = ['All', 'Low', 'Normal', 'High', 'Urgent'];
                        foreach ($priorities as $i => $pr):
                            $sel = ($current_priority ?? '') === $pr ? 'selected' : '';
                            ?>
                            <option value="<?= esc($pr) ?>" <?= $sel ?>><?= $plabels[$i] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                    <a class="btn btn-light" href="<?= site_url('maintenance/requests') ?>">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="woTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                type="button" role="tab"><i class="fas fa-inbox"></i> Pending Queue</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="mywork-tab" data-bs-toggle="tab" data-bs-target="#mywork" type="button"
                role="tab"><i class="fas fa-briefcase"></i> My Work</button>
        </li>
    </ul>

    <div class="tab-content card shadow" id="woTabsContent">
        <!-- Pending Queue -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-inbox"></i> Pending Queue</h6>
                <span class="text-muted small"><?= count($pending_requests ?? []) ?> records</span>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_requests)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Priority</th>
                                    <th>Title</th>
                                    <th>Property / Unit</th>
                                    <th>Requested</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                foreach (($pending_requests ?? []) as $r): ?>
                                    <?php
                                    $prio = strtolower($r['priority'] ?? 'normal');
                                    $pClass = $prio === 'urgent' ? 'danger' : ($prio === 'high' ? 'warning' : ($prio === 'normal' ? 'info' : 'secondary'));
                                    ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><span class="badge bg-<?= $pClass ?>"><?= ucfirst($prio) ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($r['title']) ?></div>
                                            <div class="text-muted small">
                                                <?= esc(character_limiter($r['description'] ?? '', 80)) ?>
                                            </div>
                                            <?php if (!empty($cancelled_ids) && in_array((int) $r['id'], $cancelled_ids, true)): ?>
                                                <span class="badge bg-dark mt-1">You cancelled this earlier</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-building"></i> <?= esc($r['property_name'] ?? '—') ?></div>
                                            <?php if (!empty($r['unit_name'])): ?>
                                                <div class="text-muted small"><i class="fas fa-door-open"></i> Unit:
                                                    <?= esc($r['unit_name']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($r['requested_date']) ? date('M d, Y', strtotime($r['requested_date'])) : '—' ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a class="btn btn-outline-primary" title="View"
                                                    href="<?= site_url('maintenance/requests/view/' . $r['id']) ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success" title="Accept"
                                                    onclick="openAcceptModal(<?= (int) $r['id'] ?>, <?= (float) ($r['estimated_cost'] ?? 0) ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <div>No pending requests</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Work -->
        <div class="tab-pane fade" id="mywork" role="tabpanel">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-briefcase"></i> My Work</h6>
                <span class="text-muted small"><?= count($requests ?? []) ?> records</span>
            </div>
            <div class="card-body">
                <?php if (!empty($requests)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Priority</th>
                                    <th>Title</th>
                                    <th>Property / Unit</th>
                                    <th>Scheduled</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $j = 1;
                                foreach (($requests ?? []) as $r): ?>
                                    <?php
                                    $prio = strtolower($r['priority'] ?? 'normal');
                                    $pClass = $prio === 'urgent' ? 'danger' : ($prio === 'high' ? 'warning' : ($prio === 'normal' ? 'info' : 'secondary'));

                                    $status = strtolower($r['status'] ?? '');
                                    $statusMap = [
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'approved' => 'primary',
                                        'cancelled' => 'dark',
                                        'pending' => 'secondary',
                                        'rejected' => 'danger',
                                    ];
                                    $sClass = $statusMap[$status] ?? 'secondary';
                                    ?>
                                    <tr>
                                        <td><?= $j++ ?></td>
                                        <td><span class="badge bg-<?= $pClass ?>"><?= ucfirst($prio) ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($r['title']) ?></div>
                                            <div class="text-muted small">
                                                <?= esc(character_limiter($r['description'] ?? '', 80)) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-building"></i> <?= esc($r['property_name'] ?? '—') ?></div>
                                            <?php if (!empty($r['unit_name'])): ?>
                                                <div class="text-muted small"><i class="fas fa-door-open"></i> Unit:
                                                    <?= esc($r['unit_name']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($r['assigned_date']) ? date('M d, Y', strtotime($r['assigned_date'])) : '—' ?>
                                        </td>
                                        <?php
                                        $wasCancelledByMe = !empty($cancelled_ids) && in_array((int) $r['id'], $cancelled_ids, true);
                                        $displayStatus = ($wasCancelledByMe && ($r['status'] === 'pending')) ? 'cancelled' : $r['status'];

                                        $status = strtolower($displayStatus ?? '');
                                        $statusMap = [
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'approved' => 'primary',
                                            'cancelled' => 'dark',
                                            'pending' => 'secondary',
                                            'rejected' => 'danger',
                                        ];
                                        $sClass = $statusMap[$status] ?? 'secondary';
                                        ?>
                                        <td><span
                                                class="badge bg-<?= $sClass ?>"><?= ucfirst(str_replace('_', ' ', $displayStatus)) ?></span>
                                            <?php if ($wasCancelledByMe): ?>
                                                <span class="badge bg-dark ms-1">You cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a class="btn btn-outline-primary" title="View"
                                                    href="<?= site_url('maintenance/requests/view/' . $r['id']) ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <?php if ($r['status'] === 'approved'): ?>
                                                    <button class="btn btn-outline-success" title="Start Work"
                                                        onclick="openStartModal(<?= (int) $r['id'] ?>)">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($r['status'] === 'in_progress'): ?>
                                                    <button class="btn btn-outline-warning" title="Complete"
                                                        onclick="openCompleteModal(<?= (int) $r['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (in_array($r['status'], ['approved', 'in_progress', 'completed'])): ?>
                                                    <button class="btn btn-outline-secondary" title="Upload Image"
                                                        onclick="openUploadModal(<?= (int) $r['id'] ?>)">
                                                        <i class="fas fa-image"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (in_array($r['status'], ['approved', 'in_progress'])): ?>
                                                    <button class="btn btn-outline-danger" title="Cancel"
                                                        onclick="confirmCancel(<?= (int) $r['id'] ?>)">
                                                        <i class="fas fa-times"></i>
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
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <div>No requests found</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Accept Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check"></i> Accept Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('', ['id' => 'acceptForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="accept_request_id" name="request_id">
                <div class="alert alert-info small" id="accept_hint"></div>
                <div class="mb-3">
                    <label class="form-label">Approved Cost (SAR) *</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="approved_cost"
                        id="approved_cost" required placeholder="Enter your approved cost...">
                    <small class="text-muted">If landlord set an estimate, your cost must be ≤ estimate + 150
                        SAR.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="acceptSubmitBtn"><i class="fas fa-check"></i> Accept</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Start Work Modal -->
<div class="modal fade" id="startModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-play"></i> Start Work</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('', ['id' => 'startForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="start_request_id" name="request_id">
                <div class="mb-3">
                    <label class="form-label">Duration (days) *</label>
                    <input type="number" class="form-control" min="1" max="60" name="duration_days" id="duration_days"
                        required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Work Notes (optional)</label>
                    <textarea class="form-control" name="work_notes" id="work_notes" rows="2"></textarea>
                </div>
                <small class="text-muted">This will block your calendar for the specified duration.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="startSubmitBtn"><i class="fas fa-play"></i> Start</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check"></i> Complete Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open_multipart('', ['id' => 'completeForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="complete_request_id" name="request_id">
                <div class="mb-3">
                    <label class="form-label">Completion Notes *</label>
                    <textarea class="form-control" name="completion_notes" id="completion_notes" rows="3" required
                        placeholder="Describe the work completed..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Completion Images *</label>
                    <input type="file" class="form-control" name="completion_images[]" id="completion_images"
                        accept=".jpg,.jpeg,.png" multiple required>
                    <small class="text-muted">Upload one or more images showing the completed work.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning" id="completeSubmitBtn"><i class="fas fa-check"></i> Mark
                    Complete</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Upload Image Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-image"></i> Upload Work Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open_multipart('', ['id' => 'uploadForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="upload_request_id" name="request_id">
                <div class="mb-3">
                    <label class="form-label">Image *</label>
                    <input type="file" class="form-control" name="image" id="image" accept=".jpg,.jpeg,.png" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="image_type" id="image_type">
                        <option value="after">After</option>
                        <option value="before">Before</option>
                        <option value="issue">Issue</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="2"
                        placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info" id="uploadSubmitBtn"><i class="fas fa-upload"></i> Upload</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Hidden form for cancellation -->
<?= form_open('', ['id' => 'cancelForm', 'style' => 'display:none']) ?>
<?= csrf_field() ?>
<input type="hidden" name="status" value="cancelled">
<input type="hidden" name="work_notes" id="cancel_notes">
<?= form_close() ?>

<script>
    function toast(msg, type = 'info') {
        const cls = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const div = document.createElement('div');
        div.className = `alert ${cls} alert-dismissible fade show notification-alert position-fixed`;
        div.style.top = '20px'; div.style.right = '20px'; div.style.zIndex = '9999'; div.style.minWidth = '300px';
        div.innerHTML = `<i class="fas ${icon}"></i> ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div); 
        setTimeout(() => div.remove(), 4000);
    }

    // Accept modal
    function openAcceptModal(id, estimated) {
        // Wait a moment for DOM to be ready after any previous modal operations
        setTimeout(() => {
            const acceptRequestIdElement = document.getElementById('accept_request_id');
            const acceptHintElement = document.getElementById('accept_hint');
            const approvedCostElement = document.getElementById('approved_cost');
            
            if (!acceptRequestIdElement || !acceptHintElement || !approvedCostElement) {
                console.error('Modal elements not found:', {
                    acceptRequestIdElement: !!acceptRequestIdElement,
                    acceptHintElement: !!acceptHintElement,
                    approvedCostElement: !!approvedCostElement
                });
                toast('Modal elements not found. Please refresh the page.', 'error');
                return;
            }
            
            acceptRequestIdElement.value = id;
            const hint = (estimated && Number(estimated) > 0)
                ? `Landlord estimate: <strong>${Number(estimated).toFixed(2)}</strong> SAR. Your approved cost must be ≤ estimate + 150.`
                : `No landlord estimate. You may enter any approved cost.`;
            acceptHintElement.innerHTML = hint;
            approvedCostElement.value = '';
            
            const modalElement = document.getElementById('acceptModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                toast('Modal not found. Please refresh the page.', 'error');
            }
        }, 100);
    }

    // Handle accept form submission
    document.getElementById('acceptForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const id = document.getElementById('accept_request_id').value;
        
        fetch('<?= site_url('maintenance/requests/accept') ?>/' + id, {
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            bootstrap.Modal.getInstance(document.getElementById('acceptModal')).hide();
            if (d.success) { 
                toast('Accepted', 'success'); 
                setTimeout(() => location.reload(), 1000);
            } else { 
                toast(d.message || 'Failed', 'error'); 
            }
        }).catch(err => toast(err.message, 'error'));
    });

    // Start modal
    function openStartModal(id) {
        document.getElementById('start_request_id').value = id;
        document.getElementById('duration_days').value = '';
        document.getElementById('work_notes').value = '';
        new bootstrap.Modal(document.getElementById('startModal')).show();
    }

    // Handle start form submission
    document.getElementById('startForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const id = document.getElementById('start_request_id').value;
        
        fetch('<?= site_url('maintenance/requests/start') ?>/' + id, {
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            bootstrap.Modal.getInstance(document.getElementById('startModal')).hide();
            if (d.success) { 
                toast('Work started', 'success'); 
                setTimeout(() => location.reload(), 1000);
            } else { 
                toast(d.message || 'Failed', 'error'); 
            }
        }).catch(err => toast(err.message, 'error'));
    });

    // Complete modal
    function openCompleteModal(id) {
        document.getElementById('complete_request_id').value = id;
        document.getElementById('completion_notes').value = '';
        document.getElementById('completion_images').value = '';
        new bootstrap.Modal(document.getElementById('completeModal')).show();
    }

    // Handle complete form submission
    document.getElementById('completeForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const notes = document.getElementById('completion_notes').value.trim();
        const files = document.getElementById('completion_images').files;
        
        if (!notes) { 
            toast('Completion notes are required', 'error'); 
            return; 
        }
        if (!files || files.length === 0) { 
            toast('Please upload at least one completion image', 'error'); 
            return; 
        }

        const fd = new FormData(this);
        const id = document.getElementById('complete_request_id').value;

        fetch('<?= site_url('maintenance/requests/complete') ?>/' + id, {
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
            if (d.success) { 
                toast('Request completed', 'success'); 
                setTimeout(() => location.reload(), 1000);
            } else { 
                toast(d.message || 'Failed', 'error'); 
            }
        }).catch(err => toast(err.message, 'error'));
    });

    // Upload modal
    function openUploadModal(id) {
        document.getElementById('upload_request_id').value = id;
        document.getElementById('image').value = '';
        document.getElementById('description').value = '';
        new bootstrap.Modal(document.getElementById('uploadModal')).show();
    }

    // Handle upload form submission
    document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const id = document.getElementById('upload_request_id').value;
        
        fetch('<?= site_url('maintenance/requests/upload-image') ?>/' + id, {
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            if (d.success) {
                toast('Image uploaded', 'success');
                setTimeout(() => location.reload(), 1000);
            } else { 
                toast(d.message || 'Failed', 'error'); 
            }
        }).catch(err => toast(err.message, 'error'));
    });

    // Cancel confirmation
    function confirmCancel(id) {
        if (!confirm('Cancel this approved/in-progress request?')) return;

        const fd = new FormData(document.getElementById('cancelForm'));
        // Optional: add a quick note
        const note = prompt('Optional note for cancel:', '');
        if (note !== null) fd.set('work_notes', note);

        fetch('<?= site_url('maintenance/requests/update-status') ?>/' + id, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toast('Request cancelled', 'success');
                setTimeout(() => {
                    location.href = '<?= site_url('maintenance/requests?tab=pending') ?>';
                }, 1000);
            } else {
                toast(d.message || 'Failed to cancel', 'error');
            }
        }).catch(err => toast(err.message, 'error'));
    }

    // Tab handling
    document.addEventListener('DOMContentLoaded', function () {
        const tab = new URLSearchParams(location.search).get('tab');
        if (tab === 'pending') {
            new bootstrap.Tab(document.getElementById('pending-tab')).show();
        } else if (tab === 'mywork') {
            new bootstrap.Tab(document.getElementById('mywork-tab')).show();
        }
    });
</script>

<style>
    .notification-alert {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        border: none;
    }
</style>

<?= $this->endSection() ?>