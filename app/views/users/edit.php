<?php
$pageTitle = 'تعديل مستخدم';
ob_start();
?>

<h1>تعديل مستخدم</h1>

<div class="card" style="max-width:700px; margin:20px auto;">
    <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=edit_user" class="grid-2">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
        
        <div style="grid-column:1/-1;">
            <label>اسم المستخدم</label>
            <input type="text" value="<?php echo e($user['username']); ?>" disabled style="background:#f3f4f6;">
        </div>
        
        <div style="grid-column:1/-1;">
            <label>البريد الإلكتروني *</label>
            <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
        </div>
        
        <div>
            <label>الاسم الأول</label>
            <input type="text" name="first_name" value="<?php echo e($user['first_name']); ?>">
        </div>
        
        <div>
            <label>اسم العائلة</label>
            <input type="text" name="last_name" value="<?php echo e($user['last_name']); ?>">
        </div>
        
        <div>
            <label>الدور *</label>
            <select name="role" required>
                <option value="Viewer" <?php echo $user['role'] === 'Viewer' ? 'selected' : ''; ?>>Viewer</option>
                <option value="Operator" <?php echo $user['role'] === 'Operator' ? 'selected' : ''; ?>>Operator</option>
                <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <div style="grid-column:1/-1;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                <span>نشط</span>
            </label>
        </div>
        
        <div style="grid-column:1/-1; display:flex; gap:10px;">
            <button class="btn btn-blue" type="submit">حفظ</button>
            <a href="<?php echo APP_URL; ?>/public/index.php?page=users" class="btn btn-gray">رجوع</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
