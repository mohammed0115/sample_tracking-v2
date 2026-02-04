<?php
$pageTitle = 'تسجيل الدخول';
ob_start();
?>

<div style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg);">
    <div class="card" style="max-width:520px; width:100%; padding:40px 36px;">
        <div style="text-align:center; margin-bottom:25px;">
            <h2 style="margin:0; color:#1f2937; font-weight:700; font-size:28px;">تسجيل الدخول</h2>
            <p style="margin-top:6px; color:#6b7280; font-size:14px;">نظام تتبع العينات</p>
        </div>
        
        <?php 
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=login" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <?php if (isset($_GET['redirect'])): ?>
            <input type="hidden" name="redirect" value="<?php echo e($_GET['redirect']); ?>">
            <?php endif; ?>
            
            <div>
                <label>اسم المستخدم</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div>
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            
            <button class="btn btn-blue" type="submit" style="width:100%; padding:10px 18px; font-size:16px;">دخول</button>
            
            <a href="<?php echo APP_URL; ?>/public/index.php?page=register" style="text-align:center; color:#2563eb; font-size:15px;">تسجيل حساب جديد</a>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
