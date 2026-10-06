<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$poster = fetchPosterById($id);
if (!$poster) {
    $_SESSION['flash_error'] = 'Poster not found.';
    redirect('/admin/ar-posters/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $posterId = (int) ($_POST['poster_id'] ?? $id);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $stmt = requireDatabase()->prepare('UPDATE ar_posters SET name = :name, description = :description, status = :status, updated_at = NOW() WHERE id = :id');
    $stmt->execute(['name' => $name, 'description' => $description, 'status' => $status, 'id' => $posterId]);
    $_SESSION['flash_success'] = 'Poster updated.';
    redirect('/admin/ar-posters/view.php?id=' . $posterId);
}

$pageTitle = 'AR Poster';
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3">Poster details</h2>
            <img src="<?= e(imageUrl($poster['poster_image'] ?? '')) ?>" class="img-fluid rounded-3 mb-3" alt="<?= e($poster['name']) ?>" style="max-height: 420px; object-fit: cover;">
            <div class="mb-3"><strong>Name:</strong> <?= e($poster['name']) ?></div>
            <div class="mb-3"><strong>Description:</strong> <?= e($poster['description'] ?? '—') ?></div>
            <div class="mb-3"><strong>Target status:</strong> <span class="status-chip <?= strtolower(str_replace('_', '-', $poster['target_status'])) ?>"><?= e(str_replace('_', ' ', strtolower($poster['target_status']))) ?></span></div>
            <div class="mb-3"><strong>Target file:</strong> <?= e($poster['target_file'] ?: 'Not compiled') ?></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">Edit poster</h2>
            <form method="post" action="view.php?id=<?= (int) $poster['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="poster_id" value="<?= (int) $poster['id'] ?>">
                <div class="mb-3"><label class="form-label">Poster name</label><input type="text" name="name" class="form-control" value="<?= e($poster['name']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control"><?= e($poster['description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $poster['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $poster['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?= appUrl('admin/ar-posters/index.php') ?>" class="btn btn-outline-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
