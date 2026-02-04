<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/reports.php';
require_once __DIR__ . '/../config/lang.php';

require_login();

$reportType = $_GET['report_type'] ?? 'samples';
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');
$userId = trim($_GET['user_id'] ?? '');

 $users = db()->query('SELECT id, username FROM users ORDER BY username')->fetchAll();
 [$columns, $rows, $title] = build_report($reportType, $fromDate, $toDate, $userId);

$canExport = in_array((current_user()['role'] ?? ''), ['Admin', 'Operator'], true);
$statusLabels = [
  __('status.approved'),
  __('status.checked'),
  __('status.pending'),
  __('status.rejected'),
];

ob_start();
?>
<h1><?= e(__('reports.title')) ?></h1>

<div class="card">
  <h3><?= e(__('reports.filters')) ?></h3>
  <form method="get" class="filters" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
    <div>
      <label><?= e(__('reports.from_date')) ?></label>
      <input type="date" name="from_date" value="<?= e($fromDate) ?>">
    </div>
    <div>
      <label><?= e(__('reports.to_date')) ?></label>
      <input type="date" name="to_date" value="<?= e($toDate) ?>">
    </div>
    <div>
      <label><?= e(__('reports.type')) ?></label>
      <select name="report_type">
        <option value="samples" <?= $reportType === 'samples' ? 'selected' : '' ?>><?= e(__('reports.samples')) ?></option>
        <option value="rfid" <?= $reportType === 'rfid' ? 'selected' : '' ?>><?= e(__('reports.rfid')) ?></option>
        <option value="approval" <?= $reportType === 'approval' ? 'selected' : '' ?>><?= e(__('reports.approval')) ?></option>
        <option value="audit" <?= $reportType === 'audit' ? 'selected' : '' ?>><?= e(__('reports.audit')) ?></option>
      </select>
    </div>
    <div>
      <label><?= e(__('reports.user')) ?></label>
      <select name="user_id">
        <option value=""><?= e(__('samples.all')) ?></option>
        <?php foreach ($users as $u): ?>
          <option value="<?= (int)$u['id'] ?>" <?= (string)$userId === (string)$u['id'] ? 'selected' : '' ?>><?= e($u['username']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-blue" type="submit"><?= e(__('samples.search')) ?></button>
  </form>
</div>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h3><?= e($title) ?></h3>
    <div style="display:flex;gap:10px;">
      <?php if ($canExport): ?>
        <a class="btn btn-green" href="<?= e(url('/audit/export-excel?' . http_build_query($_GET))) ?>"><?= e(__('reports.export_excel')) ?></a>
        <a class="btn btn-red" href="<?= e(url('/audit/export-pdf?' . http_build_query($_GET))) ?>"><?= e(__('reports.export_pdf')) ?></a>
      <?php endif; ?>
      <button class="btn btn-gray" onclick="window.print()"><?= e(__('reports.print')) ?></button>
    </div>
  </div>

  <div class="spacer"></div>

  <table>
    <thead>
      <tr>
        <?php foreach ($columns as $col): ?>
          <th><?= e($col) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= count($columns) ?>"><?= e(__('reports.no_data')) ?></td></tr>
      <?php else: ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td>
                <?php if (in_array($cell, $statusLabels, true)): ?>
                  <span class="status <?= $cell === __('status.approved') ? 'approved' : ($cell === __('status.checked') ? 'checked' : ($cell === __('status.pending') ? 'pending' : 'rejected')) ?>"><?= e($cell) ?></span>
                <?php else: ?>
                  <?= e($cell) ?>
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
$title = __('reports.title');
$active = 'reports';
include __DIR__ . '/../partials/layout.php';
