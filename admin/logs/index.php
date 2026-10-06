<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$logs = requireDatabase()->query('SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 30')->fetchAll();
$pageTitle = 'Activity logs';
require_once __DIR__ . '/../header.php';
?>
<div class="card p-4">
    <h2 class="h5 mb-3">Recent activity</h2>
    <div class="list-group list-group-flush">
        <?php foreach ($logs as $log): ?>
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold"><?= e($log['action']) ?></div>
                        <small class="text-muted"><?= e($log['description']) ?> | <?= e($log['full_name'] ?? 'System') ?></small>
                    </div>
                    <small class="text-muted"><?= e(date('M d, Y H:i', strtotime($log['created_at']))) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
