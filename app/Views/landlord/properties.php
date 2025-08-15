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
            <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">Add Property</a>
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
                                <?php 
                                $occupied = 0;
                                if (isset($properties)) {
                                    foreach ($properties as $prop) {
                                        if (($prop['status'] ?? '') === 'occupied') $occupied++;
                                    }
                                }
                                echo $occupied;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                <?php 
                                $vacant = 0;
                                if (isset($properties)) {
                                    foreach ($properties as $prop) {
                                        if (($prop['status'] ?? '') === 'vacant') $vacant++;
                                    }
                                }
                                echo $vacant;
                                ?>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Units
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $totalUnits = 0;
                                if (isset($properties)) {
                                    foreach ($properties as $prop) {
                                        $totalUnits += ($prop['number_of_units'] ?? 1);
                                    }
                                }
                                echo $totalUnits;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-closed fa-2x text-gray-300"></i>
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
            <span class="badge badge-primary"><?= isset($properties) ? count($properties) : 0 ?> properties</span>
        </div>
        <div class="card-body">
            <?php if (!empty($properties)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Property Details</th>
                                <th>Type</th>
                                <th>Units</th>
                                <th>Management</th>
                                <th>Your Ownership</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?= esc($property['property_name']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> <?= esc($property['address']) ?>
                                            </small>
                                            <?php if (!empty($property['created_at'])): ?>
                                                <br>
                                                <small class="text-info">
                                                    Added: <?= date('M d, Y', strtotime($property['created_at'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $propertyType = $property['property_type'] ?? '';
                                        $displayType = '';
                                        
                                        switch ($propertyType) {
                                            case 'rest_house':
                                                $displayType = 'Rest House';
                                                break;
                                            case 'chalet':
                                                $displayType = 'Chalet';
                                                break;
                                            case 'other':
                                                $displayType = 'Other';
                                                break;
                                            default:
                                                $displayType = 'Not Set';
                                        }
                                        ?>
                                        <span class="badge badge-secondary" style="background: #6c757d !important; color: white !important; padding: 0.5rem 0.75rem; font-size: 0.875rem; display: inline-block;">
                                            <?= $displayType ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= $property['number_of_units'] ?? 1 ?></strong>
                                        <small class="text-muted">units</small>
                                    </td>
                                    <td>
                                        <?php if (!empty($property['management_company'])): ?>
                                            <div>
                                                <strong><?= esc($property['management_company']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= $property['management_percentage'] ?? 0 ?>% fee</small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Self-managed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            <?= $property['ownership_percentage'] ?? 100 ?>%
                                        </span>
                                        <?php if (($property['number_of_landlords'] ?? 1) > 1): ?>
                                            <br>
                                            <small class="text-muted">
                                                Shared with <?= ($property['number_of_landlords'] - 1) ?> others
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= ($property['status'] ?? 'vacant') === 'vacant' ? 'warning' : (($property['status'] ?? '') === 'occupied' ? 'success' : 'info') ?>">
                                            <?= ucfirst($property['status'] ?? 'Vacant') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewPropertyDetails(<?= $property['id'] ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="<?= site_url('landlord/properties/edit/' . $property['id']) ?>" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="showUnitsModal(<?= $property['id'] ?>)" 
                                                    title="View Units">
                                                <i class="fas fa-door-open"></i>
                                            </button>
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
                    <p class="text-muted">Start by adding your first property to get started.</p>
                    <a href="<?= site_url('landlord/request-property') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Property Details Modal -->
<div class="modal fade" id="propertyDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Property Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="propertyDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Units Modal -->
<div class="modal fade" id="unitsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Property Units</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="unitsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewPropertyDetails(propertyId) {
    // Redirect to the property details page
    window.location.href = `<?= site_url('landlord/properties/view') ?>/${propertyId}`;
}

function showUnitsModal(propertyId) {
    // Show loading in modal
    document.getElementById('unitsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading units...</p>
        </div>
    `;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('unitsModal')).show();
    
    // Fetch units data
    fetch(`<?= site_url('landlord/properties/units') ?>/${propertyId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = `<h5 class="mb-3">${data.property.property_name}</h5>`;
            
            if (data.units && data.units.length > 0) {
                html += '<div class="row">';
                data.units.forEach(unit => {
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card border-left-info">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Unit</div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">${unit.unit_name}</div>
                                    <div class="text-xs text-muted">Added: ${new Date(unit.created_at).toLocaleDateString()}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html += '<p class="text-muted">No units defined for this property.</p>';
            }
            
            document.getElementById('unitsContent').innerHTML = html;
        } else {
            document.getElementById('unitsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error: ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('unitsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading units: ${error.message}
            </div>
        `;
    });
}
</script>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    font-size: 0.85rem;
}

.btn-group .btn {
    margin-right: 0;
}

/* Force badge styles */
.badge {
    display: inline-block !important;
    padding: 0.5rem 0.75rem !important;
    font-size: 0.875rem !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    color: #fff !important;
    text-align: center !important;
    white-space: nowrap !important;
    vertical-align: baseline !important;
    border-radius: 0.375rem !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: #fff !important;
}

/* Debug styles */
.debug-info {
    background: yellow !important;
    color: black !important;
    font-weight: bold !important;
    padding: 2px 5px !important;
    border: 1px solid red !important;
}
</style>

<?= $this->endSection() ?>