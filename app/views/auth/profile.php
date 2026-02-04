<?php
$pageTitle = 'الملف الشخصي';
ob_start();
?>

<h1>الملف الشخصي</h1>

<div class="card" style="max-width:620px; margin:20px auto;">
    <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=profile" enctype="multipart/form-data" class="grid-2">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div>
            <label>الاسم الأول</label>
            <input type="text" name="first_name" value="<?php echo e($user['first_name']); ?>">
        </div>
        
        <div>
            <label>اسم العائلة</label>
            <input type="text" name="last_name" value="<?php echo e($user['last_name']); ?>">
        </div>
        
        <div style="grid-column:1/-1;">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
        </div>
        
        <div style="grid-column:1/-1;">
            <label>الصورة الشخصية</label>
            <input type="file" name="avatar" accept="image/*">
            <?php if ($user['avatar']): ?>
            <p class="muted" style="margin-top:8px;">الصورة الحالية: <?php echo e($user['avatar']); ?></p>
            <?php endif; ?>
        </div>
        
        <div style="grid-column:1/-1; display:flex; gap:10px;">
            <button class="btn btn-blue" type="submit">حفظ التغييرات</button>
            <a href="<?php echo APP_URL; ?>/public/index.php?page=dashboard" class="btn btn-gray">إلغاء</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
