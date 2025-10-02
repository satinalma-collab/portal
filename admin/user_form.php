<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Initialization ---
require_admin();

$data = get_data();
$applications = $data['applications'] ?? [];
$user = null;
$is_edit_mode = false;

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $user_index = array_search($user_id, array_column($data['users'], 'id'));
    if ($user_index !== false) {
        $user = $data['users'][$user_index];
        $is_edit_mode = true;
    } else {
        $_SESSION['flash_message'] = ['message' => 'User not found.', 'type' => 'danger'];
        header('Location: users.php');
        exit();
    }
}

$page_title = $is_edit_mode ? 'Edit User' : 'Add New User';

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $username = $_POST['username'];
    $role = $_POST['role'];
    $permissions = $_POST['permissions'] ?? [];

    // Validate username uniqueness if it has changed or in add mode
    if (!$is_edit_mode || ($is_edit_mode && strcasecmp($user['username'], $username) !== 0)) {
        foreach ($data['users'] as $existing_user) {
            if (strcasecmp($existing_user['username'], $username) === 0) {
                $_SESSION['flash_message'] = ['message' => "Username '{$username}' already exists.", 'type' => 'danger'];
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
    }

    if ($is_edit_mode) {
        // --- Update existing user ---
        $data['users'][$user_index]['username'] = $username;
        $data['users'][$user_index]['role'] = $role;

        if (in_array('all', $permissions)) {
            $data['users'][$user_index]['permissions'] = ['all'];
        } else {
            $data['users'][$user_index]['permissions'] = array_map('intval', $permissions);
        }

        if (!empty($_POST['password'])) {
            $data['users'][$user_index]['passwordHash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
    } else {
        // --- Add new user ---
        if (empty($_POST['password'])) {
            $_SESSION['flash_message'] = ['message' => 'Password is required for new users.', 'type' => 'danger'];
            header('Location: user_form.php');
            exit();
        }
        $new_id = empty($data['users']) ? 1 : max(array_column($data['users'], 'id')) + 1;
        $new_user = [
            'id' => $new_id,
            'username' => $username,
            'passwordHash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => $role,
            'permissions' => in_array('all', $permissions) ? ['all'] : array_map('intval', $permissions)
        ];
        $data['users'][] = $new_user;
    }

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'User saved successfully.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to save user data.', 'type' => 'danger'];
    }

    header('Location: users.php');
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
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= e($user['username'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" <?= !$is_edit_mode ? 'required' : '' ?>>
        <?php if ($is_edit_mode): ?>
            <div class="form-text">Leave blank to keep the current password.</div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select" id="role" name="role">
            <option value="user" <?= (isset($user) && $user['role'] === 'user') ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= (isset($user) && $user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
        </select>
    </div>

    <div class="mb-3">
        <h5>Application Permissions</h5>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permissions[]" value="all" id="perm-all"
                <?= (isset($user) && in_array('all', $user['permissions'])) ? 'checked' : '' ?>>
            <label class="form-check-label" for="perm-all">
                <strong>Grant All Permissions</strong>
            </label>
        </div>
        <hr>
        <div class="row">
            <?php foreach ($applications as $app): ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= e($app['id']) ?>" id="app-<?= e($app['id']) ?>"
                            <?= (isset($user) && (in_array('all', $user['permissions']) || in_array($app['id'], $user['permissions']))) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="app-<?= e($app['id']) ?>">
                            <?= e($app['name']) ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $is_edit_mode ? 'Update' : 'Create' ?> User</button>
    <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once __DIR__ . '/../_footer.php'; ?>