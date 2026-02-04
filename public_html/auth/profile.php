<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_login();
$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($first === '' || $last === '' || $email === '') {
        $error = __('error.required');
    } else {
        // Handle avatar upload
        $avatar_path = $user['avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['avatar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $upload_dir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Delete old avatar if exists
                if (!empty($user['avatar']) && file_exists(__DIR__ . '/../' . $user['avatar'])) {
                    unlink(__DIR__ . '/../' . $user['avatar']);
                }
                
                $new_filename = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
                $target = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                    $avatar_path = 'uploads/avatars/' . $new_filename;
                }
            }
        }
        
        $stmt = db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, avatar = ? WHERE id = ?');
        $stmt->execute([$first, $last, $email, $avatar_path, $user['id']]);

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $user['id']]);
        }

        audit_log((int)$user['id'], null, 'User updated own profile');

        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) {
            login_user($fresh);
        }
        $success = __('success.saved');
    }
}

ob_start();
?>
<h1><?= e(__('nav.profile')) ?></h1>
<div class="card" style="max-width:520px;">
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="grid-2" style="gap:16px;">
        <div style="grid-column:1/-1;text-align:center;margin-bottom:10px;">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= e(url('/' . $user['avatar'])) ?>" alt="Avatar" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #3498db;">
            <?php else: ?>
                <div style="width:120px;height:120px;border-radius:50%;background:#e0e0e0;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user" style="font-size:50px;color:#999;"></i>
                </div>
            <?php endif; ?>
        </div>
        <div style="grid-column:1/-1;">
            <label><?= e(__('auth.avatar')) ?></label>
            <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/jpg">
            <small style="color:#666;display:block;margin-top:5px;"><?= e(__('auth.avatar_help')) ?></small>
        </div>
        <div>
            <label><?= e(__('auth.first_name')) ?></label>
            <input type="text" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required>
        </div>
        <div>
            <label><?= e(__('auth.last_name')) ?></label>
            <input type="text" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required>
        </div>
        <div style="grid-column:1/-1;">
            <label><?= e(__('auth.email')) ?></label>
            <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
        </div>
        <div style="grid-column:1/-1;">
            <label><?= e(__('auth.new_password_optional')) ?></label>
            <input type="password" name="password">
            <small style="color:#999;display:block;margin-top:5px;"><?= e(__('auth.password_help')) ?></small>
        </div>
        <div style="grid-column:1/-1;display:flex;gap:10px;">
            <button class="btn btn-blue" type="submit"><?= e(__('auth.save')) ?></button>
            <a class="btn btn-gray" href="<?= e(url('/samples/add')) ?>"><?= e(__('auth.back')) ?></a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('nav.profile');
$active = '';
include __DIR__ . '/../partials/layout.php';
