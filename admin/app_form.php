<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/auth.php';

// --- Authorization & Initialization ---
require_admin();

$data = get_data();
$app = null;
$is_edit_mode = false;

if (isset($_GET['id'])) {
    $app_id = (int)$_GET['id'];
    $app_index = array_search($app_id, array_column($data['applications'], 'id'));
    if ($app_index !== false) {
        $app = $data['applications'][$app_index];
        $is_edit_mode = true;
    } else {
        $_SESSION['flash_message'] = ['message' => 'Application not found.', 'type' => 'danger'];
        header('Location: apps.php');
        exit();
    }
}

$page_title = $is_edit_mode ? 'Edit Application' : 'Add New Application';

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = $_POST['name'];
    $description = $_POST['description'];
    $path = $_POST['path'];
    $icon = $_POST['icon'];

    if ($is_edit_mode) {
        // --- Update existing application ---
        $data['applications'][$app_index]['name'] = $name;
        $data['applications'][$app_index]['description'] = $description;
        $data['applications'][$app_index]['path'] = $path;
        $data['applications'][$app_index]['icon'] = $icon;
    } else {
        // --- Add new application ---
        $new_id = empty($data['applications']) ? 101 : max(array_column($data['applications'], 'id')) + 1;
        $new_app = [
            'id' => $new_id,
            'name' => $name,
            'description' => $description,
            'path' => $path,
            'icon' => $icon
        ];
        $data['applications'][] = $new_app;
    }

    if (save_data($data)) {
        $_SESSION['flash_message'] = ['message' => 'Application saved successfully.', 'type' => 'success'];
    } else {
        $_SESSION['flash_message'] = ['message' => 'Failed to save application data.', 'type' => 'danger'];
    }

    header('Location: apps.php');
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
        <label for="name" class="form-label">Application Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= e($app['name'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?= e($app['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label for="path" class="form-label">Path</label>
        <input type="text" class="form-control" id="path" name="path" value="<?= e($app['path'] ?? '') ?>" placeholder="e.g., crm.php" required>
        <div class="form-text">The PHP file to link to (e.g., <code>reports.php</code>).</div>
    </div>

    <div class="mb-3">
        <label for="icon" class="form-label">FontAwesome Icon Class</label>
        <input type="text" class="form-control" id="icon" name="icon" value="<?= e($app['icon'] ?? '') ?>" placeholder="e.g., fa-tasks">
        <div class="form-text">Enter the full FontAwesome icon class (e.g., <code>fa-user-tie</code>, <code>fa-chart-pie</code>).</div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $is_edit_mode ? 'Update' : 'Create' ?> Application</button>
    <a href="apps.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once __DIR__ . '/../_footer.php'; ?>