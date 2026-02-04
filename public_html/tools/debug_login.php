<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();
$stmt = $pdo->prepare("SELECT username, password, is_active FROM users WHERE username = ?");
$stmt->execute(['admin']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
header('Content-Type: text/plain; charset=utf-8');
if (!$user) {
    echo "admin not found\n";
    exit;
}
var_dump($user);
$ok = password_verify('admin123', $user['password']);
var_dump($ok);
