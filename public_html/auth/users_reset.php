<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';
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

$raw = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
$newPassword = substr($raw, 0, 8);
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$hash, $userId]);

audit_log((int)current_user()['id'], null, 'إعادة تعيين كلمة المرور للمستخدم: ' . $target['username']);

ob_start();
?>
<h1><?= e(__('users.password_reset_title')) ?></h1>
<div class="card" style="max-width:520px;">
    <div class="alert success"><?= e(__('users.password_reset_done')) ?> <strong><?= e($target['username']) ?></strong></div>
    <div class="card" style="background:#f9fafb;">
        <div style="font-size:18px;text-align:center;letter-spacing:1px;">
            <?= e($newPassword) ?>
        </div>
    </div>
    <div style="margin-top:12px;text-align:center;">
        <a class="btn btn-blue" href="<?= e(url('/auth/users')) ?>"><?= e(__('auth.back')) ?></a>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = __('users.password_reset_title');
$active = 'users';
include __DIR__ . '/../partials/layout.php';
