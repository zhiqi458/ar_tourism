<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-6">
            <h1>About AR Tourism Explorer</h1>
            <p class="lead text-muted">A tourism technology platform designed to enhance discovery through interactive digital overlays and cultural storytelling.</p>
            <p>Visitors can access a destination page, explore attraction details, watch videos and trigger AR experiences using their camera and a tourism poster or landmark image. The platform is designed to work on modern mobile browsers and present content in a clean, accessible format.</p>
        </div>
        <div class="col-lg-6">
            <img src="<?= appUrl('assets/images/placeholder-landmark.svg') ?>" class="img-fluid rounded-4 shadow" alt="AR tourism landscape">
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
