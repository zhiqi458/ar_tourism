<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
        $photo = $_FILES['cover_image'] ?? null;
        $cover = '';
        if ($photo && $photo['tmp_name']) {
            $file = saveUploadedFile($photo, UPLOAD_DIR . '/destinations', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'], 2 * 1024 * 1024);
            $cover = $file ?: '';
        }

        $sql = $id > 0
            ? 'UPDATE destinations SET name = :name, slug = :slug, short_description = :short_description, full_description = :full_description, cover_image = CASE WHEN :new_cover <> "" THEN :new_cover ELSE cover_image END, location = :location, latitude = :latitude, longitude = :longitude, map_url = :map_url, category_id = :category_id, status = :status, updated_at = NOW() WHERE id = :id'
            : 'INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, map_url, category_id, status, created_at, updated_at) VALUES (:name, :slug, :short_description, :full_description, :cover_image, :location, :latitude, :longitude, :map_url, :category_id, :status, NOW(), NOW())';
        $params = [
            'name' => $name,
            'slug' => $slug,
            'short_description' => trim($_POST['short_description'] ?? ''),
            'full_description' => trim($_POST['full_description'] ?? ''),
            'cover_image' => $cover,
            'location' => trim($_POST['location'] ?? ''),
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'map_url' => trim($_POST['map_url'] ?? ''),
            'category_id' => $_POST['category_id'] ?? null,
            'status' => $_POST['status'] ?? 'active',
        ];
        if ($id > 0) {
            $params['new_cover'] = $cover;
            $params['id'] = $id;
        } else {
            $params['cover_image'] = $cover;
        }
        requireDatabase()->prepare($sql)->execute($params);
        $_SESSION['flash_success'] = $id > 0 ? 'Destination updated successfully.' : 'Destination saved successfully.';
    }
    redirect('/admin/destinations/index.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    requireDatabase()->prepare('DELETE FROM destinations WHERE id = :id')->execute(['id' => $id]);
    $_SESSION['flash_success'] = 'Destination deleted.';
    redirect('/admin/destinations/index.php');
}

$destinations = requireDatabase()->query('SELECT d.*, c.name AS category_name FROM destinations d LEFT JOIN categories c ON c.id = d.category_id ORDER BY d.created_at DESC')->fetchAll();
$categories = requireDatabase()->query('SELECT * FROM categories WHERE status = "active" ORDER BY name ASC')->fetchAll();
$editingDestination = null;
if (isset($_GET['edit'])) {
    $stmt = requireDatabase()->prepare('SELECT * FROM destinations WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editingDestination = $stmt->fetch() ?: null;
}
$pageTitle = 'Destinations';
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3"><?= $editingDestination ? 'Edit destination' : 'Add destination' ?></h2>
            <form method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) ($editingDestination['id'] ?? 0) ?>">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= e($editingDestination['name'] ?? '') ?>" required></div>
                <div class="mb-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($editingDestination['location'] ?? '') ?>"></div>
                <div class="row g-2">
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Latitude</label><input type="text" name="latitude" class="form-control" value="<?= e($editingDestination['latitude'] ?? '') ?>"></div></div>
                    <div class="col-md-6"><div class="mb-3"><label class="form-label">Longitude</label><input type="text" name="longitude" class="form-control" value="<?= e($editingDestination['longitude'] ?? '') ?>"></div></div>
                </div>
                <div class="mb-3"><label class="form-label">Map URL</label><input type="url" name="map_url" class="form-control" value="<?= e($editingDestination['map_url'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Category</label><select name="category_id" class="form-select">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) ($editingDestination['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="mb-3"><label class="form-label">Short description</label><textarea name="short_description" rows="3" class="form-control"><?= e($editingDestination['short_description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Full description</label><textarea name="full_description" rows="4" class="form-control"><?= e($editingDestination['full_description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Cover image</label><input type="file" name="cover_image" class="form-control" accept="image/*"></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($editingDestination['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($editingDestination['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                <button type="submit" class="btn btn-primary"><?= $editingDestination ? 'Update destination' : 'Save destination' ?></button>
                <?php if ($editingDestination): ?><a href="index.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">Destination list</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Location</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($destinations as $destination): ?>
                            <tr>
                                <td><?= e($destination['name']) ?></td>
                                <td><?= e($destination['location'] ?? '—') ?></td>
                                <td><span class="status-chip <?= e($destination['status']) ?>"><?= e($destination['status']) ?></span></td>
                                <td><a href="index.php?edit=<?= (int) $destination['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a> <a href="index.php?delete=<?= (int) $destination['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this destination?')">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
