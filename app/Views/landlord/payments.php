<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Payment Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar"></i> Payment Management
        </h1>
        <div>
            <div class="btn-group" role="group">
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-danger" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_payments'] ?? 0 ?>
                            </div>
                            <div class="text-xs">
                                All time
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['current_month_payments'] ?? 0 ?>
                            </div>
                            <div class="text-xs">
                                Payments received
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
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
                                Average Payment
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['average_payment'] ?? 0, 0) ?>
                            </div>
                            <div class="text-xs">
                                Per transaction
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
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
                                Total Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($stats['total_income'] ?? 0, 0) ?>
                            </div>
                            <div class="text-xs">
                                This Month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Payment Records</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= current_url() ?>" id="filterForm">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="payment_type" class="form-label">Payment Type</label>
                        <select class="form-control" id="payment_type" name="payment_type">
                            <option value="">All Types</option>
                            <option value="rent" <?= ($filters['payment_type'] ?? '') === 'rent' ? 'selected' : '' ?>>Rent</option>
                            <option value="maintenance" <?= ($filters['payment_type'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="property" class="form-label">Property</label>
                        <select class="form-control" id="property" name="property">
                            <option value="">All Properties</option>
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>" <?= ($filters['property'] ?? '') == $property['id'] ? 'selected' : '' ?>>
                                        <?= esc($property['property_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $filters['date_from'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $filters['date_to'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="tenant" class="form-label">Tenant</label>
                        <select class="form-control" id="tenant" name="tenant">
                            <option value="">All Tenants</option>
                            <?php if (!empty($tenants)): ?>
                                <?php foreach ($tenants as $tenant): ?>
                                    <option value="<?= $tenant['id'] ?>" <?= ($filters['tenant'] ?? '') == $tenant['id'] ? 'selected' : '' ?>>
                                        <?= esc($tenant['first_name'] . ' ' . $tenant['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Records Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Payment Records</h6>
            <div>
                <span class="badge badge-primary"><?= count($payment_receipts ?? []) ?> payments</span>
                <button class="btn btn-sm btn-outline-primary ms-2" onclick="bulkActions()">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($payment_receipts)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0" id="paymentsTable">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Receipt</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_receipts as $receipt): ?>
                                <tr id="receipt-<?= $receipt['id'] ?>">
                                    <td>
                                        <input type="checkbox" class="receipt-checkbox" value="<?= $receipt['id'] ?>">
                                    </td>
                                    <td>
                                        <?php if (!empty($receipt['receipt_file'])): ?>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewReceipt('<?= $receipt['receipt_file'] ?>')" 
                                                    title="View Receipt">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-receipt"></i> Receipt
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($receipt['property_name'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($receipt['property_address'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px;">
                                                    <span class="text-white fw-bold">
                                                        <?= strtoupper(substr($receipt['tenant_first_name'] ?? 'T', 0, 1) . substr($receipt['tenant_last_name'] ?? 'T', 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= esc(($receipt['tenant_first_name'] ?? '') . ' ' . ($receipt['tenant_last_name'] ?? '')) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= esc($receipt['tenant_email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">$<?= number_format($receipt['amount'] ?? 0, 2) ?></strong>
                                        <?php if (!empty($receipt['ownership_percentage']) && $receipt['ownership_percentage'] != 100): ?>
                                            <br>
                                            <small class="text-muted">
                                                Your share: $<?= number_format(($receipt['amount'] ?? 0) * (($receipt['ownership_percentage'] ?? 100) / 100), 2) ?>
                                                (<?= $receipt['ownership_percentage'] ?>%)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($receipt['payment_date']) ? date('M j, Y', strtotime($receipt['payment_date'])) : 'N/A' ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php
                                            if (!empty($receipt['payment_date'])) {
                                                $datetime = strtotime($receipt['payment_date']);
                                                $now = time();
                                                $diff = $now - $datetime;
                                                $days = floor($diff / (60 * 60 * 24));
                                                if ($days == 0) {
                                                    echo 'Today';
                                                } elseif ($days == 1) {
                                                    echo 'Yesterday';
                                                } elseif ($days < 30) {
                                                    echo $days . ' days ago';
                                                } else {
                                                    echo date('M Y', $datetime);
                                                }
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= ucfirst($receipt['payment_type'] ?? 'rent') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Confirmed
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            Processed: <?= !empty($receipt['created_at']) ? date('M j, Y', strtotime($receipt['created_at'])) : 'N/A' ?>
                                        </small>
                                        <?php if (!empty($receipt['notes'])): ?>
                                            <br>
                                            <small class="text-muted" title="<?= esc($receipt['notes']) ?>">
                                                <i class="fas fa-comment"></i> Note
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="viewPaymentDetails(<?= $receipt['id'] ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (!empty($receipt['receipt_file'])): ?>
                                                <a href="<?= site_url('landlord/download-receipt/' . $receipt['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Download Receipt">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="addNote(<?= $receipt['id'] ?>)" 
                                                    title="Add Note">
                                                <i class="fas fa-comment-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (if needed) -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing <?= count($payment_receipts) ?> of <?= $total_receipts ?? count($payment_receipts) ?> payments
                    </div>
                    <div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Pagination links would go here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Payment Records Found</h5>
                    <p class="text-muted">No payment records match your current filters.</p>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary" onclick="clearFilters()">
                            <i class="fas fa-refresh"></i> Clear Filters
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Viewer Modal -->
<div class="modal fade" id="receiptViewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="receiptViewerContent">
                <!-- Receipt image/PDF will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note to Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_note" class="form-label">Note</label>
                        <textarea class="form-control" id="payment_note" name="note" rows="3" 
                                  placeholder="Add a note about this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="bulk_action" class="form-label">Select Action</label>
                    <select class="form-control" id="bulk_action" name="action">
                        <option value="">Select Action</option>
                        <option value="export_selected">Export Selected</option>
                        <option value="add_notes">Add Notes to Selected</option>
                        <option value="download_receipts">Download Receipts</option>
                    </select>
                </div>
                <div class="mb-3" id="bulk_notes_section" style="display: none;">
                    <label for="bulk_notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="bulk_notes" name="notes" rows="3" 
                              placeholder="Add notes for selected payments..."></textarea>
                </div>
                <div class="alert alert-info">
                    <span id="selected_count">0</span> payments selected
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">
                    <i class="fas fa-play"></i> Execute Action
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPaymentId = null;
let selectedReceipts = [];

function viewReceipt(filename) {
    const fileExtension = filename.split('.').pop().toLowerCase();
    const receiptUrl = `<?= base_url('uploads/receipts') ?>/${filename}`;
    
    let content = '';
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
        content = `<img src="${receiptUrl}" class="img-fluid" alt="Payment Receipt">`;
    } else if (fileExtension === 'pdf') {
        content = `<embed src="${receiptUrl}" type="application/pdf" width="100%" height="500px">`;
    } else {
        content = `<p>Cannot preview this file type. <a href="${receiptUrl}" target="_blank">Click here to download</a></p>`;
    }
    
    document.getElementById('receiptViewerContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('receiptViewerModal')).show();
}

function viewPaymentDetails(paymentId) {
    currentPaymentId = paymentId;
    
    // Show loading
    document.getElementById('paymentDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading payment details...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
    
    // Fetch payment details
    fetch(`<?= site_url('landlord/payment-details') ?>/${paymentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('paymentDetailsContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('paymentDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading payment details: ${error.message}
            </div>
        `;
    });
}

function addNote(paymentId) {
    currentPaymentId = paymentId;
    document.getElementById('payment_note').value = '';
    new bootstrap.Modal(document.getElementById('addNoteModal')).show();
}

function exportToExcel() {
    // Check if there are any payments to export
    if (<?= count($payment_receipts ?? []) ?> === 0) {
        showAlert('warning', 'No payments available to export. Please add some payment records first.');
        return;
    }
    
    // Show loading message
    showAlert('info', 'Preparing Excel export...');
    
    try {
        // Fallback: Create CSV export using JavaScript
        exportToCSV();
    } catch (error) {
        showAlert('danger', 'Export failed. Please try again or contact support.');
        console.error('Export error:', error);
    }
}

function exportToPDF() {
    // Check if there are any payments to export
    if (<?= count($payment_receipts ?? []) ?> === 0) {
        showAlert('warning', 'No payments available to export. Please add some payment records first.');
        return;
    }
    
    // Show loading message
    showAlert('info', 'Preparing PDF export...');
    
    try {
        // Fallback: Print the current page
        window.print();
    } catch (error) {
        showAlert('danger', 'Export failed. Please try again or contact support.');
        console.error('Export error:', error);
    }
}

function exportToCSV() {
    const table = document.getElementById('paymentsTable');
    if (!table) {
        showAlert('warning', 'No payment data available to export.');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    // Get headers (skip checkbox column)
    const headerRow = rows[0];
    const headers = [];
    for (let i = 1; i < headerRow.cells.length; i++) { // Skip first checkbox column
        headers.push(headerRow.cells[i].textContent.trim());
    }
    csv.push(headers.join(','));
    
    // Get data rows (skip header)
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const rowData = [];
        for (let j = 1; j < row.cells.length; j++) { // Skip first checkbox column
            let cellText = row.cells[j].textContent.trim();
            // Clean up the text and escape commas
            cellText = cellText.replace(/\s+/g, ' ').replace(/"/g, '""');
            if (cellText.includes(',')) {
                cellText = `"${cellText}"`;
            }
            rowData.push(cellText);
        }
        csv.push(rowData.join(','));
    }
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `payments_export_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showAlert('success', 'CSV export completed successfully!');
}

function clearFilters() {
    document.getElementById('payment_type').value = '';
    document.getElementById('property').value = '';
    document.getElementById('tenant').value = '';
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
    document.getElementById('filterForm').submit();
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.receipt-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedReceipts();
}

function updateSelectedReceipts() {
    selectedReceipts = Array.from(document.querySelectorAll('.receipt-checkbox:checked'))
                           .map(checkbox => parseInt(checkbox.value));
    
    document.getElementById('selected_count').textContent = selectedReceipts.length;
}

function bulkActions() {
    updateSelectedReceipts();
    
    if (selectedReceipts.length === 0) {
        showAlert('warning', 'Please select at least one payment');
        return;
    }
    
    new bootstrap.Modal(document.getElementById('bulkActionsModal')).show();
}

function executeBulkAction() {
    const action = document.getElementById('bulk_action').value;
    const notes = document.getElementById('bulk_notes').value;
    
    if (!action) {
        showAlert('warning', 'Please select an action');
        return;
    }
    
    if (selectedReceipts.length === 0) {
        showAlert('warning', 'Please select at least one payment');
        return;
    }
    
    const data = {
        action: action,
        receipts: selectedReceipts,
        notes: notes
    };
    
    fetch(`<?= site_url('landlord/bulk-payment-action') ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
            showAlert('success', data.message);
            if (action !== 'export_selected') {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Form submission for adding notes
document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(`<?= site_url('landlord/add-payment-note') ?>/${currentPaymentId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Auto-submit on filter change
document.querySelectorAll('#payment_type, #property, #tenant, #date_from, #date_to').forEach(element => {
    element.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Update selected receipts when checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('receipt-checkbox')) {
        updateSelectedReceipts();
    }
});

// Show notes section for bulk add notes action
document.getElementById('bulk_action').addEventListener('change', function() {
    const notesSection = document.getElementById('bulk_notes_section');
    if (this.value === 'add_notes') {
        notesSection.style.display = 'block';
        document.getElementById('bulk_notes').required = true;
    } else {
        notesSection.style.display = 'none';
        document.getElementById('bulk_notes').required = false;
    }
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'times-circle')}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}
</script>

<style>
.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    margin-right: 0;
}

.progress-sm {
    height: 8px;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.avatar-sm {
    flex-shrink: 0;
}

.alert.position-fixed {
    min-width: 300px;
}

.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.hover-shadow {
    transition: box-shadow 0.15s ease-in-out;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
    
    .d-flex.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .avatar-sm {
        margin-bottom: 0.5rem;
    }
    
    .d-sm-flex {
        flex-direction: column !important;
        gap: 1rem;
    }
    
    .btn-group[role="group"] {
        flex-direction: row;
        gap: 0.5rem;
    }
}

/* Enhanced card styling */
.card.border-left-primary,
.card.border-left-success,
.card.border-left-warning,
.card.border-left-info {
    transition: transform 0.2s ease-in-out;
}

.card.border-left-primary:hover,
.card.border-left-success:hover,
.card.border-left-warning:hover,
.card.border-left-info:hover {
    transform: translateY(-2px);
}

/* Table row hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.075);
    transform: scale(1.002);
    transition: all 0.2s ease;
}

/* Modal enhancements */
.modal-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.modal-title {
    color: #5a5c69;
    font-weight: 600;
}

/* Button enhancements */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Badge enhancements */
.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Receipt viewer enhancements */
#receiptViewerContent img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

/* Filter form enhancements */
.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Statistics cards animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card.border-left-primary,
.card.border-left-success,
.card.border-left-warning,
.card.border-left-info {
    animation: fadeInUp 0.6s ease-out;
}

.card.border-left-primary {
    animation-delay: 0.1s;
}

.card.border-left-success {
    animation-delay: 0.2s;
}

.card.border-left-warning {
    animation-delay: 0.3s;
}

.card.border-left-info {
    animation-delay: 0.4s;
}

/* Loading spinner */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Empty state styling */
.text-center.py-5 {
    padding: 3rem 1rem !important;
}

.text-center.py-5 i {
    opacity: 0.5;
}

/* Print styles for PDF export */
@media print {
    .btn, .modal, .alert, .pagination, .card-header .btn-group {
        display: none !important;
    }
    
    .container-fluid {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
    
    .card-header {
        background-color: #f8f9fc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .table {
        font-size: 12px !important;
    }
    
    .table th {
        background-color: #f8f9fc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge {
        border: 1px solid #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .text-success {
        color: #28a745 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .text-primary {
        color: #007bff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .text-warning {
        color: #ffc107 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .h3 {
        margin-bottom: 1rem !important;
    }
    
    /* Hide checkbox column when printing */
    .table th:first-child,
    .table td:first-child {
        display: none !important;
    }
    
    /* Ensure page breaks */
    .row {
        break-inside: avoid;
    }
    
    /* Print header */
    @page {
        margin: 1in;
        @top-center {
            content: "Payment Management Report - " date();
        }
    }
}

/* Responsive improvements */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        border: none;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}
</style>

<?= $this->endSection() ?>