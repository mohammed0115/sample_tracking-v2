<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$rows = db()->query('SELECT id, uid, is_active FROM rfid_tags ORDER BY uid')->fetchAll();

ob_start();
?>
<h1><?= e(__('rfid.manage')) ?></h1>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3><?= e(__('rfid.list')) ?></h3>
        <a class="btn btn-blue" href="<?= e(url('/rfid/create')) ?>"><?= e(__('rfid.add')) ?></a>
    </div>
    <table>
        <thead>
        <tr>
            <th><?= e(__('rfid.uid')) ?></th>
            <th><?= e(__('rfid.status')) ?></th>
            <th><?= e(__('rfid.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['uid']) ?></td>
                <td><?= (int)$row['is_active'] === 1 ? e(__('rfid.active')) : e(__('rfid.inactive')) ?></td>
                <td class="table-actions" style="display:flex;gap:8px;justify-content:center;">
                    <a class="btn btn-sm btn-blue" href="<?= e(url('/rfid/edit?id=' . (int)$row['id'])) ?>"><?= e(__('rfid.edit')) ?></a>
                    <a class="btn btn-sm btn-gray" href="<?= e(url('/rfid/toggle?id=' . (int)$row['id'])) ?>"><?= (int)$row['is_active'] === 1 ? e(__('rfid.deactivate')) : e(__('rfid.activate')) ?></a>
                    <a class="btn btn-sm btn-red" href="<?= e(url('/rfid/delete?id=' . (int)$row['id'])) ?>" onclick="return confirm('<?= e(__('rfid.confirm_delete')) ?>')"><?= e(__('rfid.delete')) ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = __('rfid.manage');
$active = 'rfid';
include __DIR__ . '/../partials/layout.php';
