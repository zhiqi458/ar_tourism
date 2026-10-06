<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach (['site_name', 'site_tagline', 'contact_email', 'contact_phone'] as $key) {
        $value = trim($_POST[$key] ?? '');
        if ($value !== '') {
            setSetting($key, $value);
        }
    }
    $_SESSION['flash_success'] = 'Settings updated.';
    redirect('/admin/settings/index.php');
}

$pageTitle = 'Settings';
$settings = [
    'site_name' => getSetting('site_name', APP_NAME),
    'site_tagline' => getSetting('site_tagline', 'Explore destinations with immersive AR stories'),
    'contact_email' => getSetting('contact_email', 'hello@artourism.local'),
    'contact_phone' => getSetting('contact_phone', '+6012-3456789'),
];
require_once __DIR__ . '/../header.php';
?>
<div class="card p-4">
    <h2 class="h5 mb-3">System settings</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="row g-3">
            <div class="col-md-6"><div class="mb-3"><label class="form-label">Site name</label><input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name']) ?>"></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="form-label">Site tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline']) ?>"></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="form-label">Contact email</label><input type="email" name="contact_email" class="form-control" value="<?= e($settings['contact_email']) ?>"></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="form-label">Contact phone</label><input type="text" name="contact_phone" class="form-control" value="<?= e($settings['contact_phone']) ?>"></div></div>
        </div>
        <button type="submit" class="btn btn-primary">Save settings</button>
    </form>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
