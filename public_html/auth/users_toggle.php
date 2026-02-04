<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$userId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$target = $stmt->fetch();
if (!$target) {
    http_response_code(404);
    echo e(__('error.user_not_found'));
    exit;
}

$newActive = (int)$target['is_active'] === 1 ? 0 : 1;
$stmt = db()->prepare('UPDATE users SET is_active = ? WHERE id = ?');
$stmt->execute([$newActive, $userId]);

audit_log((int)current_user()['id'], null, ($newActive ? 'تفعيل المستخدم' : 'إيقاف المستخدم') . ': ' . $target['username']);

redirect(url('/auth/users'));
