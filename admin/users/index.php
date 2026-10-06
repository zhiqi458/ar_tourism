<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'editor';
    $status = $_POST['status'] ?? 'active';
    if ($id > 0 && $fullName !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $params = ['id' => $id, 'full_name' => $fullName, 'email' => $email, 'role' => $role, 'status' => $status];
        $sql = 'UPDATE users SET full_name = :full_name, email = :email, role = :role, status = :status';
        if (trim($_POST['password'] ?? '') !== '') {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        $stmt = requireDatabase()->prepare($sql . ', updated_at = NOW() WHERE id = :id');
        $stmt->execute($params);
        $_SESSION['flash_success'] = 'User updated.';
    }
    redirect('/admin/users/index.php');
}

$users = requireDatabase()->query('SELECT id, username, email, full_name, role, status, last_login_at FROM users ORDER BY created_at DESC')->fetchAll();
$editingUser = null;
if (isset($_GET['edit'])) {
    $stmt = requireDatabase()->prepare('SELECT id, username, email, full_name, role, status FROM users WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editingUser = $stmt->fetch() ?: null;
}
$pageTitle = 'Users';
require_once __DIR__ . '/../header.php';
?>
<div class="card p-4">
    <?php if ($editingUser): ?>
        <h2 class="h5 mb-3">Edit user: <?= e($editingUser['username']) ?></h2>
        <form method="post" action="index.php" class="row g-3 mb-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="id" value="<?= (int) $editingUser['id'] ?>">
            <div class="col-md-6"><label class="form-label">Full name</label><input name="full_name" class="form-control" value="<?= e($editingUser['full_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($editingUser['email']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Role</label><select name="role" class="form-select"><option value="admin" <?= $editingUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option><option value="editor" <?= $editingUser['role'] === 'editor' ? 'selected' : '' ?>>Editor</option></select></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $editingUser['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $editingUser['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
            <div class="col-md-4"><label class="form-label">New password</label><input type="password" name="password" class="form-control" minlength="8"></div>
            <div class="col-12"><button class="btn btn-primary">Update user</button> <a href="index.php" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    <?php endif; ?>
    <h2 class="h5 mb-3">System users</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Full name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['full_name']) ?></td>
                        <td><?= e($user['username']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['role']) ?></td>
                        <td><span class="status-chip <?= e($user['status']) ?>"><?= e($user['status']) ?></span></td>
                        <td><a href="index.php?edit=<?= (int) $user['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
