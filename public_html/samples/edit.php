<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin', 'Operator']);

$sampleNumber = trim($_GET['sample_number'] ?? '');
if ($sampleNumber === '') {
    redirect(url('/samples/list'));
}

$stmt = db()->prepare('SELECT s.*, r.id AS rfid_id, r.uid AS rfid_uid FROM samples s JOIN rfid_tags r ON r.id = s.rfid_id WHERE s.sample_number = ?');
$stmt->execute([$sampleNumber]);
$sample = $stmt->fetch();
if (!$sample) {
    redirect(url('/samples/list'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sampleType = trim($_POST['sample_type'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $personName = trim($_POST['person_name'] ?? '');
    $collectedDate = trim($_POST['collected_date'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($sampleType === '' || $category === '' || $personName === '' || $collectedDate === '' || $location === '') {
        $error = __('error.required');
    } else {
        $stmt = db()->prepare('UPDATE samples SET sample_type = ?, category = ?, person_name = ?, collected_date = ?, location = ? WHERE sample_number = ?');
        $stmt->execute([$sampleType, $category, $personName, $collectedDate, $location, $sampleNumber]);
        audit_log((int)current_user()['id'], (int)$sample['id'], 'تعديل العينة');
        $success = __('success.saved');
        
        $stmt = db()->prepare('SELECT s.*, r.id AS rfid_id, r.uid AS rfid_uid FROM samples s JOIN rfid_tags r ON r.id = s.rfid_id WHERE s.sample_number = ?');
        $stmt->execute([$sampleNumber]);
        $sample = $stmt->fetch();
    }
}

$rfidTags = db()->query('SELECT r.id, r.uid FROM rfid_tags r WHERE r.is_active = 1 ORDER BY r.uid')->fetchAll();

ob_start();
?>
<h1><?= e(__('samples.edit')) ?></h1>

<div class="card" style="max-width:700px;margin:0 auto;">
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" class="grid-2">
        <div>
            <label><?= e(__('samples.sample_number')) ?></label>
            <input type="text" value="<?= e($sample['sample_number']) ?>" readonly>
        </div>
        <div>
            <label><?= e(__('samples.collected_date')) ?></label>
            <input type="date" name="collected_date" value="<?= e($sample['collected_date']) ?>" required>
        </div>
        <div>
            <label><?= e(__('samples.person_name')) ?></label>
            <input type="text" name="person_name" value="<?= e($sample['person_name']) ?>" required>
        </div>
        <div>
            <label><?= e(__('samples.category')) ?></label>
            <input type="text" name="category" value="<?= e($sample['category']) ?>" required>
        </div>
        <div>
            <label><?= e(__('samples.location')) ?></label>
            <input type="text" name="location" value="<?= e($sample['location']) ?>" required>
        </div>
        <div>
            <label>RFID</label>
            <select disabled>
                <option selected><?= e($sample['rfid_uid']) ?></option>
            </select>
        </div>
        <div>
            <label><?= e(__('samples.sample_type')) ?></label>
            <input type="text" name="sample_type" value="<?= e($sample['sample_type']) ?>" required>
        </div>
        <div>
            <label><?= e(__('samples.status')) ?></label>
            <input type="text" value="<?= e($sample['status']) ?>" readonly>
        </div>
        <div style="grid-column:1/-1;display:flex;gap:10px;">
            <button class="btn btn-blue" type="submit"><?= e(__('samples.save')) ?></button>
            <a href="<?= e(url('/samples/list')) ?>" class="btn btn-gray"><?= e(__('auth.back')) ?></a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('samples.edit');
$active = 'samples';
include __DIR__ . '/../partials/layout.php';
