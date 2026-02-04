<?php
$pageTitle = 'التقارير';
ob_start();
?>

<h1>التقارير</h1>

<div class="card">
    <h3>فلترة التقارير</h3>
    <form method="get" action="<?php echo APP_URL; ?>/public/index.php" class="filters" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
        <input type="hidden" name="page" value="reports">
        
        <div>
            <label>من تاريخ</label>
            <input type="date" name="from_date" value="<?php echo e($fromDate); ?>">
        </div>
        
        <div>
            <label>إلى تاريخ</label>
            <input type="date" name="to_date" value="<?php echo e($toDate); ?>">
        </div>
        
        <div>
            <label>نوع التقرير</label>
            <select name="report_type">
                <option value="samples" <?php echo $reportType === 'samples' ? 'selected' : ''; ?>>تقرير العينات</option>
                <option value="rfid" <?php echo $reportType === 'rfid' ? 'selected' : ''; ?>>تقرير فحص RFID</option>
                <option value="approval" <?php echo $reportType === 'approval' ? 'selected' : ''; ?>>تقرير الاعتماد</option>
                <option value="audit" <?php echo $reportType === 'audit' ? 'selected' : ''; ?>>تقرير النشاط</option>
            </select>
        </div>
        
        <div>
            <label>المستخدم</label>
            <select name="user_id">
                <option value="">الكل</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo $userId == $u['id'] ? 'selected' : ''; ?>><?php echo e($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button class="btn btn-blue" type="submit">بحث</button>
    </form>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3><?php echo e($report['title']); ?></h3>
        <div style="display:flex; gap:10px;">
            <?php if (isOperatorOrAdmin()): ?>
                <a class="btn btn-green" href="<?php echo APP_URL; ?>/public/index.php?page=export_report&<?php echo $_SERVER['QUERY_STRING'] ?? ''; ?>">تصدير CSV</a>
            <?php endif; ?>
            <button class="btn btn-gray" onclick="window.print()">طباعة</button>
        </div>
    </div>
    
    <div class="spacer"></div>
    
    <table>
        <thead>
            <tr>
                <?php foreach ($report['columns'] as $col): ?>
                    <th><?php echo e($col); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($report['rows'])): ?>
                <tr><td colspan="<?php echo count($report['columns']); ?>">لا توجد بيانات</td></tr>
            <?php else: ?>
                <?php foreach ($report['rows'] as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <?php if (in_array($key, $report['status_columns'])): ?>
                                <span class="status <?php echo getStatusClass(strtolower($value)); ?>">
                                    <?php echo e($value); ?>
                                </span>
                            <?php else: ?>
                                <?php echo e($value); ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
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
