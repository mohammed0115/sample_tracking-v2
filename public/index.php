<?php
// Front Controller - Main Entry Point

// Support both /public_html/index.php and /public_html/public/index.php structures
$baseDir = file_exists(__DIR__ . '/config/database.php') 
    ? __DIR__ 
    : __DIR__ . '/..';

require_once $baseDir . '/config/database.php';
require_once $baseDir . '/config/app.php';
require_once $baseDir . '/includes/session.php';
require_once $baseDir . '/includes/functions.php';

// Autoload controllers
spl_autoload_register(function ($class) use ($baseDir) {
    $file = $baseDir . '/app/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
    
    $file = $baseDir . '/app/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Get requested page
$page = sanitize($_GET['page'] ?? 'dashboard');
$action = sanitize($_GET['action'] ?? 'index');

// Route requests
try {
    switch ($page) {
        case 'login':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLogin();
            }
            break;
            
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'register':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->register();
            } else {
                $controller->showRegister();
            }
            break;
            
        case 'profile':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->updateProfile();
            } else {
                $controller->showProfile();
            }
            break;
            
        case 'dashboard':
            $controller = new SampleController();
            $controller->dashboard();
            break;
            
        case 'samples':
            $controller = new SampleController();
            $controller->index();
            break;
            
        case 'add_sample':
            $controller = new SampleController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->add();
            } else {
                $controller->showAdd();
            }
            break;
            
        case 'sample_detail':
            $controller = new SampleController();
            $controller->show();
            break;
            
        case 'sample_action':
            $controller = new SampleController();
            $controller->processAction();
            break;
            
        case 'export_samples':
            $controller = new SampleController();
            $controller->exportExcel();
            break;
            
        case 'reports':
            $controller = new ReportController();
            $controller->index();
            break;
            
        case 'export_report':
            $controller = new ReportController();
            $controller->exportCsv();
            break;
            
        case 'users':
            $controller = new UserController();
            $controller->index();
            break;
            
        case 'create_user':
            $controller = new UserController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } else {
                $controller->showCreate();
            }
            break;
            
        case 'edit_user':
            $controller = new UserController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->update();
            } else {
                $controller->showEdit();
            }
            break;
            
        case 'toggle_user':
            $controller = new UserController();
            $controller->toggleActive();
            break;
            
        case 'reset_password':
            $controller = new UserController();
            $controller->resetPassword();
            break;
            
        case 'password_reset_result':
            $controller = new UserController();
            $controller->showPasswordResetResult();
            break;
            
        default:
            redirect(APP_URL . '/index.php?page=dashboard');
    }
} catch (Exception $e) {
    http_response_code(500);
    die('An error occurred: ' . $e->getMessage());
}
