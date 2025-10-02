<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/_header.php';

// --- Authentication Check ---
// This ensures only logged-in users can access the dashboard.
require_login();

// --- Data Fetching ---
$data = get_data();
$all_applications = $data['applications'] ?? [];
$user_permissions = $_SESSION['user_role'] === 'admin' ? ['all'] : ($data['users'][array_search($_SESSION['user_id'], array_column($data['users'], 'id'))]['permissions'] ?? []);


// --- Application Filtering Logic ---
$visible_apps = [];
if (in_array('all', $user_permissions)) {
    $visible_apps = $all_applications;
} else {
    foreach ($all_applications as $app) {
        if (in_array($app['id'], $user_permissions)) {
            $visible_apps[] = $app;
        }
    }
}
?>

<div class="container">
    <div class="px-4 py-5 my-5 text-center">
        <h1 class="display-5 fw-bold">Welcome, <?= e($_SESSION['username']) ?>!</h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">Here are the applications you have access to. Click on any card to launch the application.</p>
        </div>
    </div>

    <?php if (empty($visible_apps)): ?>
        <div class="alert alert-info text-center">
            You currently do not have permission to access any applications. Please contact an administrator.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($visible_apps as $app): ?>
                <div class="col">
                    <a href="<?= e($app['path']) ?>" class="card h-100 text-decoration-none text-dark shadow-sm app-card">
                        <div class="card-body text-center">
                            <div class="app-icon mb-3">
                                <i class="fas <?= e($app['icon'] ?? 'fa-cube') ?> fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?= e($app['name']) ?></h5>
                            <p class="card-text"><?= e($app['description']) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.app-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.app-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.app-icon {
    font-size: 2.5rem;
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>