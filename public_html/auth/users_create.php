<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? 'Viewer';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $first === '' || $last === '' || $password === '') {
        $error = __('error.required');
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = __('error.exists');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare('INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hash, $first, $last, $role, $is_active]);
            audit_log((int)current_user()['id'], null, "إنشاء مستخدم: {$username} ({$role})");
            redirect(url('/auth/users'));
        }
    }
}

ob_start();
?>
<h1><?= e(__('users.add')) ?></h1>
<div class="card" style="max-width:520px;">
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="grid-2" style="gap:16px;">
        <div>
            <label><?= e(__('auth.username')) ?></label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label><?= e(__('auth.email')) ?></label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label><?= e(__('auth.first_name')) ?></label>
            <input type="text" name="first_name" required>
        </div>
        <div>
            <label><?= e(__('auth.last_name')) ?></label>
            <input type="text" name="last_name" required>
        </div>
        <div>
            <label><?= e(__('users.role')) ?></label>
            <select name="role">
                <option value="Admin">Admin</option>
                <option value="Operator">Operator</option>
                <option value="Viewer" selected>Viewer</option>
            </select>
        </div>
        <div>
            <label><?= e(__('auth.password')) ?></label>
            <input type="password" name="password" required>
        </div>
        <div style="grid-column:1/-1;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_active" value="1" checked>
            <span><?= e(__('users.account_active')) ?></span>
        </div>
        <div style="grid-column:1/-1;display:flex;gap:10px;">
            <button class="btn btn-blue" type="submit"><?= e(__('auth.save')) ?></button>
            <a class="btn btn-gray" href="<?= e(url('/auth/users')) ?>"><?= e(__('auth.back')) ?></a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('users.add');
$active = 'users';
include __DIR__ . '/../partials/layout.php';
