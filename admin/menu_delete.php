<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Validation ---
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menus.php');
    exit();
}

verify_csrf_token();

$menu_id_to_delete = (int)($_POST['menu_id'] ?? 0);

// --- Deletion Logic ---
$data = get_data();
$menu_index = array_search($menu_id_to_delete, array_column($data['menus'], 'id'));

if ($menu_index !== false) {
    // Remove the menu item from the array
    array_splice($data['menus'], $menu_index, 1);

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'Menu item has been successfully deleted.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to delete menu item. Could not save data.', 'type' => 'danger'];
    }
} else {
    $_SESSION['flash_message'] = ['message' => 'Menu item not found.', 'type' => 'danger'];
}

header('Location: menus.php');
exit();

?>