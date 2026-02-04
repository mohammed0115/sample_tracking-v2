<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
if (!empty($_SERVER['PATH_INFO'])) {
    $path = $_SERVER['PATH_INFO'];
}
if (str_starts_with($path, '/index.php')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}
$base = base_path();
if ($base && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim($path, '/');
$path = $path === '/' ? '/' : rtrim($path, '/');
if (str_ends_with($path, '.php')) {
    $path = substr($path, 0, -4);
}

switch ($path) {
    case '/':
        if (is_logged_in()) {
            redirect(url('/samples/add'));
        }
        redirect(url('/auth/login'));
        break;

    case '/auth/login':
        require __DIR__ . '/auth/login.php';
        break;
    case '/auth/logout':
        require __DIR__ . '/auth/logout.php';
        break;
    case '/auth/register':
        require __DIR__ . '/auth/register.php';
        break;
    case '/auth/profile':
        require __DIR__ . '/auth/profile.php';
        break;
    case '/auth/users':
        require __DIR__ . '/auth/users.php';
        break;
    case '/auth/users/create':
        require __DIR__ . '/auth/users_create.php';
        break;
    case '/auth/users/edit':
        require __DIR__ . '/auth/users_edit.php';
        break;
    case '/auth/users/toggle':
        require __DIR__ . '/auth/users_toggle.php';
        break;
    case '/auth/users/reset':
        require __DIR__ . '/auth/users_reset.php';
        break;

    case '/samples/list':
        require __DIR__ . '/samples/list.php';
        break;
    case '/samples/add':
        require __DIR__ . '/samples/add.php';
        break;
    case '/samples/detail':
        require __DIR__ . '/samples/detail.php';
        break;
    case '/samples/edit':
        require __DIR__ . '/samples/edit.php';
        break;

    case '/rfid/list':
        require __DIR__ . '/rfid/list.php';
        break;
    case '/rfid/create':
        require __DIR__ . '/rfid/create.php';
        break;
    case '/rfid/edit':
        require __DIR__ . '/rfid/edit.php';
        break;
    case '/rfid/toggle':
        require __DIR__ . '/rfid/toggle.php';
        break;
    case '/rfid/delete':
        require __DIR__ . '/rfid/delete.php';
        break;
    case '/rfid/checker':
        require __DIR__ . '/rfid/checker.php';
        break;
    case '/rfid/forensic':
        require __DIR__ . '/rfid/forensic.php';
        break;
    case '/rfid/warehouse':
        require __DIR__ . '/rfid/warehouse.php';
        break;

    case '/audit/reports':
        require __DIR__ . '/audit/reports.php';
        break;
    case '/audit/export-excel':
        require __DIR__ . '/audit/export_excel.php';
        break;
    case '/audit/export-pdf':
        require __DIR__ . '/audit/export_pdf.php';
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/config/lang.php';
        echo e(__('error.not_found'));
}
