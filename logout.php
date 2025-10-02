<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all of the session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Start a new session just to store a flash message
session_start();
$_SESSION['flash_message'] = [
    'message' => 'You have been successfully logged out.',
    'type' => 'success'
];

// Redirect to login page
header('Location: ' . BASE_URL . '/login.php');
exit();

?>