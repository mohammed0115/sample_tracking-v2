<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

function generate_sample_number(): string {
    $date = date('Ymd');
    $stmt = db()->prepare("SELECT sample_number FROM samples WHERE sample_number LIKE ? ORDER BY sample_number DESC LIMIT 1");
    $stmt->execute([$date . '%']);
    $lastSample = $stmt->fetch();
    if ($lastSample && isset($lastSample['sample_number'])) {
        $lastNumber = (int)substr($lastSample['sample_number'], -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }
    return $date . $newNumber;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sampleNumber = trim($_POST['sample_number'] ?? '');
    if ($sampleNumber === '') {
        $sampleNumber = generate_sample_number();
    }
    $sampleType = trim($_POST['sample_type'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $personName = trim($_POST['person_name'] ?? '');
    $collectedDate = trim($_POST['collected_date'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $rfidId = (int)($_POST['rfid_id'] ?? 0);

    if ($sampleNumber === '' || $sampleType === '' || $category === '' || $personName === '' || $collectedDate === '' || $location === '' || $rfidId === 0) {
        $error = __('error.required');
    } else {
        $stmt = db()->prepare('SELECT id FROM samples WHERE sample_number = ?');
        $stmt->execute([$sampleNumber]);
        if ($stmt->fetch()) {
            $error = __('error.sample_exists');
        } else {
            $stmt = db()->prepare('SELECT id FROM samples WHERE rfid_id = ?');
            $stmt->execute([$rfidId]);
            if ($stmt->fetch()) {
                $error = __('error.rfid_used');
            } else {
                $stmt = db()->prepare('INSERT INTO samples (sample_number, sample_type, category, person_name, collected_date, location, rfid_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$sampleNumber, $sampleType, $category, $personName, $collectedDate, $location, $rfidId, 'pending']);
                $newSampleId = (int)db()->lastInsertId();
                audit_log((int)current_user()['id'], $newSampleId, 'إضافة عينة جديدة');
                $success = 'تم حفظ العينة بنجاح';

                if (isset($_POST['add_another'])) {
                    redirect(url('/samples/add'));
                }
                redirect(url('/samples/list'));
            }
        }
    }
}

$recent = db()->query('SELECT * FROM samples ORDER BY collected_date DESC, id DESC LIMIT 5')->fetchAll();
$rfidTags = db()->query('SELECT r.id, r.uid FROM rfid_tags r LEFT JOIN samples s ON s.rfid_id = r.id WHERE r.is_active = 1 AND s.id IS NULL ORDER BY r.uid')->fetchAll();
$generatedSampleNumber = generate_sample_number();

$statusMap = [
    'pending' => __('status.pending'),
    'checked' => __('status.checked'),
    'approved' => __('status.approved'),
    'rejected' => __('status.rejected'),
];

ob_start();
?>
<h1><?= e(__('samples.add_title')) ?></h1>

<div class="grid-3">
    <div class="action-card green" id="openAddModal" style="cursor:pointer;"><?= e(__('samples.add_new')) ?> <span>＋</span></div>
    <a class="action-card blue" href="<?= e(url('/samples/list')) ?>" style="color:#fff;text-decoration:none;">
        <?= e(__('samples.list_title')) ?>
    </a>
    <a class="action-card blue" href="<?= e(url('/audit/reports')) ?>" style="color:#fff;text-decoration:none;">
        <?= e(__('reports.title')) ?>
    </a>
</div>

<div class="spacer"></div>

<div class="grid-2">
    <div class="card">
        <h3><?= e(__('samples.dashboard_stats')) ?></h3>
        <div class="stat-card"><span>المتوسط اليومي</span><strong>5.4</strong></div>
        <div class="stat-card"><span>المتوسط الأسبوعي</span><strong>2.1</strong></div>
        <div class="stat-card"><span>عينات اليوم</span><strong>118</strong></div>
    </div>
    <div class="card">
        <h3><?= e(__('samples.summary')) ?></h3>
        <div class="stat-card"><span>دم</span><strong>41</strong></div>
        <div class="stat-card"><span>أنسجة</span><strong>27</strong></div>
        <div class="stat-card"><span>جنائية</span><strong>18</strong></div>
    </div>
</div>

<div class="spacer"></div>

<div class="grid-main">
    <div class="card">
        <h3><?= e(__('samples.indicators')) ?></h3>
        <div class="stat-card"><span><?= e(__('samples.most_type')) ?></span><strong>دم</strong></div>
        <div class="stat-card"><span><?= e(__('samples.top_category')) ?></span><strong>جنائية</strong></div>
        <div class="stat-card"><span><?= e(__('samples.pending_count')) ?></span><strong>8</strong></div>
    </div>

    <div class="card">
        <div id="addMessage">
            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success"><?= e($success) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="spacer"></div>

<div class="card">
    <h3><?= e(__('samples.latest')) ?></h3>
    <table>
        <thead>
        <tr>
            <th><?= e(__('samples.sample_number')) ?></th>
            <th><?= e(__('samples.sample_type')) ?></th>
            <th><?= e(__('samples.date')) ?></th>
            <th><?= e(__('samples.person_name')) ?></th>
            <th><?= e(__('samples.status')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$recent): ?>
            <tr><td colspan="5"><?= e(__('samples.no_data')) ?></td></tr>
        <?php else: ?>
            <?php foreach ($recent as $sample): ?>
                <tr>
                    <td><?= e($sample['sample_number']) ?></td>
                    <td><?= e($sample['sample_type']) ?></td>
                    <td><?= e($sample['collected_date']) ?></td>
                    <td><?= e($sample['person_name']) ?></td>
                    <td><span class="status <?= e($sample['status']) ?>"><?= e($statusMap[$sample['status']] ?? $sample['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
<div class="spacer"></div>

<div class="card">
    <h3><?= e(__('samples.latest')) ?></h3>
    <table>
        <thead>
        <tr>
            <th><?= e(__('samples.sample_number')) ?></th>
            <th><?= e(__('samples.sample_type')) ?></th>
            <th><?= e(__('samples.date')) ?></th>
            <th><?= e(__('samples.person_name')) ?></th>
            <th><?= e(__('samples.status')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$recent): ?>
            <tr><td colspan="5"><?= e(__('samples.no_data')) ?></td></tr>
        <?php else: ?>
            <?php foreach ($recent as $sample): ?>
                <tr>
                    <td><?= e($sample['sample_number']) ?></td>
                    <td><?= e($sample['sample_type']) ?></td>
                    <td><?= e($sample['collected_date']) ?></td>
                    <td><?= e($sample['person_name']) ?></td>
                    <td><span class="status <?= e($sample['status']) ?>"><?= e($statusMap[$sample['status']] ?? $sample['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="addSampleModal">
    <div class="modal-card" style="min-width:500px;">
        <h3><?= e(__('samples.add_title')) ?></h3>
        <form method="post" class="grid-2">
            <div>
                <label><?= e(__('samples.sample_number')) ?></label>
                <input type="text" name="sample_number" value="<?= e($generatedSampleNumber) ?>" readonly>
            </div>
            <div>
                <label><?= e(__('samples.collected_date')) ?></label>
                <input type="date" name="collected_date" required>
            </div>
            <div>
                <label><?= e(__('samples.person_name')) ?></label>
                <input type="text" name="person_name" required>
            </div>
            <div>
                <label><?= e(__('samples.category')) ?></label>
                <input type="text" name="category" required>
            </div>
            <div>
                <label><?= e(__('samples.location')) ?></label>
                <input type="text" name="location" required>
            </div>
            <div>
                <label>RFID</label>
                <select name="rfid_id" required>
                    <option value="">اختر</option>
                    <?php foreach ($rfidTags as $tag): ?>
                        <option value="<?= (int)$tag['id'] ?>"><?= e($tag['uid']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label><?= e(__('samples.sample_type')) ?></label>
                <input type="text" name="sample_type" required>
            </div>
            <div style="grid-column:1/-1;display:flex;gap:10px;">
                <button class="btn btn-blue" type="submit"><?= e(__('samples.save')) ?></button>
                <button class="btn btn-green" type="submit" name="add_another"><?= e(__('samples.save_add')) ?></button>
                <button type="button" class="btn btn-gray" onclick="closeAddModal()"><?= e(__('auth.back')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = __('samples.add_title');
$active = 'add';
$extra_js = '
<script>
const addModal = document.getElementById("addSampleModal");
const openBtn = document.getElementById("openAddModal");

openBtn.addEventListener("click", function() {
    addModal.classList.add("show");
});

function closeAddModal() {
    addModal.classList.remove("show");
}

addModal.addEventListener("click", function(e) {
    if (e.target === addModal) {
        closeAddModal();
    }
});

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && addModal.classList.contains("show")) {
        closeAddModal();
    }
});
</script>
';
include __DIR__ . '/../partials/layout.php';
