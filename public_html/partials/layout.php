<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/lang.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? __('app.title')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/style.css')) ?>">
    <?php if (!empty($extra_css)) echo $extra_css; ?>
</head>
<body>
<div class="layout">
    <?php if ($user): ?>
    <aside class="sidebar">
        <div class="avatar" id="avatarMenu">
            <?php if (!empty($user['avatar'])): ?>
                <img class="avatar-img" alt="avatar" src="<?= e(url('/' . $user['avatar'])) ?>" style="object-fit:cover;">
            <?php else: ?>
                <img class="avatar-img" alt="avatar" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'><rect width='80' height='80' fill='%23e0e7ff'/><circle cx='40' cy='30' r='14' fill='%236474ff'/><path d='M14 72c6-16 19-24 26-24s20 8 26 24' fill='%236474ff'/></svg>">
            <?php endif; ?>
            <div class="avatar-name"><?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? '')) ?></div>
            <div class="avatar-menu">
                <a href="<?= e(url('/auth/profile')) ?>"><?= e(__('nav.profile')) ?></a>
                <button type="button" id="openLangModal"><?= e(__('nav.change_language')) ?></button>
                <a href="<?= e(url('/auth/logout')) ?>"><?= e(__('nav.logout')) ?></a>
            </div>
        </div>
        <h3><?= e(__('nav.dashboard')) ?></h3>
        <a href="<?= e(url('/samples/add')) ?>" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i><?= e(__('nav.dashboard')) ?></a>
        <a href="<?= e(url('/samples/list')) ?>" class="<?= ($active ?? '') === 'samples' ? 'active' : '' ?>"><i class="fas fa-list"></i><?= e(__('nav.samples')) ?></a>
        <a href="<?= e(url('/samples/add')) ?>" class="<?= ($active ?? '') === 'add' ? 'active' : '' ?>"><i class="fas fa-plus"></i><?= e(__('nav.add_sample')) ?></a>
        <a href="<?= e(url('/rfid/checker')) ?>" class="<?= ($active ?? '') === 'checker' ? 'active' : '' ?>"><i class="fas fa-check-circle"></i><?= e(__('nav.checker')) ?></a>
        <a href="<?= e(url('/rfid/forensic')) ?>" class="<?= ($active ?? '') === 'forensic' ? 'active' : '' ?>"><i class="fas fa-fingerprint"></i><?= e(__('nav.forensic')) ?></a>
        <a href="<?= e(url('/rfid/warehouse')) ?>" class="<?= ($active ?? '') === 'warehouse' ? 'active' : '' ?>"><i class="fas fa-warehouse"></i><?= e(__('nav.warehouse')) ?></a>
        <a href="<?= e(url('/audit/reports')) ?>" class="<?= ($active ?? '') === 'reports' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i><?= e(__('nav.reports')) ?></a>
        <?php if (($user['role'] ?? '') === 'Admin'): ?>
            <a href="<?= e(url('/auth/users')) ?>" class="<?= ($active ?? '') === 'users' ? 'active' : '' ?>"><i class="fas fa-users"></i><?= e(__('nav.users')) ?></a>
            <a href="<?= e(url('/rfid/list')) ?>" class="<?= ($active ?? '') === 'rfid' ? 'active' : '' ?>"><i class="fas fa-tag"></i><?= e(__('nav.rfid')) ?></a>
        <?php endif; ?>
        <a href="<?= e(url('/auth/logout')) ?>"><i class="fas fa-sign-out-alt"></i><?= e(__('nav.logout')) ?></a>
    </aside>
    <?php endif; ?>
    <main class="main" style="width:100%;">
        <?= $content ?? '' ?>
        <div class="spacer"></div>
        <div class="muted" style="text-align:center;font-size:12px;padding:10px 0;"><?= e(__('footer.copyright')) ?></div>
    </main>
</div>

<div class="modal" id="langModal">
    <div class="modal-card">
        <h3><?= e(__('lang.select')) ?></h3>
        <div style="display:flex;gap:12px;margin-top:16px;">
            <a class="btn btn-blue" style="width:100%;text-align:center;" href="<?= e(url('?lang=ar')) ?>"><?= e(__('lang.ar')) ?></a>
            <a class="btn btn-blue" style="width:100%;text-align:center;" href="<?= e(url('?lang=en')) ?>"><?= e(__('lang.en')) ?></a>
        </div>
    </div>
</div>
<?php if (!empty($extra_js)) echo $extra_js; ?>
<script>
    const avatarMenu = document.getElementById('avatarMenu');
    if (avatarMenu) {
        const menu = avatarMenu.querySelector('.avatar-menu');
        avatarMenu.addEventListener('click', function (e) {
            if (menu && menu.contains(e.target)) {
                return;
            }
            e.stopPropagation();
            avatarMenu.classList.toggle('open');
        });
        if (menu) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }
        document.addEventListener('click', function () {
            avatarMenu.classList.remove('open');
        });
    }

    const openLangModal = document.getElementById('openLangModal');
    const langModal = document.getElementById('langModal');
    if (openLangModal && langModal) {
        openLangModal.addEventListener('click', function (e) {
            e.stopPropagation();
            langModal.classList.add('show');
        });
        langModal.addEventListener('click', function (e) {
            if (e.target === langModal) {
                langModal.classList.remove('show');
            }
        });
    }
</script>
</body>
</html>
