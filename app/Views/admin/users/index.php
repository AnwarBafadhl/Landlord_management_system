<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> User Management
        </h1>
        <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter Users
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= site_url('admin/users') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="admin" <?= $current_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="landlord" <?= $current_role === 'landlord' ? 'selected' : '' ?>>Landlord</option>
                            <option value="maintenance" <?= $current_role === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email, or username..." 
                               value="<?= esc($search_term) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Users List
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="role-icon me-3">
                                                <?php
                                                $icons = [
                                                    'admin' => 'fas fa-user-shield text-primary',
                                                    'landlord' => 'fas fa-building text-success',
                                                    'maintenance' => 'fas fa-tools text-warning'
                                                ];
                                                ?>
                                                <i class="<?= $icons[$user['role']] ?? 'fas fa-user' ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">@<?= esc($user['username']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= esc($user['email']) ?>
                                        <?php if ($user['phone']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone"></i> <?= esc($user['phone']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'landlord') ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('g:i A', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('admin/users/edit/' . $user['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button class="btn btn-sm btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $user['id'] ?>, <?= $user['is_active'] ? 'false' : 'true' ?>)"
                                                    title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?>"></i>
                                            </button>
                                            
                                            <?php if ($user['id'] != session()->get('user_id')): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteUser(<?= $user['id'] ?>, '<?= esc($user['first_name'] . ' ' . $user['last_name']) ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
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
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No users found matching your criteria.</p>
                    <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add First User
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleStatus(userId, newStatus) {
    const action = newStatus === 'true' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        window.location.href = '<?= site_url('admin/users/toggle-status') ?>/' + userId;
    }
}

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        window.location.href = '<?= site_url('admin/users/delete') ?>/' + userId;
    }
}

// DataTables initialization (if you want to add sorting/pagination)
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('usersTable');
    if (table && typeof $ !== 'undefined' && $.fn.DataTable) {
        $(table).DataTable({
            "pageLength": 25,
            "order": [[4, "desc"]], // Sort by created date
            "columnDefs": [
                { "orderable": false, "targets": [5] } // Disable sorting on actions column
            ]
        });
    }
});
</script>

<?= $this->endSection() ?>