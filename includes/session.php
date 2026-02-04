<?php
// Session Management

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// CSRF Token Generation
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Verification
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Check if user has role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === $role;
}

// Check if user is admin
function isAdmin() {
    return hasRole('Admin');
}

// Check if user is operator or admin
function isOperatorOrAdmin() {
    return hasRole('Admin') || hasRole('Operator');
}

// Require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/public/index.php?page=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Require admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
    }
}
