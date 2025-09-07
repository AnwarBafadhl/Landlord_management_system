<?= $this->extend('layouts/maintenance') ?>

<?= $this->section('title') ?>My Schedule<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Prepare a simple calendar for the current month
$ym = $current_month ?? date('Y-m');
$first = $ym . '-01';
$startDow = (int) date('w', strtotime($first)); // 0 Sun .. 6 Sat
$daysIn = (int) date('t', strtotime($first));
// Map availability by date (Y-m-d)
$availMap = [];
foreach (($availability ?? []) as $a) {
    $availMap[$a['date']] = $a;
}
// Group requests by date (Y-m-d from assigned_date)
$reqMap = [];
foreach (($monthly_requests ?? []) as $r) {
    if (!empty($r['assigned_date'])) {
        $k = date('Y-m-d', strtotime($r['assigned_date']));
        $reqMap[$k] = $reqMap[$k] ?? [];
        $reqMap[$k][] = $r;
    }
}
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt"></i> My Schedule
        </h1>
        <div class="d-flex gap-2">
            <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list"></i> Work Orders
            </a>
            <button class="btn btn-info btn-sm" onclick="openAvailabilityModalFor('<?= date('Y-m-d') ?>')">
                <i class="fas fa-user-clock"></i> Set Today
            </button>
        </div>
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

    <!-- Month header -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= date('F Y', strtotime($ym . '-01')) ?>
            </h6>
            <span class="text-muted small">
                <i class="fas fa-circle text-success"></i> Available &nbsp;
                <i class="fas fa-circle text-secondary"></i> Not Available &nbsp;
                <i class="fas fa-briefcase"></i> Assigned Jobs
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table calendar table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $day = 1;
                        for ($row = 0; $row < 6; $row++):
                            echo '<tr style="height: 120px;">';
                            for ($col = 0; $col < 7; $col++):
                                if ($row === 0 && $col < $startDow) {
                                    echo '<td class="bg-light"></td>';
                                } elseif ($day > $daysIn) {
                                    echo '<td class="bg-light"></td>';
                                } else {
                                    $dateStr = sprintf('%s-%02d', $ym, $day);
                                    $avail = $availMap[$dateStr] ?? null;
                                    $requests = $reqMap[$dateStr] ?? [];
                                    $isToday = ($dateStr === date('Y-m-d'));
                                    $availBadge = $avail
                                        ? ($avail['is_available'] ? '<span class="badge bg-success">Available</span>'
                                            : '<span class="badge bg-secondary">Not Avail.</span>')
                                        : '<span class="badge bg-light text-muted">—</span>';
                                    echo '<td>';
                                    echo '<div class="d-flex justify-content-between align-items-center">';
                                    echo '<div class="fw-bold ' . ($isToday ? 'text-primary' : '') . '">' . $day . '</div>';
                                    echo '<button class="btn btn-xxs btn-outline-primary" onclick="openAvailabilityModalFor(\'' . $dateStr . '\')"><i class="fas fa-pen"></i></button>';
                                    echo '</div>';
                                    echo '<div class="mt-1">' . $availBadge . '</div>';
                                    if (!empty($avail['notes'])) {
                                        echo '<div class="text-muted small">' . esc($avail['notes']) . '</div>';
                                    }
                                    if ($requests) {
                                        echo '<div class="mt-2 small">';
                                        foreach ($requests as $r) {
                                            $pClass = $r['priority'] === 'urgent' ? 'danger' : ($r['priority'] === 'high' ? 'warning' : 'info');
                                            echo '<div class="mb-1 p-1 border rounded">';
                                            echo '<span class="badge bg-' . $pClass . ' me-1">' . ucfirst($r['priority']) . '</span>';
                                            echo '<a href="' . site_url('maintenance/requests/view/' . $r['id']) . '" class="text-decoration-none">' . $r['title'] . '</a>';
                                            echo '<div class="text-muted">' . esc($r['property_name'] ?? '') . '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</td>';
                                    $day++;
                                }
                            endfor;
                            echo '</tr>';
                            if ($day > $daysIn)
                                break;
                        endfor;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Lists below calendar -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-briefcase"></i> This Month's
                        Assignments</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($monthly_requests)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Request</th>
                                        <th>Property</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_requests as $r): ?>
                                        <?php
                                        $sClass = $r['status'] === 'completed' ? 'success' : ($r['status'] === 'in_progress' ? 'warning' : 'secondary');
                                        ?>
                                        <tr>
                                            <td><?= !empty($r['assigned_date']) ? date('M d, Y', strtotime($r['assigned_date'])) : '—' ?>
                                            </td>
                                            <td class="fw-semibold"><?= esc($r['title']) ?></td>
                                            <td class="text-muted small"><?= esc($r['property_name'] ?? '') ?></td>
                                            <td><span
                                                    class="badge bg-<?= $sClass ?>"><?= ucfirst(str_replace('_', ' ', $r['status'])) ?></span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('maintenance/requests/view/' . $r['id']) ?>"
                                                        class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (in_array($r['status'], ['approved', 'in_progress'])): ?>
                                                        <button class="btn btn-outline-warning"
                                                            onclick="openCompleteModal(<?= (int) $r['id'] ?>)">
                                                            <i class="fas fa-check"></i>
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
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <div>No assignments this month</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Quick Availability Editor -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-clock"></i> Quick Availability
                    </h6>
                    <span class="text-muted small"><?= date('F Y', strtotime($ym . '-01')) ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php for ($d = 1; $d <= $daysIn; $d++):
                            $dateStr = sprintf('%s-%02d', $ym, $d);
                            $a = $availMap[$dateStr] ?? null;
                            $is = $a && ((int) $a['is_available'] === 1);
                            ?>
                            <div class="col-4 col-md-3 col-lg-2">
                                <button class="btn btn-sm w-100 <?= $is ? 'btn-success' : 'btn-outline-secondary' ?>"
                                    onclick="openAvailabilityModalFor('<?= $dateStr ?>')">
                                    <?= $d ?>
                                </button>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Availability Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-clock"></i> Update Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('maintenance/schedule/update', ['id' => 'availabilityForm']) ?>
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" name="date" id="availability_date">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="availability_switch" name="is_available"
                        value="1">
                    <label class="form-check-label" for="availability_switch">Available on this date</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" id="availability_notes" rows="2" maxlength="255"
                        placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info" id="availabilitySubmitBtn"><i class="fas fa-save"></i> Save</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Complete Modal for quick completion -->
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
                    <label class="form-label">Actual Cost</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="actual_cost" id="actual_cost"
                        placeholder="0.00">
                </div>

                <div class="mb-3">
                    <label class="form-label">Materials Used</label>
                    <textarea class="form-control" name="materials_used" id="materials_used" rows="2"
                        placeholder="List materials..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Completion Notes *</label>
                    <textarea class="form-control" name="completion_notes" id="completion_notes" rows="3" required
                        placeholder="Describe the work completed..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Completion Images (optional)</label>
                    <input type="file" class="form-control" name="completion_images[]" id="completion_images"
                        accept=".jpg,.jpeg,.png" multiple>
                    <small class="text-muted">Upload images if available to show completed work.</small>
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

    // Availability modal
    function openAvailabilityModalFor(dateStr) {
        document.getElementById('availability_date').value = dateStr;
        // Reset form
        const switchEl = document.getElementById('availability_switch');
        switchEl.checked = false;
        document.getElementById('availability_notes').value = '';

        // Try to load current availability from server or page data if available
        // This is a simplified approach - in a full implementation, you might
        // fetch the current status via AJAX or embed it in the page

        new bootstrap.Modal(document.getElementById('availabilityModal')).show();
    }

    // Handle availability form submission
    document.getElementById('availabilityForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        
        // Ensure we send 0 if checkbox is unchecked
        if (!document.getElementById('availability_switch').checked) {
            fd.set('is_available', '0');
        }
        
        fetch('<?= site_url('maintenance/schedule/update') ?>', {
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            bootstrap.Modal.getInstance(document.getElementById('availabilityModal')).hide();
            if (d.success) { 
                toast('Availability saved', 'success'); 
                setTimeout(() => location.reload(), 1000);
            } else { 
                toast(d.message || 'Failed', 'error'); 
            }
        }).catch(err => toast(err.message, 'error'));
    });

    // Complete modal
    function openCompleteModal(id) {
        document.getElementById('complete_request_id').value = id;
        document.getElementById('actual_cost').value = '';
        document.getElementById('materials_used').value = '';
        document.getElementById('completion_notes').value = '';
        document.getElementById('completion_images').value = '';
        new bootstrap.Modal(document.getElementById('completeModal')).show();
    }

    // Handle complete form submission
    document.getElementById('completeForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const notes = document.getElementById('completion_notes').value.trim();
        
        if (!notes) { 
            toast('Completion notes are required', 'error'); 
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
</script>

<style>
    .calendar td {
        vertical-align: top;
    }

    .btn-xxs {
        padding: .15rem .35rem;
        font-size: .75rem;
        line-height: 1;
    }

    .notification-alert {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        border: none;
    }
</style>

<?= $this->endSection() ?>