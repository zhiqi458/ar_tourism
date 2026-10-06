<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../includes/config.php';
}
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    redirect('/admin/login.php');
}

$user = currentUser();
$pageTitle = $pageTitle ?? APP_NAME . ' Admin';
$siteName = getSetting('site_name', APP_NAME);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= appUrl('assets/css/styles.css') ?>">
</head>
<body class="admin-shell">
    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 admin-sidebar p-3">
                <div class="sidebar-brand mb-4"><?= e($siteName) ?></div>
                <nav class="d-flex flex-column gap-2">
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], '/admin/index.php') ? 'active' : '' ?>" href="<?= appUrl('admin/index.php') ?>"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'categories') ? 'active' : '' ?>" href="<?= appUrl('admin/categories/index.php') ?>"><i class="fa-solid fa-tags me-2"></i>Categories</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'destinations') ? 'active' : '' ?>" href="<?= appUrl('admin/destinations/index.php') ?>"><i class="fa-solid fa-location-dot me-2"></i>Destinations</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'attractions') ? 'active' : '' ?>" href="<?= appUrl('admin/attractions/index.php') ?>"><i class="fa-solid fa-landmark me-2"></i>Attractions</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'ar-posters') ? 'active' : '' ?>" href="<?= appUrl('admin/ar-posters/index.php') ?>"><i class="fa-solid fa-camera me-2"></i>AR Posters</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'users') ? 'active' : '' ?>" href="<?= appUrl('admin/users/index.php') ?>"><i class="fa-solid fa-users me-2"></i>Users</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'logs') ? 'active' : '' ?>" href="<?= appUrl('admin/logs/index.php') ?>"><i class="fa-solid fa-clock-rotate-left me-2"></i>Logs</a>
                    <a class="sidebar-link <?= str_contains($_SERVER['PHP_SELF'], 'settings') ? 'active' : '' ?>" href="<?= appUrl('admin/settings/index.php') ?>"><i class="fa-solid fa-gear me-2"></i>Settings</a>
                    <a class="sidebar-link" href="<?= appUrl('admin/logout.php') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
                </nav>
            </aside>
            <main class="col-lg-10 admin-main">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0"><?= e($pageTitle) ?></h1>
                    </div>
                    <div class="text-muted">Welcome, <?= e($_SESSION['user_name'] ?? 'Admin') ?></div>
                </div>
                <?php if (($flashError = flash('flash_error'))): ?>
                    <div class="alert alert-danger" role="alert"><?= e($flashError) ?></div>
                <?php endif; ?>
                <?php if (($flashSuccess = flash('flash_success'))): ?>
                    <div class="alert alert-success" role="alert"><?= e($flashSuccess) ?></div>
                <?php endif; ?>
