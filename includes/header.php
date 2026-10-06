<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}

$user = currentUser();
$siteName = getSetting('site_name', APP_NAME);
$tagline = getSetting('site_tagline', 'Explore destinations with immersive AR stories');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $siteName) ?></title>
    <meta name="description" content="<?= e($tagline) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= appUrl('assets/css/styles.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary d-flex align-items-center gap-2" href="<?= appUrl('index.php') ?>">
                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.1rem;">
                    <i class="fa-solid fa-vr-cardboard"></i>
                </span>
                <span><?= e($siteName) ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('index.php') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('destinations.php') ?>">Destinations</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('attractions.php') ?>">Attractions</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('ar.php') ?>">AR Experience</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('about.php') ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= appUrl('contact.php') ?>">Contact</a></li>
                    <?php if ($user && ($user['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="<?= appUrl('admin/index.php') ?>">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php $flashError = flash('flash_error'); if ($flashError): ?>
        <div class="container mt-3" data-flash>
            <div class="alert alert-danger mb-0" role="alert"><?= e($flashError) ?></div>
        </div>
    <?php endif; ?>

    <?php $flashSuccess = flash('flash_success'); if ($flashSuccess): ?>
        <div class="container mt-3" data-flash>
            <div class="alert alert-success mb-0" role="alert"><?= e($flashSuccess) ?></div>
        </div>
    <?php endif; ?>
