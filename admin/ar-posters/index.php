<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name = trim($_POST['name'] ?? '');
    $posterImage = $_FILES['poster_image'] ?? null;
    $fileName = '';
    if ($posterImage && $posterImage['tmp_name']) {
        $fileName = saveUploadedFile($posterImage, UPLOAD_DIR . '/posts', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'], 2 * 1024 * 1024) ?: '';
    }

    $stmt = requireDatabase()->prepare('INSERT INTO ar_posters (name, description, poster_image, target_file, target_status, status, created_at, updated_at) VALUES (:name, :description, :poster_image, :target_file, :target_status, :status, NOW(), NOW())');
    $stmt->execute([
        'name' => $name,
        'description' => trim($_POST['description'] ?? ''),
        'poster_image' => $fileName,
        'target_file' => '',
        'target_status' => 'NOT_COMPILED',
        'status' => $_POST['status'] ?? 'active',
    ]);
    $_SESSION['flash_success'] = 'AR poster created.';
    redirect('/admin/ar-posters/index.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    requireDatabase()->prepare('DELETE FROM ar_posters WHERE id = :id')->execute(['id' => $id]);
    $_SESSION['flash_success'] = 'AR poster deleted.';
    redirect('/admin/ar-posters/index.php');
}

$posters = requireDatabase()->query('SELECT * FROM ar_posters ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'AR Posters';
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3">Add AR poster</h2>
            <form method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3"><label class="form-label">Poster name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control"></textarea></div>
                <div class="mb-3"><label class="form-label">Poster image</label><input type="file" name="poster_image" class="form-control" accept="image/*"></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <button type="submit" class="btn btn-primary">Create poster</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">AR posters</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Target</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($posters as $poster): ?>
                            <tr>
                                <td><?= e($poster['name']) ?></td>
                                <td><span class="status-chip <?= strtolower(str_replace('_', '-', $poster['target_status'])) ?>"><?= e(str_replace('_', ' ', strtolower($poster['target_status']))) ?></span></td>
                                <td>
                                    <a href="view.php?id=<?= (int) $poster['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="index.php?delete=<?= (int) $poster['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this poster?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
