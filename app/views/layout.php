<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>نظام تتبع العينات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
      --blue:#4f8df7;
      --blue-2:#3b82f6;
      --green:#22c55e;
      --green-2:#34b399;
      --red:#ef4444;
      --bg:#f4f6fb;
      --card:#ffffff;
      --text:#1f2937;
      --muted:#6b7280;
      --border:#e5e7eb;
      --radius:16px;
    }
    * {box-sizing:border-box; font-family:"Cairo",sans-serif}
    body {margin:0; background:var(--bg); color:var(--text)}
    
    .layout {display:flex; min-height:100vh; direction:ltr}
    .sidebar {direction:rtl; width:260px; background:#fff; padding:28px 20px; box-shadow:0 10px 30px rgba(0,0,0,.06)}
    .main {direction:rtl; flex:1; padding:30px 40px}
    
    .sidebar h3 {margin:0 0 28px 0; font-size:18px}
    .sidebar .avatar {display:flex; align-items:center; gap:12px; margin-bottom:22px}
    .sidebar .avatar-img {width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid #e5e7eb}
    .sidebar .avatar-name {font-size:14px; color:#1f2937}
    .sidebar a {display:block; padding:12px 16px; margin-bottom:10px; border-radius:12px; text-decoration:none; color:#374151; font-size:14px; display:flex; align-items:center; gap:10px}
    .sidebar a i {width:20px; text-align:center; color:#9ca3af; font-size:14px}
    .sidebar a.active, .sidebar a:hover {background:#eef2ff; color:var(--blue)}
    .sidebar a.active i, .sidebar a:hover i {color:var(--blue)}
    
    h1 {margin:0 0 20px 0; font-size:28px}
    h2 {margin:0 0 16px 0}
    h3 {margin:0 0 14px 0}
    
    .card {background:#fff; padding:20px; border-radius:var(--radius); box-shadow:0 12px 30px rgba(0,0,0,.06)}
    .card + .card {margin-top:20px}
    
    .btn {border:none; padding:10px 18px; border-radius:10px; cursor:pointer; font-size:14px; display:inline-flex; align-items:center; gap:6px; text-decoration:none}
    .btn-blue {background:var(--blue); color:#fff}
    .btn-green {background:var(--green); color:#fff}
    .btn-red {background:var(--red); color:#fff}
    .btn-gray {background:#e5e7eb; color:#374151}
    .btn:disabled {opacity:.5; cursor:not-allowed}
    
    .status {padding:6px 14px; border-radius:999px; color:#fff; font-size:12px; display:inline-block}
    .status.pending {background:var(--blue)}
    .status.checked {background:#0ea5e9}
    .status.approved {background:var(--green)}
    .status.rejected {background:var(--red)}
    
    label {font-size:13px; color:var(--muted); display:block; margin-bottom:6px}
    input, select, textarea {width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--border); font-size:14px; background:#fff}
    
    table {width:100%; border-collapse:collapse}
    thead {background:#f1f5f9}
    th, td {padding:12px 10px; text-align:center; font-size:14px}
    tbody tr:not(:last-child) {border-bottom:1px solid #eee}
    
    .grid-2 {display:grid; grid-template-columns:1fr 1fr; gap:20px}
    .grid-3 {display:grid; grid-template-columns:repeat(3,1fr); gap:20px}
    .muted {color:var(--muted)}
    .spacer {height:16px}
    
    .alert {padding:12px 16px; border-radius:10px; margin-bottom:16px}
    .alert-success {background:#d1fae5; color:#065f46}
    .alert-error {background:#fee2e2; color:#991b1b}
    
    .filters {display:grid; grid-template-columns:1fr 1fr 1fr 1.2fr auto; gap:16px; align-items:end}
    .filters .btn {height:42px}
    
    .pagination {display:flex; gap:8px; justify-content:center; margin-top:16px}
    .page {background:#fff; border:1px solid var(--border); padding:6px 12px; border-radius:8px; text-decoration:none; color:var(--text)}
    .page.active {background:var(--blue); color:#fff; border-color:var(--blue)}
    
    .stat-card {display:flex; justify-content:space-between; align-items:center; padding:14px; border-bottom:1px solid #eee}
    .stat-card:last-child {border-bottom:none}
    .stat-card strong {font-size:22px; color:var(--green)}
    
    .action-card {color:#fff; padding:18px; border-radius:var(--radius); display:flex; justify-content:space-between; align-items:center; font-weight:600}
    .action-card.green {background:linear-gradient(135deg,#6ee7b7,#34b399)}
    .action-card.blue {background:linear-gradient(135deg,#60a5fa,#3b82f6)}
    
    .log {max-height:150px; overflow:auto; font-size:13px; border:1px solid var(--border); border-radius:8px; padding:10px}
    .log div {border-bottom:1px solid #eee; padding:6px 0}
    .log div:last-child {border-bottom:none}
    
    .rfid-box {border:2px dashed #c7d2fe; border-radius:14px; padding:18px; text-align:center}
    </style>
</head>
<body>
<?php if (isLoggedIn()): ?>
<div class="layout">
    <aside class="sidebar">
        <div class="avatar">
            <?php 
            $currentUser = getCurrentUser();
            $avatarUrl = !empty($currentUser['avatar']) 
                ? APP_URL . '/public/uploads/' . e($currentUser['avatar']) 
                : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'><rect width='80' height='80' fill='%23e0e7ff'/><circle cx='40' cy='30' r='14' fill='%236474ff'/><path d='M14 72c6-16 19-24 26-24s20 8 26 24' fill='%236474ff'/></svg>";
            ?>
            <img class="avatar-img" alt="avatar" src="<?php echo $avatarUrl; ?>">
            <div class="avatar-name"><?php echo e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?: e($currentUser['username']); ?></div>
        </div>
        <h3>لوحة التحكم</h3>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>الرئيسية
        </a>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=samples" class="<?php echo $page === 'samples' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i>قائمة العينات
        </a>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=add_sample" class="<?php echo $page === 'add_sample' ? 'active' : ''; ?>">
            <i class="fas fa-plus"></i>إضافة عينة
        </a>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=reports" class="<?php echo $page === 'reports' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>التقارير
        </a>
        <?php if (isAdmin()): ?>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=users" class="<?php echo $page === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>إدارة المستخدمين
        </a>
        <?php endif; ?>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=profile" class="<?php echo $page === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>الملف الشخصي
        </a>
        <a href="<?php echo APP_URL; ?>/public/index.php?page=logout">
            <i class="fas fa-sign-out-alt"></i>تسجيل الخروج
        </a>
    </aside>
    <main class="main" style="width:100%;">
        <?php 
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:20px;">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <?php echo $content ?? ''; ?>
        
        <div class="spacer"></div>
        <div class="muted" style="text-align:center; font-size:12px; padding:10px 0;">
            © 2026 GSC. All rights reserved for GetSolution Co
        </div>
    </main>
</div>
<?php else: ?>
    <?php echo $content ?? ''; ?>
<?php endif; ?>
</body>
</html>
