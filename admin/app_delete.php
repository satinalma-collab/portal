<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Validation ---
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: apps.php');
    exit();
}

verify_csrf_token();

$app_id_to_delete = (int)($_POST['app_id'] ?? 0);

// --- Deletion Logic ---
$data = get_data();
$app_index = array_search($app_id_to_delete, array_column($data['applications'], 'id'));

if ($app_index !== false) {
    // Remove the application from the main list
    array_splice($data['applications'], $app_index, 1);

    // Also, remove this permission from all users who have it
    foreach ($data['users'] as &$user) {
        if (!in_array('all', $user['permissions'])) {
            $perm_index = array_search($app_id_to_delete, $user['permissions']);
            if ($perm_index !== false) {
                array_splice($user['permissions'], $perm_index, 1);
            }
        }
    }
    unset($user); // Unset reference

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'Application and all associated permissions have been deleted.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to delete application. Could not save data.', 'type' => 'danger'];
    }
} else {
    $_SESSION['flash_message'] = ['message' => 'Application not found.', 'type' => 'danger'];
}

header('Location: apps.php');
exit();

?>