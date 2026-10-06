<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $_SESSION['flash_error'] = 'Please provide both username/email and password.';
        redirect('/admin/login.php');
    }

    try {
        $pdo = requireDatabase();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE (username = :username_identifier OR email = :email_identifier) AND status = "active" LIMIT 1');
        $stmt->execute([
            'username_identifier' => $identifier,
            'email_identifier' => $identifier,
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            loginUser($user);
            logActivity((int) $user['id'], 'Admin logged in', 'Administrator logged into the dashboard.');
            $_SESSION['flash_success'] = 'Welcome back.';
            redirect('/admin/index.php');
        }

        $_SESSION['flash_error'] = 'Invalid username/email or password.';
        redirect('/admin/login.php');
    } catch (Throwable $e) {
        error_log('Admin login failed: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'The database is not configured yet. Please import the SQL schema.';
        redirect('/admin/login.php');
    }
}

$siteName = getSetting('site_name', APP_NAME);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e($siteName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= appUrl('assets/css/styles.css') ?>">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 p-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 54px; height: 54px; font-size: 1.4rem;">
                            <i class="fa-solid fa-vr-cardboard"></i>
                        </div>
                        <h1 class="h3 mt-3 mb-0"><?= e($siteName) ?></h1>
                        <p class="text-muted mb-0">Administrator Login</p>
                    </div>

                    <?php if (($msg = flash('flash_error'))): ?>
                        <div class="alert alert-danger" role="alert"><?= e($msg) ?></div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="mb-3">
                            <label for="identifier" class="form-label">Username or Email</label>
                            <input type="text" id="identifier" name="identifier" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3 small text-muted">Default admin account: admin / Admin123!</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
