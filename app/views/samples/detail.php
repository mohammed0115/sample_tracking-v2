<?php
$pageTitle = 'تفاصيل العينة - ' . $sample['sample_number'];
ob_start();
?>

<h1>تفاصيل العينة</h1>

<div style="display:grid; grid-template-columns:1.2fr 2fr; gap:24px;">
    <div class="card">
        <h3>بيانات العينة</h3>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="border-bottom:1px solid var(--border); padding:10px 0;">
                <div class="muted" style="font-size:12px;">رقم العينة</div>
                <strong style="font-size:18px;"><?php echo e($sample['sample_number']); ?></strong>
            </div>
            <div style="border-bottom:1px solid var(--border); padding:10px 0;">
                <div class="muted" style="font-size:12px;">نوع العينة</div>
                <strong><?php echo e($sample['sample_type']); ?></strong>
            </div>
            <div style="border-bottom:1px solid var(--border); padding:10px 0;">
                <div class="muted" style="font-size:12px;">التصنيف</div>
                <strong><?php echo e($sample['category']); ?></strong>
            </div>
            <div style="border-bottom:1px solid var(--border); padding:10px 0;">
                <div class="muted" style="font-size:12px;">تاريخ الجمع</div>
                <strong><?php echo formatDate($sample['collected_date']); ?></strong>
            </div>
            <div style="border-bottom:1px solid var(--border); padding:10px 0;">
                <div class="muted" style="font-size:12px;">اسم الشخص</div>
                <strong><?php echo e($sample['person_name']); ?></strong>
            </div>
            <div style="padding:10px 0;">
                <div class="muted" style="font-size:12px;">الموقع</div>
                <strong><?php echo e($sample['location'] ?: '-'); ?></strong>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:24px; font-weight:700;"><?php echo e($sample['sample_number']); ?></div>
            <div class="status <?php echo getStatusClass($sample['status']); ?>" style="font-size:14px;">
                <?php echo getStatusLabel($sample['status']); ?>
            </div>
        </div>
        
        <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=sample_action" class="card rfid-box">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="sample_number" value="<?php echo e($sample['sample_number']); ?>">
            
            <strong>فحص العينة عبر RFID</strong>
            <div class="spacer"></div>
            
            <?php if ($canAct && $sample['status'] === 'pending'): ?>
                <button class="btn btn-blue" name="action" value="rfid_check">قراءة RFID</button>
            <?php else: ?>
                <button class="btn btn-blue" type="button" disabled>قراءة RFID</button>
            <?php endif; ?>
            
            <div class="muted" style="margin-top:10px;">
                <?php if (in_array($sample['status'], ['checked', 'approved'])): ?>
                    تمت قراءة RFID بنجاح (UID: <?php echo e($sample['rfid_uid']); ?>)
                <?php else: ?>
                    لم يتم فحص العينة بعد
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($canAct): ?>
        <form method="post" action="<?php echo APP_URL; ?>/public/index.php?page=sample_action" class="card">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="sample_number" value="<?php echo e($sample['sample_number']); ?>">
            
            <h3>إجراءات العينة</h3>
            <div style="display:flex; gap:10px; margin-top:12px;">
                <button class="btn btn-green" name="action" value="approve" <?php echo $sample['status'] !== 'checked' ? 'disabled' : ''; ?>>اعتماد</button>
                <button class="btn btn-red" name="action" value="reject">رفض</button>
                <a href="<?php echo APP_URL; ?>/public/index.php?page=samples" class="btn btn-gray">رجوع</a>
            </div>
        </form>
        <?php endif; ?>
        
        <div class="card">
            <h3>سجل التدقيق</h3>
            <div class="log">
                <?php if (empty($logs)): ?>
                    <div>لا توجد سجلات بعد</div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <div>• <?php echo e($log['action']); ?> — <?php echo formatDate($log['timestamp'], DATETIME_FORMAT); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
