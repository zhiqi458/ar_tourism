<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    requireDatabase();
    ensureDefaultAdmin();
    createDemoData();
} catch (Throwable $e) {
    $_SESSION['flash_error'] = 'The database is not initialized. Import database/database.sql first.';
    redirect('/admin/login.php');
}

requireAdmin();
$pageTitle = 'Dashboard';
$stats = fetchDashboardStats();
$recent = getRecentActivities(5);
$latestDestinations = [];
$latestPosters = [];
try {
    $latestDestinations = requireDatabase()->query('SELECT * FROM destinations ORDER BY created_at DESC LIMIT 5')->fetchAll();
    $latestPosters = requireDatabase()->query('SELECT * FROM ar_posters ORDER BY created_at DESC LIMIT 5')->fetchAll();
} catch (Throwable $e) {
    $latestDestinations = [];
    $latestPosters = [];
}

require_once __DIR__ . '/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between"><div><small class="text-muted">Destinations</small><h3 class="mb-0"><?= e((string) $stats['destinations']) ?></h3></div><i class="fa-solid fa-location-dot fa-2x text-primary"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between"><div><small class="text-muted">Attractions</small><h3 class="mb-0"><?= e((string) $stats['attractions']) ?></h3></div><i class="fa-solid fa-landmark fa-2x text-primary"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between"><div><small class="text-muted">AR Posters</small><h3 class="mb-0"><?= e((string) $stats['ar_posters']) ?></h3></div><i class="fa-solid fa-camera fa-2x text-primary"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between"><div><small class="text-muted">Categories</small><h3 class="mb-0"><?= e((string) $stats['categories']) ?></h3></div><i class="fa-solid fa-tags fa-2x text-primary"></i></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Recent destinations</h2><a href="<?= appUrl('admin/destinations/index.php') ?>" class="btn btn-sm btn-outline-primary">Manage</a></div>
            <div class="list-group list-group-flush">
                <?php foreach ($latestDestinations as $destination): ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold"><?= e($destination['name']) ?></div>
                            <small class="text-muted"><?= e($destination['location'] ?? 'City location') ?></small>
                        </div>
                        <span class="status-chip <?= e($destination['status']) ?>"><?= e($destination['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Recent AR posters</h2><a href="<?= appUrl('admin/ar-posters/index.php') ?>" class="btn btn-sm btn-outline-primary">Manage</a></div>
            <div class="list-group list-group-flush">
                <?php foreach ($latestPosters as $poster): ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold"><?= e($poster['name']) ?></div>
                            <small class="text-muted"><?= e($poster['target_status']) ?></small>
                        </div>
                        <span class="status-chip <?= strtolower(str_replace('_', '-', $poster['target_status'])) ?>"><?= e(str_replace('_', ' ', strtolower($poster['target_status']))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">System activity</h2>
            <div class="list-group list-group-flush">
                <?php foreach ($recent as $item): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold"><?= e($item['action']) ?></div>
                                <small class="text-muted"><?= e($item['description']) ?></small>
                            </div>
                            <small class="text-muted"><?= e(date('M d, Y', strtotime($item['created_at']))) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3">Quick actions</h2>
            <div class="d-grid gap-2">
                <a href="<?= appUrl('admin/categories/index.php') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-tag me-2"></i>Add category</a>
                <a href="<?= appUrl('admin/destinations/index.php') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-location-dot me-2"></i>Add destination</a>
                <a href="<?= appUrl('admin/attractions/index.php') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-landmark me-2"></i>Add attraction</a>
                <a href="<?= appUrl('admin/ar-posters/index.php') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-camera me-2"></i>Manage posters</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
