<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $first === '' || $last === '' || $password === '') {
        $error = __('error.required');
    } elseif ($password !== $confirm) {
        $error = __('error.password_mismatch');
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = __('error.exists');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare('INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
            $stmt->execute([$username, $email, $hash, $first, $last, 'Viewer']);
            $newUserId = (int)db()->lastInsertId();
            audit_log($newUserId, null, 'تسجيل مستخدم جديد');
            $success = __('auth.register_success');
        }
    }
}

ob_start();
?>
<div class="main">
    <h1><?= e(__('auth.register')) ?></h1>
    <form method="post" class="card" style="max-width:400px;margin:auto;">
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <label><?= e(__('auth.first_name')) ?></label>
                <input type="text" name="first_name" required>
            </div>
            <div>
                <label><?= e(__('auth.last_name')) ?></label>
                <input type="text" name="last_name" required>
            </div>
            <div>
                <label><?= e(__('auth.username')) ?></label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label><?= e(__('auth.email')) ?></label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label><?= e(__('auth.password')) ?></label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label><?= e(__('auth.password_confirm')) ?></label>
                <input type="password" name="password_confirm" required>
            </div>
            <button class="btn btn-blue" type="submit"><?= e(__('auth.register')) ?></button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('auth.register');
include __DIR__ . '/../partials/layout.php';
