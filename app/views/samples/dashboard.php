<?php
$pageTitle = 'لوحة التحكم';
ob_start();
?>

<h1>لوحة التحكم - إضافة عينة جديدة</h1>

<div class="grid-3">
    <div class="action-card green">إضافة جديدة <span>＋</span></div>
    <a class="action-card blue" href="<?php echo APP_URL; ?>/public/index.php?page=samples" style="color:#fff; text-decoration:none;">قائمة العينات</a>
    <a class="action-card blue" href="<?php echo APP_URL; ?>/public/index.php?page=reports" style="color:#fff; text-decoration:none;">التقارير</a>
</div>

<div class="spacer"></div>

<div class="grid-2">
    <div class="card">
        <h3>إحصائيات العينات</h3>
        <div class="stat-card">
            <span>إجمالي العينات</span>
            <strong><?php echo number_format($stats['total']); ?></strong>
        </div>
        <div class="stat-card">
            <span>قيد الفحص</span>
            <strong><?php echo number_format($stats['by_status']['pending'] ?? 0); ?></strong>
        </div>
        <div class="stat-card">
            <span>تم التحقق</span>
            <strong><?php echo number_format($stats['by_status']['checked'] ?? 0); ?></strong>
        </div>
        <div class="stat-card">
            <span>معتمدة</span>
            <strong><?php echo number_format($stats['by_status']['approved'] ?? 0); ?></strong>
        </div>
    </div>
    
    <div class="card">
        <h3>ملخص الأنواع</h3>
        <?php foreach ($stats['by_type'] as $type => $count): ?>
        <div class="stat-card">
            <span><?php echo e($type); ?></span>
            <strong><?php echo number_format($count); ?></strong>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="spacer"></div>

<div class="card">
    <h3>إضافة عينة</h3>
    <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=add_sample" class="grid-2">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div>
            <label>نوع العينة *</label>
            <input type="text" name="sample_type" value="<?php echo e($_SESSION['old_input']['sample_type'] ?? ''); ?>" required list="sample_types">
            <datalist id="sample_types">
                <option value="دم">
                <option value="لعاب">
                <option value="شعر">
                <option value="أنسجة">
            </datalist>
        </div>
        
        <div>
            <label>التصنيف *</label>
            <input type="text" name="category" value="<?php echo e($_SESSION['old_input']['category'] ?? ''); ?>" required list="categories">
            <datalist id="categories">
                <option value="جنائية">
                <option value="طب شرعي">
                <option value="طبية">
            </datalist>
        </div>
        
        <div>
            <label>اسم الشخص *</label>
            <input type="text" name="person_name" value="<?php echo e($_SESSION['old_input']['person_name'] ?? ''); ?>" required>
        </div>
        
        <div>
            <label>تاريخ الجمع *</label>
            <input type="date" name="collected_date" value="<?php echo e($_SESSION['old_input']['collected_date'] ?? date('Y-m-d')); ?>" required>
        </div>
        
        <div>
            <label>الموقع</label>
            <input type="text" name="location" value="<?php echo e($_SESSION['old_input']['location'] ?? ''); ?>">
        </div>
        
        <div>
            <label>RFID *</label>
            <select name="rfid_id" required>
                <option value="">اختر RFID...</option>
                <?php foreach ($rfidTags as $rfid): ?>
                <option value="<?php echo $rfid['id']; ?>" <?php echo (($_SESSION['old_input']['rfid_id'] ?? '') == $rfid['id']) ? 'selected' : ''; ?>>
                    <?php echo e($rfid['uid']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="grid-column:1/-1; display:flex; gap:10px;">
            <button class="btn btn-blue" type="submit">حفظ</button>
            <button class="btn btn-green" type="submit" name="add_another" value="1">حفظ وإضافة جديدة</button>
            <a href="<?php echo APP_URL; ?>/public/index.php?page=samples" class="btn btn-gray">قائمة العينات</a>
        </div>
    </form>
    <?php unset($_SESSION['old_input']); ?>
</div>

<div class="spacer"></div>

<div class="card">
    <h3>أحدث العينات</h3>
    <table>
        <thead>
            <tr>
                <th>رقم العينة</th>
                <th>النوع</th>
                <th>التاريخ</th>
                <th>الاسم</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentSamples)): ?>
                <tr><td colspan="5">لا توجد عينات بعد</td></tr>
            <?php else: ?>
                <?php foreach ($recentSamples as $sample): ?>
                <tr>
                    <td><a href="<?php echo APP_URL; ?>/public/index.php?page=sample_detail&sample_number=<?php echo urlencode($sample['sample_number']); ?>" style="color:var(--blue);"><?php echo e($sample['sample_number']); ?></a></td>
                    <td><?php echo e($sample['sample_type']); ?></td>
                    <td><?php echo formatDate($sample['collected_date']); ?></td>
                    <td><?php echo e($sample['person_name']); ?></td>
                    <td><span class="status <?php echo getStatusClass($sample['status']); ?>"><?php echo getStatusLabel($sample['status']); ?></span></td>
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
