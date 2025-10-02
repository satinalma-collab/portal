<?php
// Include core files
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/database.php';

// Generate CSRF token for forms
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?>PortalCore PHP</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <i class="fas fa-shield-alt"></i> PortalCore
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav ms-auto">
                <?php if (is_logged_in()): ?>
                    <?php
                        $data = get_data();
                        $menus = $data['menus'] ?? [];
                        usort($menus, fn($a, $b) => $a['order'] <=> $b['order']);
                    ?>
                    <?php foreach ($menus as $menu): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e($menu['path']) ?>"><?= e($menu['title']) ?></a>
                        </li>
                    <?php endforeach; ?>

                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/index.php"><i class="fas fa-cogs"></i> Admin Panel</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= e($_SESSION['flash_message']['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash_message']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>