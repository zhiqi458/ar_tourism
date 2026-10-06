<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $photo = $_FILES['main_image'] ?? null;
        $imagePath = '';
        if ($photo && $photo['tmp_name']) {
            $imagePath = saveUploadedFile($photo, UPLOAD_DIR . '/gallery', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'], 2 * 1024 * 1024) ?: '';
        }

        $sql = $id > 0
            ? 'UPDATE attractions SET destination_id = :destination_id, category_id = :category_id, name = :name, short_description = :short_description, full_description = :full_description, main_image = CASE WHEN :new_image <> "" THEN :new_image ELSE main_image END, youtube_url = :youtube_url, website_url = :website_url, google_maps_url = :google_maps_url, latitude = :latitude, longitude = :longitude, opening_hours = :opening_hours, entry_information = :entry_information, status = :status, display_order = :display_order, updated_at = NOW() WHERE id = :id'
            : 'INSERT INTO attractions (destination_id, category_id, name, short_description, full_description, main_image, youtube_url, website_url, google_maps_url, latitude, longitude, opening_hours, entry_information, status, display_order, created_at, updated_at) VALUES (:destination_id, :category_id, :name, :short_description, :full_description, :main_image, :youtube_url, :website_url, :google_maps_url, :latitude, :longitude, :opening_hours, :entry_information, :status, :display_order, NOW(), NOW())';
        $params = [
            'destination_id' => $_POST['destination_id'] ?? 0,
            'category_id' => $_POST['category_id'] ?? null,
            'name' => $name,
            'short_description' => trim($_POST['short_description'] ?? ''),
            'full_description' => trim($_POST['full_description'] ?? ''),
            'youtube_url' => trim($_POST['youtube_url'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'google_maps_url' => trim($_POST['google_maps_url'] ?? ''),
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'opening_hours' => trim($_POST['opening_hours'] ?? ''),
            'entry_information' => trim($_POST['entry_information'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'display_order' => (int) ($_POST['display_order'] ?? 0),
        ];
        if ($id > 0) {
            $params['new_image'] = $imagePath;
            $params['id'] = $id;
        } else {
            $params['main_image'] = $imagePath;
        }
        requireDatabase()->prepare($sql)->execute($params);
        $_SESSION['flash_success'] = $id > 0 ? 'Attraction updated successfully.' : 'Attraction saved successfully.';
    }
    redirect('/admin/attractions/index.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    requireDatabase()->prepare('DELETE FROM attractions WHERE id = :id')->execute(['id' => $id]);
    $_SESSION['flash_success'] = 'Attraction deleted.';
    redirect('/admin/attractions/index.php');
}

$attractions = requireDatabase()->query('SELECT a.*, d.name AS destination_name FROM attractions a LEFT JOIN destinations d ON d.id = a.destination_id ORDER BY a.created_at DESC')->fetchAll();
$destinations = requireDatabase()->query('SELECT * FROM destinations WHERE status = "active" ORDER BY name ASC')->fetchAll();
$categories = requireDatabase()->query('SELECT * FROM categories WHERE status = "active" ORDER BY name ASC')->fetchAll();
$editingAttraction = null;
if (isset($_GET['edit'])) {
    $stmt = requireDatabase()->prepare('SELECT * FROM attractions WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editingAttraction = $stmt->fetch() ?: null;
}
$pageTitle = 'Attractions';
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3"><?= $editingAttraction ? 'Edit attraction' : 'Add attraction' ?></h2>
            <form method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) ($editingAttraction['id'] ?? 0) ?>">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= e($editingAttraction['name'] ?? '') ?>" required></div>
                <div class="row g-2">
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Destination</label><select name="destination_id" class="form-select" required>
                        <?php foreach ($destinations as $destination): ?>
                            <option value="<?= (int) $destination['id'] ?>" <?= (int) ($editingAttraction['destination_id'] ?? 0) === (int) $destination['id'] ? 'selected' : '' ?>><?= e($destination['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Category</label><select name="category_id" class="form-select">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) ($editingAttraction['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div></div>
                </div>
                <div class="mb-3"><label class="form-label">Short description</label><textarea name="short_description" rows="3" class="form-control"><?= e($editingAttraction['short_description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Full description</label><textarea name="full_description" rows="4" class="form-control"><?= e($editingAttraction['full_description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Main image</label><input type="file" name="main_image" class="form-control" accept="image/*"></div>
                <div class="mb-3"><label class="form-label">YouTube URL</label><input type="url" name="youtube_url" class="form-control" value="<?= e($editingAttraction['youtube_url'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Website URL</label><input type="url" name="website_url" class="form-control" value="<?= e($editingAttraction['website_url'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Google Maps URL</label><input type="url" name="google_maps_url" class="form-control" value="<?= e($editingAttraction['google_maps_url'] ?? '') ?>"></div>
                <div class="row g-2">
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Latitude</label><input type="text" name="latitude" class="form-control" value="<?= e($editingAttraction['latitude'] ?? '') ?>"></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Longitude</label><input type="text" name="longitude" class="form-control" value="<?= e($editingAttraction['longitude'] ?? '') ?>"></div></div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Opening hours</label><input type="text" name="opening_hours" class="form-control" value="<?= e($editingAttraction['opening_hours'] ?? '') ?>"></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Display order</label><input type="number" name="display_order" class="form-control" value="<?= e((string) ($editingAttraction['display_order'] ?? 0)) ?>"></div></div>
                </div>
                <div class="mb-3"><label class="form-label">Entry information</label><input type="text" name="entry_information" class="form-control" value="<?= e($editingAttraction['entry_information'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($editingAttraction['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($editingAttraction['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                <button type="submit" class="btn btn-primary"><?= $editingAttraction ? 'Update attraction' : 'Save attraction' ?></button>
                <?php if ($editingAttraction): ?><a href="index.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">Attraction list</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Destination</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($attractions as $attraction): ?>
                            <tr>
                                <td><?= e($attraction['name']) ?></td>
                                <td><?= e($attraction['destination_name'] ?? '—') ?></td>
                                <td><span class="status-chip <?= e($attraction['status']) ?>"><?= e($attraction['status']) ?></span></td>
                                <td><a href="index.php?edit=<?= (int) $attraction['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a> <a href="index.php?delete=<?= (int) $attraction['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attraction?')">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
