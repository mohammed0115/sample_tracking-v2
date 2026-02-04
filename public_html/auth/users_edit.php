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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? $target['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($email === '' || $first === '' || $last === '') {
        $error = __('error.required');
    } else {
        $beforeEmail = $target['email'];
        $beforeFirst = $target['first_name'];
        $beforeLast = $target['last_name'];
        $beforeActive = (int)$target['is_active'];
        $beforeRole = $target['role'];

        $stmt = db()->prepare('UPDATE users SET email = ?, first_name = ?, last_name = ?, role = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$email, $first, $last, $role, $is_active, $userId]);

        $actions = [];
        if ($beforeEmail !== $email || $beforeFirst !== $first || $beforeLast !== $last) {
            $actions[] = 'تعديل بيانات المستخدم';
        }
        if ($beforeActive !== $is_active) {
            $actions[] = $is_active ? 'تفعيل المستخدم' : 'إيقاف المستخدم';
        }
        if ($beforeRole !== $role) {
            $actions[] = "تغيير دور المستخدم إلى {$role}";
        }
        foreach ($actions as $action) {
            audit_log((int)current_user()['id'], null, "{$action}: {$target['username']}");
        }

        redirect(url('/auth/users'));
    }
}

ob_start();
?>
<h1><?= e(__('users.edit')) ?></h1>
<div class="card" style="max-width:520px;">
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="grid-2" style="gap:16px;">
        <div>
            <label><?= e(__('auth.username')) ?></label>
            <input type="text" value="<?= e($target['username']) ?>" disabled>
        </div>
        <div>
            <label><?= e(__('auth.email')) ?></label>
            <input type="email" name="email" value="<?= e($target['email']) ?>" required>
        </div>
        <div>
            <label><?= e(__('auth.first_name')) ?></label>
            <input type="text" name="first_name" value="<?= e($target['first_name']) ?>" required>
        </div>
        <div>
            <label><?= e(__('auth.last_name')) ?></label>
            <input type="text" name="last_name" value="<?= e($target['last_name']) ?>" required>
        </div>
        <div>
            <label><?= e(__('users.role')) ?></label>
            <select name="role">
                <option value="Admin" <?= $target['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Operator" <?= $target['role'] === 'Operator' ? 'selected' : '' ?>>Operator</option>
                <option value="Viewer" <?= $target['role'] === 'Viewer' ? 'selected' : '' ?>>Viewer</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_active" value="1" <?= (int)$target['is_active'] === 1 ? 'checked' : '' ?>>
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
$title = __('users.edit');
$active = 'users';
include __DIR__ . '/../partials/layout.php';
