<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Property Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> Property Management
        </h1>
        <a href="<?= site_url('admin/properties/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Property
        </a>
    </div>

    <!-- Properties Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Properties List
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($properties)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Rent</th>
                                <th>Landlord</th>
                                <th>Tenant</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $displayedProperties = [];
                            foreach ($properties as $property): 
                                if (in_array($property['id'], $displayedProperties)) continue;
                                $displayedProperties[] = $property['id'];
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($property['property_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($property['address']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= ucfirst($property['property_type']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= $property['bedrooms'] ?>BR / <?= $property['bathrooms'] ?>BA
                                        </small>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($property['base_rent'], 2) ?></strong>
                                        <br>
                                        <small class="text-muted">per month</small>
                                    </td>
                                    <td>
                                        <?php if ($property['first_name']): ?>
                                            <?= esc($property['first_name'] . ' ' . $property['last_name']) ?>
                                            <br>
                                            <small class="text-muted"><?= $property['ownership_percentage'] ?>% ownership</small>
                                        <?php else: ?>
                                            <span class="text-muted">No landlord assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($property['tenant_first_name']): ?>
                                            <?= esc($property['tenant_first_name'] . ' ' . $property['tenant_last_name']) ?>
                                            <br>
                                            <small class="text-muted">
                                                Until <?= date('M Y', strtotime($property['lease_end'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Vacant</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $property['status'] === 'occupied' ? 'success' : ($property['status'] === 'maintenance' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($property['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('admin/properties/view/' . $property['id']) ?>" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('admin/properties/edit/' . $property['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProperty(<?= $property['id'] ?>, '<?= esc($property['property_name']) ?>')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-building fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No properties found.</p>
                    <a href="<?= site_url('admin/properties/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Property
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteProperty(propertyId, propertyName) {
    if (confirm(`Are you sure you want to delete property "${propertyName}"? This action cannot be undone.`)) {
        window.location.href = '<?= site_url('admin/properties/delete') ?>/' + propertyId;
    }
}
</script>

<?= $this->endSection() ?>