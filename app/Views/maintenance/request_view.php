<?= $this->extend('layouts/maintenance') ?>

<?= $this->section('title') ?>Work Order Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Work Order Details
        </h1>
        <div class="btn-group">
            <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Work Orders
            </a>
            <?php if (!empty($canUpload)): ?>
                <button class="btn btn-outline-secondary btn-sm"
                    onclick="openUploadModal(<?= (int) ($request['id'] ?? 0) ?>)">
                    <i class="fas fa-image"></i> Upload Image
                </button>
            <?php endif; ?>
            <?php if (!empty($canCancel)): ?>
                <button class="btn btn-outline-danger btn-sm" onclick="openCancelModal(<?= (int) ($request['id'] ?? 0) ?>)">
                    <i class="fas fa-ban"></i> Cancel
                </button>
            <?php endif; ?>
            <?php if (!empty($canStart)): ?>
                <button class="btn btn-success btn-sm" onclick="openStartModal(<?= (int) ($request['id'] ?? 0) ?>)">
                    <i class="fas fa-play"></i> Start Work
                </button>
            <?php endif; ?>
            <?php if (!empty($canComplete)): ?>
                <button class="btn btn-warning btn-sm" onclick="openCompleteModal(<?= (int) ($request['id'] ?? 0) ?>)">
                    <i class="fas fa-check"></i> Complete
                </button>
            <?php endif; ?>
            <?php if (!empty($canAccept)): ?>
                <button class="btn btn-primary btn-sm"
                    onclick="openAcceptModal(<?= (int) ($request['id'] ?? 0) ?>, <?= (float) ($request['estimated_cost'] ?? 0) ?>)">
                    <i class="fas fa-check-circle"></i> Accept
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alerts (flash) -->
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

    <div class="row">
        <!-- Left: Details -->
        <div class="col-lg-8">
            <!-- Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-list"></i> Summary
                    </h6>
                    <div>
                        <?php
                        $prio = strtolower($request['priority'] ?? 'normal');
                        $pClass = $prio === 'urgent' ? 'danger' : ($prio === 'high' ? 'warning' : ($prio === 'normal' ? 'info' : 'secondary'));
                        $status = strtolower($request['status'] ?? 'pending');
                        $sClass = $status === 'completed' ? 'success' : ($status === 'in_progress' ? 'warning' : ($status === 'approved' ? 'primary' : 'secondary'));
                        ?>
                        <span class="badge badge-<?= $pClass ?> me-1"><?= ucfirst($prio) ?></span>
                        <span class="badge badge-<?= $sClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="mb-2"><?= esc($request['title'] ?? 'Untitled Request') ?></h5>
                    <p class="text-muted mb-4"><?= nl2br(esc($request['description'] ?? '')) ?></p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-primary d-block"><i class="fas fa-building"></i> Property</strong>
                            <div><?= esc($request['property_name'] ?? '—') ?></div>
                            <div class="text-muted small"><?= esc($request['property_address'] ?? '') ?></div>
                            <?php if (!empty($request['unit_name'])): ?>
                                <div class="text-muted small mt-1"><i class="fas fa-door-open"></i> Unit:
                                    <?= esc($request['unit_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-primary d-block"><i class="fas fa-money-bill-wave"></i> Cost</strong>
                            <div>Estimated:
                                <span class="fw-semibold">
                                    <?= isset($request['estimated_cost']) && $request['estimated_cost'] !== null ? number_format((float) $request['estimated_cost'], 2) . ' SAR' : '—' ?>
                                </span>
                            </div>
                            <div>Approved/Agreed:
                                <span class="fw-semibold">
                                    <?= isset($request['approved_cost']) && $request['approved_cost'] !== null
                                        ? number_format((float) $request['approved_cost'], 2) . ' SAR'
                                        : (($status === 'approved' || $status === 'in_progress' || $status === 'completed') ? '— (not set)' : '—') ?>
                                </span>
                            </div>
                            <?php if ($status === 'completed'): ?>
                                <div>Final Cost:
                                    <span class="fw-semibold">
                                        <?= isset($request['actual_cost']) && $request['actual_cost'] !== null
                                            ? number_format((float) $request['actual_cost'], 2) . ' SAR'
                                            : '—' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <strong class="text-primary d-block"><i class="fas fa-calendar-plus"></i> Requested</strong>
                            <div class="text-muted">
                                <?= !empty($request['requested_date']) ? date('M d, Y', strtotime($request['requested_date'])) : '—' ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong class="text-primary d-block"><i class="fas fa-thumbs-up"></i> Approved</strong>
                            <div class="text-muted">
                                <?= !empty($request['approved_date']) ? date('M d, Y', strtotime($request['approved_date'])) : '—' ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong class="text-primary d-block"><i class="fas fa-play-circle"></i> Start
                                (Assigned)</strong>
                            <div class="text-muted">
                                <?= !empty($request['assigned_date']) ? date('M d, Y', strtotime($request['assigned_date'])) : '—' ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong class="text-primary d-block"><i class="fas fa-flag-checkered"></i>
                                Completed</strong>
                            <div class="text-muted">
                                <?= !empty($request['completed_date']) ? date('M d, Y', strtotime($request['completed_date'])) : '—' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-images"></i> Images
                    </h6>
                    <?php if (!empty($canUpload)): ?>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="openUploadModal(<?= (int) ($request['id'] ?? 0) ?>)">
                            <i class="fas fa-upload"></i> Add Image
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($images)): ?>
                        <div class="row">
                            <?php foreach ($images as $img): ?>
                                <div class="col-6 col-md-4 col-lg-3 mb-3">
                                    <a href="<?= base_url(esc($img['image_path'])) ?>" target="_blank" class="d-block">
                                        <div class="ratio ratio-1x1 border rounded overflow-hidden bg-light">
                                            <img src="<?= base_url(esc($img['image_path'])) ?>" alt="Image"
                                                style="object-fit:cover;width:100%;height:100%">
                                        </div>
                                    </a>
                                    <div class="mt-1 small text-muted">
                                        <?= ucfirst(esc($img['image_type'] ?? '')) ?>
                                        <?php if (!empty($img['description'])): ?>
                                            – <?= esc($img['description']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="far fa-image fa-2x mb-2"></i>
                            <div>No images yet</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Actions & Notes -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if (!empty($canAccept)): ?>
                        <button class="btn btn-primary"
                            onclick="openAcceptModal(<?= (int) ($request['id'] ?? 0) ?>, <?= (float) ($request['estimated_cost'] ?? 0) ?>)">
                            <i class="fas fa-check-circle"></i> Accept Request
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($canStart)): ?>
                        <button class="btn btn-success" onclick="openStartModal(<?= (int) ($request['id'] ?? 0) ?>)">
                            <i class="fas fa-play"></i> Start Work
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($canComplete)): ?>
                        <button class="btn btn-warning" onclick="openCompleteModal(<?= (int) ($request['id'] ?? 0) ?>)">
                            <i class="fas fa-check"></i> Complete (upload images)
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($canUpload)): ?>
                        <button class="btn btn-outline-secondary"
                            onclick="openUploadModal(<?= (int) ($request['id'] ?? 0) ?>)">
                            <i class="fas fa-image"></i> Upload Image
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($canCancel)): ?>
                        <button class="btn btn-outline-danger"
                            onclick="openCancelModal(<?= (int) ($request['id'] ?? 0) ?>)">
                            <i class="fas fa-ban"></i> Cancel (Approved only)
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($request['work_notes'])): ?>
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note"></i> Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(esc($request['work_notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ACCEPT MODAL -->
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Accept Request</h5>
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
                        id="approved_cost" required>
                    <small class="text-muted">Required. If an estimate exists, cost must be ≤ estimate + 150
                        SAR.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="acceptSubmitBtn"><i class="fas fa-check"></i> Accept</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- START MODAL -->
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="startSubmitBtn"><i class="fas fa-play"></i> Start</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- COMPLETE MODAL -->
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

                <div class="alert alert-warning">
                    <i class="fas fa-camera"></i>
                    Please upload <strong>one or more completion images</strong> to finish this request.
                </div>

                <div class="mb-3">
                    <label class="form-label">Completion Images *</label>
                    <input type="file" class="form-control" name="completion_images[]" id="completion_images"
                        accept=".jpg,.jpeg,.png" multiple required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Completion Notes *</label>
                    <textarea class="form-control" name="completion_notes" id="completion_notes" rows="3" required
                        placeholder="Describe the work completed..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-warning" id="completeSubmitBtn"><i class="fas fa-check"></i> Mark
                    Complete</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- UPLOAD IMAGE MODAL -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-image"></i> Upload Image</h5>
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-info" id="uploadSubmitBtn"><i class="fas fa-upload"></i> Upload</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- CANCEL MODAL -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban"></i> Cancel Approved Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('', ['id' => 'cancelForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="cancel_request_id" name="request_id">
                <div class="alert alert-warning small">
                    <i class="fas fa-info-circle"></i>
                    This will set the status to <strong>Cancelled</strong>.
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea class="form-control" name="work_notes" id="cancel_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger" id="cancelSubmitBtn"><i class="fas fa-ban"></i> Cancel Request</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    function toast(msg, type = 'info') {
        const cls = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const div = document.createElement('div');
        div.className = `alert ${cls} alert-dismissible fade show notification-alert position-fixed`;
        div.style.top = '20px'; div.style.right = '20px'; div.style.zIndex = '9999'; div.style.minWidth = '300px';
        div.innerHTML = `<i class="fas ${icon}"></i> ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div); setTimeout(() => div.remove(), 4000);
    }

    // Accept
    function openAcceptModal(id, estimated) {
        document.getElementById('accept_request_id').value = id;
        const hint = (estimated && Number(estimated) > 0)
            ? `Landlord estimate: <strong>${estimated.toFixed ? estimated.toFixed(2) : estimated}</strong> SAR. Your approved cost must be ≤ estimate + 150.`
            : `No landlord estimate. You may set any approved cost.`;
        document.getElementById('accept_hint').innerHTML = hint;
        document.getElementById('approved_cost').value = '';
        new bootstrap.Modal(document.getElementById('acceptModal')).show();
    }
    document.getElementById('acceptSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const fd = new FormData(document.getElementById('acceptForm'));
        const id = document.getElementById('accept_request_id').value;
        fetch('<?= site_url('maintenance/requests/accept') ?>/' + id, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) { toast('Request accepted', 'success'); location.reload(); }
            else { toast(d.message || 'Failed', 'error'); }
        }).catch(err => toast(err.message, 'error'));
    });

    // Start
    function openStartModal(id) {
        document.getElementById('start_request_id').value = id;
        document.getElementById('duration_days').value = '';
        document.getElementById('work_notes').value = '';
        new bootstrap.Modal(document.getElementById('startModal')).show();
    }
    document.getElementById('startSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const fd = new FormData(document.getElementById('startForm'));
        const id = document.getElementById('start_request_id').value;
        fetch('<?= site_url('maintenance/requests/start') ?>/' + id, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) { toast('Work started', 'success'); location.reload(); }
            else { toast(d.message || 'Failed', 'error'); }
        }).catch(err => toast(err.message, 'error'));
    });

    // Complete (requires images)
    function openCompleteModal(id) {
        document.getElementById('complete_request_id').value = id;
        document.getElementById('completion_notes').value = '';
        document.getElementById('completion_images').value = '';
        new bootstrap.Modal(document.getElementById('completeModal')).show();
    }
    document.getElementById('completeSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const notes = document.getElementById('completion_notes').value.trim();
        const files = document.getElementById('completion_images').files;
        if (!notes) { toast('Completion notes are required', 'error'); return; }
        if (!files || files.length === 0) { toast('Please upload at least one completion image', 'error'); return; }
        const fd = new FormData(document.getElementById('completeForm'));
        const id = document.getElementById('complete_request_id').value;
        fetch('<?= site_url('maintenance/requests/complete') ?>/' + id, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) { toast('Request completed', 'success'); location.reload(); }
            else { toast(d.message || 'Failed', 'error'); }
        }).catch(err => toast(err.message, 'error'));
    });

    // Upload single image
    function openUploadModal(id) {
        document.getElementById('upload_request_id').value = id;
        document.getElementById('image').value = '';
        document.getElementById('description').value = '';
        new bootstrap.Modal(document.getElementById('uploadModal')).show();
    }
    document.getElementById('uploadSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const fd = new FormData(document.getElementById('uploadForm'));
        const id = document.getElementById('upload_request_id').value;
        fetch('<?= site_url('maintenance/requests/upload-image') ?>/' + id, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toast('Image uploaded', 'success');
                bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                location.reload();
            } else { toast(d.message || 'Failed', 'error'); }
        }).catch(err => toast(err.message, 'error'));
    });

    // Cancel (approved only)
    function openCancelModal(id) {
        document.getElementById('cancel_request_id').value = id;
        document.getElementById('cancel_notes').value = '';
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
    document.getElementById('cancelSubmitBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const fd = new FormData(document.getElementById('cancelForm'));
        fd.append('status', 'cancelled');
        const id = document.getElementById('cancel_request_id').value;
        fetch('<?= site_url('maintenance/requests/update-status') ?>/' + id, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                toast('Request cancelled', 'success');
                location.href = '<?= site_url('maintenance/requests?tab=pending') ?>';
            } else { toast(d.message || 'Failed', 'error'); }
        }).catch(err => toast(err.message, 'error'));
    });
</script>

<!--cancel script -->
<script>
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

    .ratio {
        position: relative;
        width: 100%;
    }

    .ratio:before {
        display: block;
        content: "";
        padding-top: 100%;
    }

    .ratio>img,
    .ratio>iframe,
    .ratio>div {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }
</style>

<?= $this->endSection() ?>