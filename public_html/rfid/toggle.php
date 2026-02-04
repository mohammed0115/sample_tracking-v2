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

$stmt = db()->prepare('UPDATE rfid_tags SET is_active = NOT is_active WHERE id = ?');
$stmt->execute([$id]);

redirect(url('/rfid/list'));
