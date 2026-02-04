<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/lang.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect(url('/auth/login'));
    }
}

function require_role(array $roles): void {
    require_login();
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo e(__('error.forbidden'));
        exit;
    }
}

function login_user(array $user): void {
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'role' => $user['role'],
        'is_active' => (int)$user['is_active'],
    ];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function audit_log(int $userId, ?int $sampleId, string $action): void {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, sample_id, action) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $sampleId, $action]);
}
