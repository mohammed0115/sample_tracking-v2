<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['uid'] ?? '');
    
    if ($uid === '') {
        $error = __('error.required');
    } else {
        $stmt = db()->prepare('SELECT id FROM rfid_tags WHERE uid = ?');
        $stmt->execute([$uid]);
        if ($stmt->fetch()) {
            $error = __('rfid.uid_exists');
        } else {
            $stmt = db()->prepare('INSERT INTO rfid_tags (uid, is_active) VALUES (?, ?)');
            $stmt->execute([$uid, 1]);
            $success = __('rfid.created_success');
            
            if (isset($_POST['add_another'])) {
                header('Location: ' . url('/rfid/create'));
                exit;
            }
            header('Location: ' . url('/rfid/list'));
            exit;
        }
    }
}

ob_start();
?>
<h1><?= e(__('rfid.add')) ?></h1>

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
            <input type="text" name="uid" required placeholder="مثال: E2008050F0040520">
        </div>
        
        <div style="display:flex;gap:10px;">
            <button class="btn btn-blue" type="submit"><?= e(__('samples.save')) ?></button>
            <button class="btn btn-green" type="submit" name="add_another"><?= e(__('samples.save_add')) ?></button>
            <a href="<?= e(url('/rfid/list')) ?>" class="btn btn-gray"><?= e(__('auth.back')) ?></a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = __('rfid.add');
$active = 'rfid';
include __DIR__ . '/../partials/layout.php';
