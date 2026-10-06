<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Destinations | ' . APP_NAME;
$search = trim($_GET['search'] ?? '');
$selectedDestination = isset($_GET['destination']) ? (int) $_GET['destination'] : null;

$destinations = [];
try {
    $destinations = fetchDestinations(null, $search);
    if ($selectedDestination) {
        $destinations = array_values(array_filter($destinations, fn($item) => (int) $item['id'] === $selectedDestination));
    }
} catch (Throwable $e) {
    $destinations = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Tourism destinations</h1>
            <p class="text-muted mb-0">Discover culturally rich destinations and memorable experiences.</p>
        </div>
        <form class="d-flex gap-2" method="get" action="destinations.php">
            <input type="text" name="search" class="form-control" placeholder="Search destinations" value="<?= e($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <?php if (empty($destinations)): ?>
        <div class="alert alert-info">No destinations matched your search.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($destinations as $destination): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card destination-card h-100">
                        <img src="<?= e(imageUrl($destination['cover_image'] ?? '')) ?>" class="card-img-top" alt="<?= e($destination['name']) ?>" style="height: 240px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-warning text-dark"><?= e($destination['category_name'] ?? 'Destination') ?></span>
                                <span class="text-muted small"><?= e($destination['location'] ?? 'Local area') ?></span>
                            </div>
                            <h2 class="h4"><?= e($destination['name']) ?></h2>
                            <p class="text-muted"><?= e($destination['short_description']) ?></p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a class="btn btn-primary" href="<?= appUrl('attractions.php?destination=' . (int) $destination['id']) ?>">View attractions</a>
                                <a class="btn btn-outline-primary" href="<?= appUrl('ar.php') ?>">AR Explore</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
