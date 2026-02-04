<?php
$pageTitle = 'قائمة العينات';
ob_start();
?>

<h1>قائمة العينات</h1>

<div class="card">
    <h3>فلترة العينات</h3>
    <form method="get" action="<?php echo APP_URL; ?>/public/index.php" class="filters">
        <input type="hidden" name="page" value="samples">
        
        <div>
            <label>نوع العينة</label>
            <select name="sample_type">
                <option value="">الكل</option>
                <option value="دم" <?php echo $filters['sample_type'] === 'دم' ? 'selected' : ''; ?>>دم</option>
                <option value="لعاب" <?php echo $filters['sample_type'] === 'لعاب' ? 'selected' : ''; ?>>لعاب</option>
                <option value="شعر" <?php echo $filters['sample_type'] === 'شعر' ? 'selected' : ''; ?>>شعر</option>
                <option value="أنسجة" <?php echo $filters['sample_type'] === 'أنسجة' ? 'selected' : ''; ?>>أنسجة</option>
            </select>
        </div>
        
        <div>
            <label>التصنيف</label>
            <select name="category">
                <option value="">الكل</option>
                <option value="جنائية" <?php echo $filters['category'] === 'جنائية' ? 'selected' : ''; ?>>جنائية</option>
                <option value="طب شرعي" <?php echo $filters['category'] === 'طب شرعي' ? 'selected' : ''; ?>>طب شرعي</option>
                <option value="طبية" <?php echo $filters['category'] === 'طبية' ? 'selected' : ''; ?>>طبية</option>
            </select>
        </div>
        
        <div>
            <label>التاريخ</label>
            <input type="date" name="date" value="<?php echo e($filters['date']); ?>">
        </div>
        
        <div>
            <label>بحث</label>
            <input type="text" name="q" placeholder="رقم العينة أو الاسم" value="<?php echo e($filters['search']); ?>">
        </div>
        
        <button class="btn btn-blue" type="submit">بحث</button>
    </form>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <h3>قائمة العينات (<?php echo number_format($pagination['total']); ?>)</h3>
        <a class="btn btn-green" href="<?php echo APP_URL; ?>/public/index.php?page=export_samples<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>">تصدير CSV</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>رقم العينة</th>
                <th>اسم الشخص</th>
                <th>النوع</th>
                <th>التصنيف</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($samples)): ?>
                <tr><td colspan="7">لا توجد عينات</td></tr>
            <?php else: ?>
                <?php foreach ($samples as $sample): ?>
                <tr>
                    <td><?php echo e($sample['sample_number']); ?></td>
                    <td><?php echo e($sample['person_name']); ?></td>
                    <td><?php echo e($sample['sample_type']); ?></td>
                    <td><?php echo e($sample['category']); ?></td>
                    <td><?php echo formatDate($sample['collected_date']); ?></td>
                    <td><span class="status <?php echo getStatusClass($sample['status']); ?>"><?php echo getStatusLabel($sample['status']); ?></span></td>
                    <td>
                        <a class="btn btn-blue" href="<?php echo APP_URL; ?>/public/index.php?page=sample_detail&sample_number=<?php echo urlencode($sample['sample_number']); ?>">عرض</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['has_prev']): ?>
            <a class="page" href="?page=samples&<?php echo http_build_query(array_merge($filters, ['page' => $pagination['prev_page']])); ?>">‹</a>
        <?php endif; ?>
        
        <span class="page active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
        
        <?php if ($pagination['has_next']): ?>
            <a class="page" href="?page=samples&<?php echo http_build_query(array_merge($filters, ['page' => $pagination['next_page']])); ?>">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
