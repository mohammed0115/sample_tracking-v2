<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_login();

$sampleNumber = trim($_GET['sample_number'] ?? '');
if ($sampleNumber === '') {
    http_response_code(400);
    echo e(__('error.sample_missing'));
    exit;
}

$stmt = db()->prepare('SELECT s.*, r.uid AS rfid_uid FROM samples s JOIN rfid_tags r ON r.id = s.rfid_id WHERE s.sample_number = ?');
$stmt->execute([$sampleNumber]);
$sample = $stmt->fetch();
if (!$sample) {
    http_response_code(404);
    echo e(__('error.sample_not_found'));
    exit;
}

$canAct = in_array((current_user()['role'] ?? ''), ['Admin', 'Operator'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canAct) {
        http_response_code(403);
        echo e(__('error.forbidden'));
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'rfid_check' && $sample['status'] === 'pending') {
        $stmt = db()->prepare('UPDATE samples SET status = ? WHERE id = ?');
        $stmt->execute(['checked', $sample['id']]);
        audit_log((int)current_user()['id'], (int)$sample['id'], 'فحص RFID (UID: ' . $sample['rfid_uid'] . ')');
    } elseif ($action === 'approve' && $sample['status'] === 'checked') {
        $stmt = db()->prepare('UPDATE samples SET status = ? WHERE id = ?');
        $stmt->execute(['approved', $sample['id']]);
        audit_log((int)current_user()['id'], (int)$sample['id'], 'اعتماد العينة');
    } elseif ($action === 'reject' && in_array($sample['status'], ['pending', 'checked', 'approved'], true)) {
        $stmt = db()->prepare('UPDATE samples SET status = ? WHERE id = ?');
        $stmt->execute(['rejected', $sample['id']]);
        audit_log((int)current_user()['id'], (int)$sample['id'], 'رفض العينة');
    }

    redirect(url('/samples/detail?sample_number=' . urlencode($sampleNumber)));
}

$logStmt = db()->prepare('SELECT action, timestamp FROM audit_logs WHERE sample_id = ? ORDER BY timestamp DESC LIMIT 10');
$logStmt->execute([(int)$sample['id']]);
$logs = $logStmt->fetchAll();

$statusMap = [
    'pending' => __('status.pending'),
    'checked' => __('status.checked'),
    'approved' => __('status.approved'),
    'rejected' => __('status.rejected'),
];

ob_start();
?>
<h1><?= e(__('samples.detail_title')) ?></h1>

<div class="grid-main">
    <div class="card">
        <h3><?= e(__('samples.detail_title')) ?></h3>
        <div class="info-item"><span><?= e(__('samples.sample_type')) ?></span><strong><?= e($sample['sample_type']) ?></strong></div>
        <div class="info-item"><span><?= e(__('samples.category')) ?></span><strong><?= e($sample['category']) ?></strong></div>
        <div class="info-item"><span><?= e(__('samples.collected_date')) ?></span><strong><?= e($sample['collected_date']) ?></strong></div>
        <div class="info-item"><span><?= e(__('samples.person_name')) ?></span><strong><?= e($sample['person_name']) ?></strong></div>
        <div class="info-item"><span><?= e(__('samples.location')) ?></span><strong><?= e($sample['location']) ?></strong></div>
    </div>

    <div>
        <div class="card" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="sample-no"><?= e($sample['sample_number']) ?></div>
            <div class="status <?= e($sample['status']) ?>"><?= e($statusMap[$sample['status']] ?? $sample['status']) ?></div>
        </div>

        <form method="post" class="card rfid-box">
            <strong><?= e(__('rfid.title')) ?></strong>
            <div class="spacer"></div>
            <?php if ($canAct && $sample['status'] === 'pending'): ?>
                <button class="btn btn-blue" name="action" value="rfid_check"><?= e(__('rfid.read')) ?></button>
            <?php else: ?>
                <button class="btn btn-blue" type="button" disabled><?= e(__('rfid.read')) ?></button>
            <?php endif; ?>
            <div class="muted" style="margin-top:10px;">
                <?php if (in_array($sample['status'], ['checked', 'approved'], true)): ?>
                    <?= e(__('rfid.read_done', ['uid' => $sample['rfid_uid']])) ?>
                <?php else: ?>
                    <?= e(__('rfid.not_read')) ?>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($canAct): ?>
        <form method="post" class="card">
            <h3><?= e(__('actions.title')) ?></h3>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-green" name="action" value="approve" <?= $sample['status'] !== 'checked' ? 'disabled' : '' ?>><?= e(__('actions.approve')) ?></button>
                <button class="btn btn-red" name="action" value="reject"><?= e(__('actions.reject')) ?></button>
                <a href="<?= e(url('/samples/list')) ?>" class="btn btn-gray"><?= e(__('auth.back')) ?></a>
            </div>
        </form>
        <?php endif; ?>

        <div class="card">
            <h3><?= e(__('audit.title')) ?></h3>
            <div class="log">
                <?php if (!$logs): ?>
                    <div><?= e(__('audit.empty')) ?></div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <div>• <?= e($log['action']) ?> — <?= e(date('Y/m/d H:i', strtotime($log['timestamp']))) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = __('samples.detail_title');
$active = 'samples';
include __DIR__ . '/../partials/layout.php';
