<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'AR Tourism Explorer');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'synergy1_zhiqi_ar_tourism');
define('DB_USER', 'synergy1_yenping');
define('DB_PASS', 'R.zb0ZwEuGZ}*fW2');

define('BASE_PATH', dirname(__DIR__));

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$projectRoot = realpath(BASE_PATH);
$basePath = '';

if ($documentRoot !== false && $projectRoot !== false) {
    $normalizedDocumentRoot = str_replace('\\', '/', rtrim($documentRoot, DIRECTORY_SEPARATOR));
    $normalizedProjectRoot = str_replace('\\', '/', rtrim($projectRoot, DIRECTORY_SEPARATOR));

    if (strcasecmp($normalizedProjectRoot, $normalizedDocumentRoot) !== 0
        && str_starts_with(strtolower($normalizedProjectRoot . '/'), strtolower($normalizedDocumentRoot . '/'))) {
        $basePath = substr($normalizedProjectRoot, strlen($normalizedDocumentRoot));
    }
}

define('APP_URL', $scheme . '://' . $host . rtrim($basePath, '/'));

define('UPLOAD_DIR', BASE_PATH . '/assets/uploads');
define('TARGET_DIR', BASE_PATH . '/assets/ar-targets');

define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_EMAIL', 'admin@ar-tourism.local');
define('DEFAULT_ADMIN_PASSWORD', 'Admin123!');

function requireDatabase(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

function startsWithHttps(): bool {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443');
}

function appUrl(string $path = ''): string {
    $suffix = ltrim($path, '/');
    return APP_URL . ($suffix !== '' ? '/' . $suffix : '');
}

function redirect(string $path): void {
    $location = preg_match('/^https?:\/\//i', $path) ? $path : appUrl($path);
    header('Location: ' . $location);
    exit;
}

function e(string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(419);
        $_SESSION['flash_error'] = 'The form expired. Please try again.';
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

function flash(string $key): ?string {
    if (isset($_SESSION[$key])) {
        $value = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }

    return null;
}

function currentUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = requireDatabase()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        return null;
    }

    return $user;
}

function requireAdmin(): void {
    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        $_SESSION['flash_error'] = 'Please log in as an administrator.';
        redirect('/admin/login.php');
    }
}

function logActivity(int $userId, string $action, string $description): void {
    $pdo = requireDatabase();
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (:user_id, :action, :description, :ip_address, NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'description' => $description,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
}

function ensureDefaultAdmin(): void {
    $pdo = requireDatabase();
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE username = :username OR email = :email');
    $stmt->execute([
        'username' => DEFAULT_ADMIN_USERNAME,
        'email' => DEFAULT_ADMIN_EMAIL,
    ]);

    if ((int) $stmt->fetchColumn() === 0) {
        $hash = password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role, status, created_at, updated_at) VALUES (:username, :email, :password_hash, :full_name, :role, :status, NOW(), NOW())');
        $insert->execute([
            'username' => DEFAULT_ADMIN_USERNAME,
            'email' => DEFAULT_ADMIN_EMAIL,
            'password_hash' => $hash,
            'full_name' => 'System Administrator',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}

function safeFileName(string $fileName): string {
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', strtolower(pathinfo($fileName, PATHINFO_FILENAME)));
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'upload';
    }

    return $base . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($extension !== '' ? '.' . $extension : '');
}

function saveUploadedFile(array $file, string $targetDir, array $allowedMimeTypes = [], int $maxSize = 5 * 1024 * 1024): ?string {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] <= 0 || $file['size'] > $maxSize) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if ($allowedMimeTypes !== [] && !in_array($mime, $allowedMimeTypes, true)) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'mp4', 'webm'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = safeFileName($file['name']);
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    $relative = str_replace('\\', '/', str_replace(UPLOAD_DIR . '/', '', $destination));
    return ltrim($relative, '/');
}

function getSetting(string $key, string $default = ''): string {
    $stmt = requireDatabase()->prepare('SELECT value FROM settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();

    return $value !== false ? $value : $default;
}

function setSetting(string $key, string $value): void {
    $pdo = requireDatabase();
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, value, updated_at) VALUES (:key, :value, NOW()) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()');
    $stmt->execute(['key' => $key, 'value' => $value]);
}

function formatDate(string $date): string {
    if ($date === '' || $date === null) {
        return '—';
    }

    return date('M d, Y', strtotime($date));
}

function youtubeIdFromUrl(string $url): ?string {
    $url = trim($url);
    if ($url === '') {
        return null;
    }

    if (preg_match('/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
        return $matches[1];
    }

    return null;
}

function googleMapLink(string $lat, ?string $lng = null): string {
    $lat = (float) $lat;
    $lng = (float) ($lng ?? 0);
    if ($lat === 0.0 && $lng === 0.0) {
        return '#';
    }

    return 'https://www.google.com/maps?q=' . rawurlencode($lat . ',' . $lng);
}

function imageUrl(string $path): string {
    if ($path === '' || $path === null) {
        return appUrl('assets/images/placeholder-landmark.svg');
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return appUrl('assets/uploads/' . ltrim($path, '/'));
}
