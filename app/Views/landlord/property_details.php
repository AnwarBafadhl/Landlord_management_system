<?= $this->extend('layouts/landlord') ?>

<?= $this->section('title') ?>Property Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> <?= esc($property['property_name']) ?>
        </h1>
        <div>
            <a href="<?= site_url('landlord/properties') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Properties
            </a>
            <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Property
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Property Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Property Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
    <td><strong>Property Name:</strong></td>
    <td class="value"><?= esc($property['property_name']) ?></td>
</tr>
<tr>
    <td><strong>Address:</strong></td>
    <td class="value"><?= esc($property['address']) ?></td>
</tr>
<tr>
    <td><strong>Property Type:</strong></td>
    <td>
        <span class="badge badge-secondary" style="color:#000; background-color:#fff;">
            <?= ucfirst($property['property_type']) ?>
        </span>
    </td>
</tr>
<tr>
    <td><strong>Number of Units:</strong></td>
    <td class="value"><?= $property['number_of_units'] ?> units</td>
</tr>
<tr>
    <td><strong>Status:</strong></td>
    <td>
        <span class="badge badge-<?= $property['status'] === 'vacant' ? 'warning' : ($property['status'] === 'occupied' ? 'success' : 'info') ?>">
            <?= ucfirst($property['status']) ?>
        </span>
    </td>
</tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Total Landlords:</strong></td>
                                    <td><?= $property['number_of_landlords'] ?> landlords</td>
                                </tr>
                                <tr>
                                    <td><strong>Management Company:</strong></td>
                                    <td>
                                        <?php if (!empty($property['management_company'])): ?>
                                            <?= esc($property['management_company']) ?>
                                            <br><small class="text-muted"><?= $property['management_percentage'] ?>% fee</small>
                                        <?php else: ?>
                                            <span class="text-muted">Self-managed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Your Ownership:</strong></td>
                                    <td><span class="badge badge-info badge-lg"><?= $your_ownership ?>%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?= date('M d, Y', strtotime($property['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td><?= date('M d, Y', strtotime($property['updated_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Units -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-door-open"></i> Property Units
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($units)): ?>
                        <div class="row">
                            <?php foreach ($units as $unit): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-left-info">
                                        <div class="card-body py-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Unit
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?= esc($unit['unit_name']) ?>
                                            </div>
                                            <div class="text-xs text-muted">
                                                Added: <?= date('M d, Y', strtotime($unit['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No units defined for this property.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Property Expenses -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-dollar-sign"></i> Property Expenses
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($expenses)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Expense Name</th>
                                        <th>Amount</th>
                                        <th>Your Share (<?= $your_ownership ?>%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenses as $expense): ?>
                                        <tr>
                                            <td><?= esc($expense['expense_name']) ?></td>
                                            <td>$<?= number_format($expense['expense_amount'], 2) ?></td>
                                            <td>$<?= number_format($expense['expense_amount'] * $your_ownership / 100, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th>Total</th>
                                        <th>$<?= number_format($total_expenses, 2) ?></th>
                                        <th>$<?= number_format($total_expenses * $your_ownership / 100, 2) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No expenses recorded for this property.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ownership Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Property Ownership
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($owners)): ?>
                        <?php foreach ($owners as $owner): ?>
                            <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                <div class="mr-3">
                                    <div class="icon-circle bg-info">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">
                                        <?php if (!empty($owner['first_name']) && !empty($owner['last_name'])): ?>
                                            <?= esc($owner['first_name'] . ' ' . $owner['last_name']) ?>
                                        <?php else: ?>
                                            <?= esc($owner['landlord_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($owner['email'])): ?>
                                        <div class="text-xs text-muted">
                                            <i class="fas fa-envelope"></i> <?= esc($owner['email']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($owner['username'])): ?>
                                        <div class="text-xs text-muted">
                                            <i class="fas fa-user"></i> <?= esc($owner['username']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-2">
                                        <span class="badge badge-info badge-lg">
                                            <?= $owner['ownership_percentage'] ?>% ownership
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No ownership information available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 font-weight-bold text-primary"><?= count($units) ?></div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Units</div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 font-weight-bold text-success"><?= count($owners) ?></div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Owners</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 font-weight-bold text-warning">$<?= number_format($total_expenses, 0) ?></div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Expenses</div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 font-weight-bold text-info">$<?= number_format($total_expenses * $your_ownership / 100, 0) ?></div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Your Share</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}
.property-info td.value {
    font-size: 0.9rem; /* match badge text size */
    font-weight: 500; /* match badge weight */
    color: #000; /* visible on white */
    font-family: inherit;
}

/* Optional: ensure table spacing is nice */
.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}
</style>

<?= $this->endSection() ?>