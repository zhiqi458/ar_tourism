<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    redirect('/index.php');
}

$attraction = null;
$gallery = [];
try {
    $pdo = requireDatabase();
    $stmt = $pdo->prepare('SELECT a.*, d.name AS destination_name, c.name AS category_name FROM attractions a LEFT JOIN destinations d ON d.id = a.destination_id LEFT JOIN categories c ON c.id = a.category_id WHERE a.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $attraction = $stmt->fetch();
    if ($attraction) {
        $galleryStmt = $pdo->prepare('SELECT * FROM attraction_images WHERE attraction_id = :id ORDER BY id ASC');
        $galleryStmt->execute(['id' => $id]);
        $gallery = $galleryStmt->fetchAll();
    }
} catch (Throwable $e) {
    $attraction = null;
}

if (!$attraction) {
    $_SESSION['flash_error'] = 'The attraction could not be found.';
    redirect('/index.php');
}

$pageTitle = $attraction['name'] . ' | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <img src="<?= e(imageUrl($attraction['main_image'] ?? '')) ?>" class="img-fluid rounded-4 shadow" alt="<?= e($attraction['name']) ?>" style="max-height: 500px; width: 100%; object-fit: cover;">
            <?php if (!empty($gallery)): ?>
                <div class="row g-3 mt-3">
                    <?php foreach ($gallery as $image): ?>
                        <div class="col-md-4">
                            <img src="<?= e(imageUrl($image['image_path'] ?? '')) ?>" class="img-fluid rounded-3 shadow-sm" alt="<?= e($image['title'] ?? $attraction['name']) ?>" style="height: 170px; width: 100%; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="card p-4">
                <span class="badge bg-primary-subtle text-primary mb-3"><?= e($attraction['category_name'] ?? 'Attraction') ?></span>
                <h1 class="h2 mb-3"><?= e($attraction['name']) ?></h1>
                <p class="text-muted"><?= e($attraction['short_description']) ?></p>
                <ul class="list-unstyled mb-4">
                    <li><strong>Destination:</strong> <?= e($attraction['destination_name'] ?? '—') ?></li>
                    <li><strong>Opening hours:</strong> <?= e($attraction['opening_hours'] ?? '—') ?></li>
                    <li><strong>Entry information:</strong> <?= e($attraction['entry_information'] ?? '—') ?></li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if (!empty($attraction['youtube_url'])): ?>
                        <a class="btn btn-primary" href="<?= e($attraction['youtube_url']) ?>" target="_blank" rel="noreferrer">Watch video</a>
                    <?php endif; ?>
                    <?php if (!empty($attraction['google_maps_url'])): ?>
                        <a class="btn btn-outline-primary" href="<?= e($attraction['google_maps_url']) ?>" target="_blank" rel="noreferrer">View map</a>
                    <?php endif; ?>
                    <?php if (!empty($attraction['website_url'])): ?>
                        <a class="btn btn-outline-secondary" href="<?= e($attraction['website_url']) ?>" target="_blank" rel="noreferrer">Official site</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-5 p-4">
        <h2 class="h4">About this attraction</h2>
        <p class="mb-0"><?= nl2br(e($attraction['full_description'] ?? '')) ?></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
