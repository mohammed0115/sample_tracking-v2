<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    redirect(url('/rfid/list'));
}

$stmt = db()->prepare('SELECT id, uid, is_active FROM rfid_tags WHERE id = ?');
$stmt->execute([$id]);
$tag = $stmt->fetch();
if (!$tag) {
    redirect(url('/rfid/list'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['uid'] ?? '');
    
    if ($uid === '') {
        $error = __('error.required');
    } else {
        $stmt = db()->prepare('SELECT id FROM rfid_tags WHERE uid = ? AND id != ?');
        $stmt->execute([$uid, $id]);
        if ($stmt->fetch()) {
            $error = __('rfid.uid_exists');
        } else {
            $stmt = db()->prepare('UPDATE rfid_tags SET uid = ? WHERE id = ?');
            $stmt->execute([$uid, $id]);
            $success = __('success.saved');
        }
    }
}

ob_start();
?>
<h1><?= e(__('rfid.edit')) ?></h1>

<div class="card" style="max-width:600px;margin:0 auto;">
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div style="margin-bottom:16px;">
            <label><?= e(__('rfid.uid')) ?></label>
            <input type="text" name="uid" required value="<?= e($tag['uid']) ?>">
        </div>
        
        <div style="display:flex;gap:10px;">
            <button class="btn btn-blue" type="submit"><?= e(__('samples.save')) ?></button>
            <a href="<?= e(url('/rfid/list')) ?>" class="btn btn-gray"><?= e(__('auth.back')) ?></a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('rfid.edit');
$active = 'rfid';
include __DIR__ . '/../partials/layout.php';
