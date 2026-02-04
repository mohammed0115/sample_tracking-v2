<?php
$pageTitle = 'إعادة تعيين كلمة المرور';
ob_start();
?>

<h1>إعادة تعيين كلمة المرور</h1>

<div class="card" style="max-width:620px; margin:40px auto; text-align:center;">
    <p>تم إنشاء كلمة مرور جديدة للمستخدم: <strong><?php echo e($username); ?></strong></p>
    <p style="font-size:20px; margin:20px 0;">كلمة المرور الجديدة: <strong style="color:var(--blue); background:#eef2ff; padding:10px 20px; border-radius:8px;"><?php echo e($newPassword); ?></strong></p>
    <p class="muted">يرجى حفظ كلمة المرور هذه وإبلاغ المستخدم بها.</p>
    <div class="spacer"></div>
    <a href="<?php echo APP_URL; ?>/public/index.php?page=users" class="btn btn-blue">رجوع لإدارة المستخدمين</a>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
