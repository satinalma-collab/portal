<?php
$page_title = 'Login';
require_once __DIR__ . '/_header.php';

// If user is already logged in, redirect to homepage
if (is_logged_in()) {
    header('Location: /index.php');
    exit();
}

// Function to set a flash message
function set_flash_message($message, $type = 'danger') {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Token Verification ---
    verify_csrf_token();

    // --- Form Processing ---
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        set_flash_message('Username and password are required.');
        header('Location: login.php');
        exit();
    }

    $data = get_data();
    $user = null;
    foreach ($data['users'] as $u) {
        if (strcasecmp($u['username'], $username) === 0) {
            $user = $u;
            break;
        }
    }

    if ($user && password_verify($password, $user['passwordHash'])) {
        // --- Login Successful ---
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        set_flash_message('Welcome back, ' . e($user['username']) . '!', 'success');

        // Check for default admin password
        if ($user['role'] === 'admin' && password_verify('admin', $user['passwordHash'])) {
             set_flash_message('Login successful. IMPORTANT: Please change your default admin password immediately!', 'warning');
        }

        header('Location: /index.php');
        exit();
    } else {
        // --- Login Failed ---
        set_flash_message('Invalid username or password.');
        header('Location: login.php');
        exit();
    }
}
?>

<div class="d-flex justify-content-center align-items-center">
    <div class="form-signin w-100">
        <form method="POST" action="login.php">
            <h1 class="h3 mb-3 fw-normal text-center">Please sign in</h1>

            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
        </form>
    </div>
</div>


<?php require_once __DIR__ . '/_footer.php'; ?>