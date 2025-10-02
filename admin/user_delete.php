<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Validation ---
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only allow POST requests for deletion
    header('Location: users.php');
    exit();
}

verify_csrf_token();

$user_id_to_delete = (int)($_POST['user_id'] ?? 0);

// --- Deletion Logic ---

// Admins cannot delete their own account
if ($user_id_to_delete === $_SESSION['user_id']) {
    $_SESSION['flash_message'] = ['message' => 'Error: You cannot delete your own account.', 'type' => 'danger'];
    header('Location: users.php');
    exit();
}

$data = get_data();
$user_index = array_search($user_id_to_delete, array_column($data['users'], 'id'));

if ($user_index !== false) {
    // Remove the user from the array
    array_splice($data['users'], $user_index, 1);

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'User has been successfully deleted.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to delete user. Could not save data.', 'type' => 'danger'];
    }
} else {
    $_SESSION['flash_message'] = ['message' => 'User not found.', 'type' => 'danger'];
}

header('Location: users.php');
exit();

?>