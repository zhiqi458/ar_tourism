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
        $stmt = $id > 0
            ? requireDatabase()->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description, status = :status, updated_at = NOW() WHERE id = :id')
            : requireDatabase()->prepare('INSERT INTO categories (name, slug, description, status, created_at, updated_at) VALUES (:name, :slug, :description, :status, NOW(), NOW())');
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            ...($id > 0 ? ['id' => $id] : []),
        ]);
        $_SESSION['flash_success'] = $id > 0 ? 'Category updated successfully.' : 'Category created successfully.';
    }
    redirect('/admin/categories/index.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    requireDatabase()->prepare('DELETE FROM categories WHERE id = :id')->execute(['id' => $id]);
    $_SESSION['flash_success'] = 'Category deleted.';
    redirect('/admin/categories/index.php');
}

$categories = requireDatabase()->query('SELECT * FROM categories ORDER BY created_at DESC')->fetchAll();
$editingCategory = null;
if (isset($_GET['edit'])) {
    $stmt = requireDatabase()->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editingCategory = $stmt->fetch() ?: null;
}
$pageTitle = 'Categories';
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h5 mb-3"><?= $editingCategory ? 'Edit category' : 'Add category' ?></h2>
            <form method="post" action="index.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) ($editingCategory['id'] ?? 0) ?>">
                <div class="mb-3">
                    <label class="form-label">Category name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($editingCategory['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($editingCategory['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($editingCategory['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($editingCategory['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= $editingCategory ? 'Update category' : 'Save category' ?></button>
                <?php if ($editingCategory): ?><a href="index.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">Categories list</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>Name</th><th>Slug</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= e($category['name']) ?></td>
                                <td><?= e($category['slug']) ?></td>
                                <td><span class="status-chip <?= e($category['status']) ?>"><?= e($category['status']) ?></span></td>
                                <td><a href="index.php?edit=<?= (int) $category['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a> <a href="index.php?delete=<?= (int) $category['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
