<?php
$pageTitle = 'تسجيل حساب جديد';
ob_start();
?>

<div style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg);">
    <div class="card" style="max-width:600px; width:100%; padding:40px 36px;">
        <div style="text-align:center; margin-bottom:25px;">
            <h2 style="margin:0; color:#1f2937; font-weight:700; font-size:28px;">تسجيل حساب جديد</h2>
        </div>
        
        <?php 
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:20px;">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=register" class="grid-2">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div style="grid-column:1/-1;">
                <label>اسم المستخدم</label>
                <input type="text" name="username" value="<?php echo e($_SESSION['old_input']['username'] ?? ''); ?>" required>
            </div>
            
            <div style="grid-column:1/-1;">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" value="<?php echo e($_SESSION['old_input']['email'] ?? ''); ?>" required>
            </div>
            
            <div>
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            
            <div>
                <label>تأكيد كلمة المرور</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <div style="grid-column:1/-1; display:flex; gap:10px;">
                <button class="btn btn-blue" type="submit">تسجيل</button>
                <a href="<?php echo APP_URL; ?>/public/index.php?page=login" class="btn btn-gray">رجوع لتسجيل الدخول</a>
            </div>
        </form>
        <?php unset($_SESSION['old_input']); ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
