<?php

// Include the base configuration first
require_once __DIR__ . '/config.php';

// Start session on all pages that include this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has the 'admin' role.
 *
 * @return bool True if user is an admin, false otherwise.
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * If the user is not logged in, redirects to the login page and terminates script execution.
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

/**
 * If the user is not an admin, redirects to the main portal page and terminates script execution.
 */
function require_admin() {
    if (!is_admin()) {
        // Redirect to a non-admin page, e.g., the main dashboard
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

/**
 * Generates and stores a CSRF token in the session.
 *
 * @return string The generated token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies the submitted CSRF token. If invalid, terminates script execution.
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Token is invalid or missing, handle the error
        die('CSRF token validation failed.');
    }
}

/**
 * A simple helper to escape HTML for output.
 *
 * @param string|null $string The string to escape.
 * @return string The escaped string.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

?>