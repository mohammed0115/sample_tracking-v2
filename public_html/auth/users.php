<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_role(['Admin']);

$rows = db()->query('SELECT id, username, email, first_name, last_name, role, is_active FROM users ORDER BY username')->fetchAll();

ob_start();
?>
<h1><?= e(__('users.manage')) ?></h1>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3><?= e(__('users.list')) ?></h3>
        <a class="btn btn-blue" href="<?= e(url('/auth/users/create')) ?>"><?= e(__('users.add')) ?></a>
    </div>
    <table>
        <thead>
        <tr>
            <th><?= e(__('auth.username')) ?></th>
            <th><?= e(__('auth.email')) ?></th>
            <th><?= e(__('users.name')) ?></th>
            <th><?= e(__('users.role')) ?></th>
            <th><?= e(__('users.status')) ?></th>
            <th><?= e(__('users.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['username']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e(trim($row['first_name'] . ' ' . $row['last_name'])) ?></td>
                <td><?= e($row['role']) ?></td>
                <td><?= (int)$row['is_active'] === 1 ? e(__('users.active')) : e(__('users.inactive')) ?></td>
                <td class="table-actions" style="display:flex;gap:8px;justify-content:center;">
                    <a class="btn btn-sm btn-blue" href="<?= e(url('/auth/users/edit?id=' . (int)$row['id'])) ?>"><?= e(__('samples.edit')) ?></a>
                    <a class="btn btn-sm btn-gray" href="<?= e(url('/auth/users/toggle?id=' . (int)$row['id'])) ?>"><?= e(__('users.toggle')) ?></a>
                    <a class="btn btn-sm btn-red" href="<?= e(url('/auth/users/reset?id=' . (int)$row['id'])) ?>"><?= e(__('users.reset_password')) ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = __('users.manage');
$active = 'users';
include __DIR__ . '/../partials/layout.php';
