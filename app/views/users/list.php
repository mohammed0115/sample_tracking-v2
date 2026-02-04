<?php
$pageTitle = 'إدارة المستخدمين';
ob_start();
?>

<h1>إدارة المستخدمين</h1>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>قائمة المستخدمين</h3>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=create_user" class="btn btn-green">إضافة مستخدم</a>
    </div>
    
    <div class="spacer"></div>
    
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>اسم المستخدم</th>
                <th>البريد</th>
                <th>الدور</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="6">لا يوجد مستخدمون</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo e(trim($u['first_name'] . ' ' . $u['last_name'])) ?: '-'; ?></td>
                    <td><?php echo e($u['username']); ?></td>
                    <td><?php echo e($u['email']); ?></td>
                    <td><?php echo e($u['role']); ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="status approved">نشط</span>
                        <?php else: ?>
                            <span class="status rejected">موقوف</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn btn-blue" href="<?php echo APP_URL; ?>/public/index.php?page=edit_user&id=<?php echo $u['id']; ?>" title="تعديل">✏️</a>
                        <a class="btn btn-gray" href="<?php echo APP_URL; ?>/public/index.php?page=toggle_user&id=<?php echo $u['id']; ?>" title="<?php echo $u['is_active'] ? 'إيقاف' : 'تفعيل'; ?>">
                            <?php echo $u['is_active'] ? '⛔' : '✅'; ?>
                        </a>
                        <a class="btn btn-red" href="<?php echo APP_URL; ?>/public/index.php?page=reset_password&id=<?php echo $u['id']; ?>" title="إعادة تعيين كلمة المرور">🔒</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
