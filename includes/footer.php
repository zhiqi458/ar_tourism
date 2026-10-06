<?php
$siteName = getSetting('site_name', APP_NAME);
$contactEmail = getSetting('contact_email', 'hello@artourism.local');
?>
    <footer class="footer mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="text-white mb-3"><?= e($siteName) ?></h5>
                    <p class="text-light-emphasis mb-0">A modern tourism platform connecting destination discovery with immersive augmented reality experiences.</p>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white">Explore</h6>
                    <ul class="list-unstyled text-light-emphasis mb-0">
                        <li><a class="text-light-emphasis" href="<?= appUrl('destinations.php') ?>">Destinations</a></li>
                        <li><a class="text-light-emphasis" href="<?= appUrl('attractions.php') ?>">Attractions</a></li>
                        <li><a class="text-light-emphasis" href="<?= appUrl('ar.php') ?>">AR Experience</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white">Contact</h6>
                    <ul class="list-unstyled text-light-emphasis mb-0">
                        <li><?= e($contactEmail) ?></li>
                        <li>Open daily</li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary mt-4 pt-3 text-light-emphasis small">
                © <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= appUrl('assets/js/app.js') ?>"></script>
</body>
</html>
