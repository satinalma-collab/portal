<?php
$page_title = 'Application Management';
require_once __DIR__ . '/../_header.php';

// --- Authorization Check ---
require_admin();

// --- Data Fetching ---
$data = get_data();
$applications = $data['applications'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Application Management</h1>
    <a href="app_form.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Application</a>
</div>

<p>Manage the applications that are displayed on the user dashboard.</p>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Icon</th>
                <th>Name</th>
                <th>Description</th>
                <th>Path</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="6" class="text-center">No applications found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= e($app['id']) ?></td>
                        <td><i class="fas <?= e($app['icon'] ?? 'fa-cube') ?>"></i></td>
                        <td><?= e($app['name']) ?></td>
                        <td><?= e($app['description']) ?></td>
                        <td><code><?= e($app['path']) ?></code></td>
                        <td class="text-end">
                            <a href="app_form.php?id=<?= e($app['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="app_delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this application? This will also remove permissions from users.');">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <input type="hidden" name="app_id" value="<?= e($app['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>