<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Initialization ---
require_admin();

$data = get_data();
$menu_item = null;
$is_edit_mode = false;

if (isset($_GET['id'])) {
    $menu_id = (int)$_GET['id'];
    $menu_index = array_search($menu_id, array_column($data['menus'], 'id'));
    if ($menu_index !== false) {
        $menu_item = $data['menus'][$menu_index];
        $is_edit_mode = true;
    } else {
        $_SESSION['flash_message'] = ['message' => 'Menu item not found.', 'type' => 'danger'];
        header('Location: menus.php');
        exit();
    }
}

$page_title = $is_edit_mode ? 'Edit Menu Item' : 'Add New Menu Item';

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $title = $_POST['title'];
    $path = $_POST['path'];
    $order = (int)$_POST['order'];

    if ($is_edit_mode) {
        // --- Update existing menu item ---
        $data['menus'][$menu_index]['title'] = $title;
        $data['menus'][$menu_index]['path'] = $path;
        $data['menus'][$menu_index]['order'] = $order;
    } else {
        // --- Add new menu item ---
        $new_id = empty($data['menus']) ? 1 : max(array_column($data['menus'], 'id')) + 1;
        $new_menu = [
            'id' => $new_id,
            'title' => $title,
            'path' => $path,
            'order' => $order
        ];
        $data['menus'][] = $new_menu;
    }

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'Menu item saved successfully.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to save menu item.', 'type' => 'danger'];
    }

    header('Location: menus.php');
    exit();
}


// --- Page Render ---
require_once __DIR__ . '/../_header.php';
?>

<h1><?= e($page_title) ?></h1>
<hr>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="<?= e($menu_item['title'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="path" class="form-label">Path</label>
        <input type="text" class="form-control" id="path" name="path" value="<?= e($menu_item['path'] ?? '') ?>" placeholder="e.g., /support.php" required>
        <div class="form-text">The URL or path for the menu link (e.g., <code>/index.php</code>).</div>
    </div>

    <div class="mb-3">
        <label for="order" class="form-label">Display Order</label>
        <input type="number" class="form-control" id="order" name="order" value="<?= e($menu_item['order'] ?? 10) ?>" required>
        <div class="form-text">A smaller number will appear earlier in the navigation bar.</div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $is_edit_mode ? 'Update' : 'Create' ?> Menu Item</button>
    <a href="menus.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once __DIR__ . '/../_footer.php'; ?>