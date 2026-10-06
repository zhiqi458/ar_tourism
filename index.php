<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = APP_NAME . ' | Interactive Tourism AR';
try {
    requireDatabase();
    ensureDefaultAdmin();
    createDemoData();
} catch (Throwable $e) {
    $_SESSION['flash_error'] = 'Database is not ready yet. Please import database/database.sql and configure MySQL first.';
}

$stats = [];
try {
    $stats = fetchDashboardStats();
} catch (Throwable $e) {
    $stats = ['destinations' => 0, 'attractions' => 0, 'ar_posters' => 0, 'ar_hotspots' => 0, 'categories' => 0];
}

$destinations = [];
$attractions = [];
try {
    $destinations = fetchDestinations(3);
    $attractions = fetchAttractions(3);
} catch (Throwable $e) {
    $destinations = [];
    $attractions = [];
}

$categories = [];
try {
    $categories = requireDatabase()->query('SELECT * FROM categories WHERE status = "active" ORDER BY name ASC LIMIT 8')->fetchAll();
} catch (Throwable $e) {
    $categories = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3">Immersive travel discovery</span>
                    <h1>Explore destinations through augmented reality.</h1>
                    <p class="lead text-white-50">Turn tourism posters, brochures and landmark images into a digital storytelling experience that helps visitors discover culture, heritage and local attractions.</p>
                    <div class="d-flex gap-3 flex-wrap mt-4">
                        <a href="<?= appUrl('ar.php') ?>" class="btn btn-light btn-lg">Start AR Experience</a>
                        <a href="<?= appUrl('destinations.php') ?>" class="btn btn-outline-light btn-lg">Explore Destinations</a>
                    </div>
                    <form class="hero-search" data-search-form>
                        <div class="input-group input-group-lg shadow">
                            <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" class="form-control border-0" placeholder="Search destination or attraction" data-search-input>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg p-3 p-lg-4">
                        <div class="row align-items-center g-3">
                            <div class="col-6">
                                <div class="qr-code-box text-center">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= rawurlencode(appUrl('ar.php')) ?>" alt="Scan QR to open AR experience" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex flex-column gap-3">
                                    <div class="text-primary fw-bold">SCAN TO START AR EXPERIENCE</div>
                                    <a href="<?= appUrl('ar.php') ?>" class="btn btn-primary">Open AR Page</a>
                                    <div class="text-muted small">Works best on mobile devices with camera access.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center"><div><small class="text-muted">Destinations</small><h3 class="mb-0"><?= e((string) $stats['destinations']) ?></h3></div><i class="fa-solid fa-location-dot fa-2x text-primary"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center"><div><small class="text-muted">Attractions</small><h3 class="mb-0"><?= e((string) $stats['attractions']) ?></h3></div><i class="fa-solid fa-landmark fa-2x text-primary"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center"><div><small class="text-muted">AR Posters</small><h3 class="mb-0"><?= e((string) $stats['ar_posters']) ?></h3></div><i class="fa-solid fa-camera fa-2x text-primary"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center"><div><small class="text-muted">Categories</small><h3 class="mb-0"><?= e((string) $stats['categories']) ?></h3></div><i class="fa-solid fa-tags fa-2x text-primary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Featured destinations</h2>
            <a href="<?= appUrl('destinations.php') ?>" class="btn btn-outline-primary">View all</a>
        </div>
        <div class="row g-4">
            <?php foreach ($destinations as $destination): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card destination-card h-100">
                        <div class="position-relative">
                            <img src="<?= e(imageUrl($destination['cover_image'] ?? '')) ?>" class="card-img-top" alt="<?= e($destination['name']) ?>" style="height: 230px; object-fit: cover;">
                            <span class="badge bg-warning text-dark featured-badge">Featured</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-primary fw-semibold"><?= e($destination['category_name'] ?? 'Tourism') ?></span>
                                <span class="text-muted small"><?= e($destination['location'] ?? 'Local destination') ?></span>
                            </div>
                            <h3 class="h5 mb-2"><?= e($destination['name']) ?></h3>
                            <p class="text-muted"><?= e($destination['short_description']) ?></p>
                            <a href="<?= appUrl('destinations.php?destination=' . (int) $destination['id']) ?>" class="btn btn-primary">Explore</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Popular attractions</h2>
            <a href="<?= appUrl('attractions.php') ?>" class="btn btn-outline-primary">Browse all</a>
        </div>
        <div class="row g-4">
            <?php foreach ($attractions as $attraction): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card attraction-card h-100">
                        <img src="<?= e(imageUrl($attraction['main_image'] ?? '')) ?>" class="card-img-top" alt="<?= e($attraction['name']) ?>" style="height: 220px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-primary"><?= e($attraction['category_name'] ?? 'Attraction') ?></span>
                                <span class="text-muted small"><?= e($attraction['opening_hours'] ?? 'Daily') ?></span>
                            </div>
                            <h3 class="h5"><?= e($attraction['name']) ?></h3>
                            <p class="text-muted"><?= e($attraction['short_description']) ?></p>
                            <a href="<?= appUrl('attraction.php?id=' . (int) $attraction['id']) ?>" class="btn btn-primary">Learn more</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Browse by category</h2>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card h-100 p-3 text-center">
                        <div class="mb-3"><i class="fa-solid fa-earth-americas fa-2x text-primary"></i></div>
                        <h3 class="h6 mb-0"><?= e($category['name']) ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
