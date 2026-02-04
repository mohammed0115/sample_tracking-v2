<?php
$pageTitle = 'إضافة عينة';
ob_start();
?>

<h1>إضافة عينة جديدة</h1>

<div class="card" style="max-width:800px; margin:20px auto;">
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
            <a href="<?php echo APP_URL; ?>/public/index.php?page=samples" class="btn btn-gray">رجوع</a>
        </div>
    </form>
    <?php unset($_SESSION['old_input']); ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
