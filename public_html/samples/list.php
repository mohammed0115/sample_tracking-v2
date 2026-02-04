<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_login();

$query = trim($_GET['q'] ?? '');
$sampleType = trim($_GET['sample_type'] ?? '');
$category = trim($_GET['category'] ?? '');
$dateValue = trim($_GET['date'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

$where = [];
$params = [];
if ($sampleType !== '') {
    $where[] = 'sample_type = ?';
    $params[] = $sampleType;
}
if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
}
if ($dateValue !== '') {
    $where[] = 'collected_date = ?';
    $params[] = $dateValue;
}
if ($query !== '') {
    $where[] = '(sample_number LIKE ? OR person_name LIKE ?)';
    $params[] = '%' . $query . '%';
    $params[] = '%' . $query . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = db()->prepare("SELECT COUNT(*) FROM samples $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $pageSize));

$sql = "SELECT * FROM samples $whereSql ORDER BY collected_date DESC, id DESC LIMIT $pageSize OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$samples = $stmt->fetchAll();

$statusMap = [
    'pending' => __('status.pending'),
    'checked' => __('status.checked'),
    'approved' => __('status.approved'),
    'rejected' => __('status.rejected'),
];

ob_start();
?>
<h1><?= e(__('samples.list_title')) ?></h1>

<div class="card">
    <h3><?= e(__('samples.filters')) ?></h3>
    <form method="get" class="filters">
        <div>
            <label><?= e(__('samples.sample_type')) ?></label>
            <select name="sample_type">
                <option value=""><?= e(__('samples.all')) ?></option>
                <option value="دم" <?= $sampleType === 'دم' ? 'selected' : '' ?>>دم</option>
                <option value="أنسجة" <?= $sampleType === 'أنسجة' ? 'selected' : '' ?>>أنسجة</option>
            </select>
        </div>
        <div>
            <label><?= e(__('samples.category')) ?></label>
            <select name="category">
                <option value=""><?= e(__('samples.all')) ?></option>
                <option value="جنائية" <?= $category === 'جنائية' ? 'selected' : '' ?>>جنائية</option>
                <option value="طبية" <?= $category === 'طبية' ? 'selected' : '' ?>>طبية</option>
            </select>
        </div>
        <div>
            <label><?= e(__('samples.date')) ?></label>
            <input type="date" name="date" value="<?= e($dateValue) ?>">
        </div>
        <div>
            <label><?= e(__('samples.search')) ?></label>
            <input type="text" name="q" placeholder="<?= e(__('samples.search_placeholder')) ?>" value="<?= e($query) ?>">
        </div>
        <button class="btn btn-blue" type="submit"><?= e(__('samples.search')) ?></button>
    </form>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <h3><?= e(__('samples.list_title')) ?></h3>
        <a class="btn btn-green" href="<?= e(url('/audit/export-excel?' . http_build_query($_GET + ['report_type' => 'samples']))) ?>"><?= e(__('reports.export_excel')) ?></a>
    </div>
    <table>
        <thead>
            <tr>
                <th><?= e(__('samples.sample_number')) ?></th>
                <th><?= e(__('samples.person_name')) ?></th>
                <th><?= e(__('samples.sample_type')) ?></th>
                <th><?= e(__('samples.category')) ?></th>
                <th><?= e(__('samples.date')) ?></th>
                <th><?= e(__('samples.status')) ?></th>
                <th><?= e(__('samples.actions')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$samples): ?>
            <tr><td colspan="7"><?= e(__('samples.no_data')) ?></td></tr>
        <?php else: ?>
            <?php foreach ($samples as $sample): ?>
                <tr>
                    <td><?= e($sample['sample_number']) ?></td>
                    <td><?= e($sample['person_name']) ?></td>
                    <td><?= e($sample['sample_type']) ?></td>
                    <td><?= e($sample['category']) ?></td>
                    <td><?= e(date('Y/m/d', strtotime($sample['collected_date']))) ?></td>
                    <td>
                        <span class="status <?= e($sample['status']) ?>"><?= e($statusMap[$sample['status']] ?? $sample['status']) ?></span>
                    </td>
                    <td class="table-actions" style="display:flex;gap:8px;">
                        <a class="btn btn-sm btn-blue" href="<?= e(url('/samples/detail?sample_number=' . urlencode($sample['sample_number']))) ?>"><?= e(__('samples.view')) ?></a>
                        <a class="btn btn-sm btn-gray" href="<?= e(url('/samples/edit?sample_number=' . urlencode($sample['sample_number']))) ?>"><?= e(__('samples.edit')) ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a class="page" href="<?= e(url('/samples/list?' . http_build_query(array_merge($_GET, ['page' => $page - 1])))) ?>">‹</a>
        <?php endif; ?>
        <span class="page"><?= $page ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="page" href="<?= e(url('/samples/list?' . http_build_query(array_merge($_GET, ['page' => $page + 1])))) ?>">›</a>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = __('samples.list_title');
$active = 'samples';
include __DIR__ . '/../partials/layout.php';
