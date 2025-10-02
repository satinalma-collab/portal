<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../_header.php';

// --- Authorization Check ---
// Only admins can access this page.
require_admin();
?>

<div class="container">
    <h1 class="mt-4">Admin Dashboard</h1>
    <p class="lead">Welcome to the PortalCore Admin Panel, <?= e($_SESSION['username']) ?>.</p>
    <hr>
    <p>From this panel, you can manage all aspects of the portal. Use the links below or the navigation bar to get started.</p>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users-cog"></i> User Management</h5>
                    <p class="card-text">Add, edit, and delete users. You can also manage their application permissions here.</p>
                    <a href="users.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-cubes"></i> Application Management</h5>
                    <p class="card-text">Create, update, and remove applications from the user dashboard.</p>
                    <a href="apps.php" class="btn btn-primary">Manage Applications</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-bars"></i> Menu Management</h5>
                    <p class="card-text">Control the items that appear in the main navigation bar for all users.</p>
                    <a href="menus.php" class="btn btn-primary">Manage Menus</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../_footer.php'; ?>