<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

if (is_logged_in()) {
    redirect(url('/samples/add'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password'])) {
            login_user($user);
            audit_log((int)$user['id'], null, 'تسجيل الدخول');
            $next = $_POST['next'] ?? $_GET['next'] ?? url('/samples/list');
            redirect($next);
        }
    }
    $error = __('auth.login_error');
}

ob_start();
?>
<div style="display:flex;align-items:center;justify-content:center;min-height:80vh;">
    <div class="card" style="max-width: 520px; width: 100%; padding: 40px 36px; box-sizing: border-box;">
        <div style="text-align:center;margin-bottom:25px;">
            <h2 style="margin:0;color:#1f2937;font-weight:700;font-size:28px;"><?= e(__('auth.login')) ?></h2>
            <p style="margin-top:6px;color:#6b7280;font-size:14px;"><?= e(__('app.title')) ?></p>
        </div>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" style="display:flex;flex-direction:column;gap:16px;">
            <div>
                <label><?= e(__('auth.username')) ?></label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label><?= e(__('auth.password')) ?></label>
                <input type="password" name="password" required>
            </div>
            <button class="btn btn-blue" type="submit" style="width: 100%; padding: 10px 18px; border-radius: 14px; font-size: 16px;"><?= e(__('auth.login')) ?></button>
            <a href="<?= e(url('/auth/register')) ?>" style="text-align:center; color:#2563eb; font-size: 15px;"><?= e(__('auth.register')) ?></a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = __('auth.login');
include __DIR__ . '/../partials/layout.php';
