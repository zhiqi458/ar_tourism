<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Attractions | ' . APP_NAME;
$destinationId = isset($_GET['destination']) ? (int) $_GET['destination'] : null;
$search = trim($_GET['search'] ?? '');

$attractions = [];
try {
    $attractions = fetchAttractions(null, $destinationId, $search);
} catch (Throwable $e) {
    $attractions = [];
}

$destinations = [];
try {
    $destinations = fetchDestinations();
} catch (Throwable $e) {
    $destinations = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Attractions</h1>
            <p class="text-muted mb-0">Explore places, stories and experiences shaped by local heritage, culture and nature.</p>
        </div>
        <form class="d-flex gap-2" method="get" action="attractions.php">
            <select class="form-select" name="destination">
                <option value="">All destinations</option>
                <?php foreach ($destinations as $destination): ?>
                    <option value="<?= (int) $destination['id'] ?>" <?= ($destinationId !== null && (int) $destination['id'] === $destinationId) ? 'selected' : '' ?>><?= e($destination['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Search attractions" value="<?= e($search) ?>">
            <button class="btn btn-primary" type="submit">Find</button>
        </form>
    </div>

    <?php if (empty($attractions)): ?>
        <div class="alert alert-info">No attractions are available for the current filter.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($attractions as $attraction): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card attraction-card h-100">
                        <img src="<?= e(imageUrl($attraction['main_image'] ?? '')) ?>" class="card-img-top" alt="<?= e($attraction['name']) ?>" style="height: 220px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary"><?= e($attraction['category_name'] ?? 'Attraction') ?></span>
                                <span class="text-muted small"><?= e($attraction['destination_name'] ?? 'Destination') ?></span>
                            </div>
                            <h2 class="h4"><?= e($attraction['name']) ?></h2>
                            <p class="text-muted"><?= e($attraction['short_description']) ?></p>
                            <a href="<?= appUrl('attraction.php?id=' . (int) $attraction['id']) ?>" class="btn btn-primary">More details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
