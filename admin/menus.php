<?php
$page_title = 'Menu Management';
require_once __DIR__ . '/../_header.php';

// --- Authorization Check ---
require_admin();

// --- Data Fetching ---
$data = get_data();
$menus = $data['menus'] ?? [];
usort($menus, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Menu Management</h1>
    <a href="menu_form.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Menu Item</a>
</div>

<p>Manage the items in the main navigation bar.</p>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Path</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($menus)): ?>
                <tr>
                    <td colspan="4" class="text-center">No menu items found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($menus as $menu): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= e($menu['order']) ?></span></td>
                        <td><?= e($menu['title']) ?></td>
                        <td><code><?= e($menu['path']) ?></code></td>
                        <td class="text-end">
                            <a href="menu_form.php?id=<?= e($menu['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="menu_delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <input type="hidden" name="menu_id" value="<?= e($menu['id']) ?>">
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