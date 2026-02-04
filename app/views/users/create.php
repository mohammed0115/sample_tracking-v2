<?php
$pageTitle = 'إضافة مستخدم';
ob_start();
?>

<h1>إضافة مستخدم</h1>

<div class="card" style="max-width:700px; margin:20px auto;">
    <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=create_user" class="grid-2">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div style="grid-column:1/-1;">
            <label>اسم المستخدم *</label>
            <input type="text" name="username" value="<?php echo e($_SESSION['old_input']['username'] ?? ''); ?>" required>
        </div>
        
        <div style="grid-column:1/-1;">
            <label>البريد الإلكتروني *</label>
            <input type="email" name="email" value="<?php echo e($_SESSION['old_input']['email'] ?? ''); ?>" required>
        </div>
        
        <div>
            <label>الاسم الأول</label>
            <input type="text" name="first_name" value="<?php echo e($_SESSION['old_input']['first_name'] ?? ''); ?>">
        </div>
        
        <div>
            <label>اسم العائلة</label>
            <input type="text" name="last_name" value="<?php echo e($_SESSION['old_input']['last_name'] ?? ''); ?>">
        </div>
        
        <div>
            <label>الدور *</label>
            <select name="role" required>
                <option value="Viewer" <?php echo (($_SESSION['old_input']['role'] ?? 'Viewer') === 'Viewer') ? 'selected' : ''; ?>>Viewer</option>
                <option value="Operator" <?php echo (($_SESSION['old_input']['role'] ?? '') === 'Operator') ? 'selected' : ''; ?>>Operator</option>
                <option value="Admin" <?php echo (($_SESSION['old_input']['role'] ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <div>
            <label>كلمة المرور *</label>
            <input type="password" name="password" required>
        </div>
        
        <div style="grid-column:1/-1;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo isset($_SESSION['old_input']['is_active']) ? 'checked' : 'checked'; ?>>
                <span>نشط</span>
            </label>
        </div>
        
        <div style="grid-column:1/-1; display:flex; gap:10px;">
            <button class="btn btn-blue" type="submit">حفظ</button>
            <a href="<?php echo APP_URL; ?>/public/index.php?page=users" class="btn btn-gray">رجوع</a>
        </div>
    </form>
    <?php unset($_SESSION['old_input']); ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
