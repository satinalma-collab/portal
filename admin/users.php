<?php
$page_title = 'User Management';
require_once __DIR__ . '/../_header.php';

// --- Authorization Check ---
require_admin();

// --- Data Fetching ---
$data = get_data();
$users = $data['users'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>User Management</h1>
    <a href="user_form.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New User</a>
</div>

<p>From this page, you can add, edit, and delete users.</p>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Permissions</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="text-center">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['id']) ?></td>
                        <td><i class="fas fa-user"></i> <?= e($user['username']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (in_array('all', $user['permissions'])): ?>
                                <span class="badge bg-info">All Applications</span>
                            <?php else: ?>
                                <?= e(implode(', ', $user['permissions'])) ?: '<span class="text-muted">None</span>' ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="user_form.php?id=<?= e($user['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($user['id'] !== $_SESSION['user_id']): // Admin cannot delete themselves ?>
                                <form action="user_delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>