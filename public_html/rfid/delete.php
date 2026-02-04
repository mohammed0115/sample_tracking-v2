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

$stmt = db()->prepare('SELECT rfid_id FROM samples WHERE rfid_id = ?');
$stmt->execute([$id]);
if ($stmt->fetch()) {
    $_SESSION['rfid_error'] = __('rfid.cannot_delete_used');
    redirect(url('/rfid/list'));
}

$stmt = db()->prepare('DELETE FROM rfid_tags WHERE id = ?');
$stmt->execute([$id]);

redirect(url('/rfid/list'));
