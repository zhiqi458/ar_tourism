<?php
require_once __DIR__ . '/config.php';

function loginUser(array $user): void {
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'] ?: $user['username'];
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function canAccessAdmin(): bool {
    $user = currentUser();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}
