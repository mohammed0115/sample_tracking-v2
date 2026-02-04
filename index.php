<?php
// Simple Entry Point for Sample Tracking System

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sample_tracking');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Helper functions
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?page=login');
        exit;
    }
}

// Routes
$page = sanitize($_GET['page'] ?? 'login');
$action = sanitize($_GET['action'] ?? 'index');

switch ($page) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($username && $password) {
                $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    header('Location: ?page=dashboard');
                    exit;
                } else {
                    $error = "Invalid credentials";
                }
            }
        }
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تسجيل الدخول</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial; background: #667eea; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .container { background: white; padding: 40px; border-radius: 10px; width: 100%; max-width: 400px; }
                h1 { margin-bottom: 20px; color: #333; text-align: center; }
                .form-group { margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
                button { width: 100%; padding: 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
                button:hover { background: #764ba2; }
                .error { color: red; padding: 10px; background: #ffe0e0; border-radius: 5px; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>نظام تتبع العينات</h1>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">دخول</button>
                    <p style="text-align: center; margin-top: 15px; font-size: 12px;">
                        admin / admin123
                    </p>
                    <p style="text-align: center; margin-top: 10px;">
                        ليس لديك حساب؟ <a href="?page=register" style="color: #667eea;">إنشاء حساب جديد</a>
                    </p>
                </form>
            </div>
        </body>
        </html>
        <?php
        break;
        
    case 'logout':
        session_destroy();
        header('Location: ?page=login');
        exit;
        break;
        
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            
            $error = '';
            
            if (!$username || !$email || !$password || !$first_name || !$last_name) {
                $error = 'جميع الحقول مطلوبة';
            } elseif (strlen($password) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } elseif ($password !== $password_confirm) {
                $error = 'كلمات المرور غير متطابقة';
            } else {
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'اسم المستخدم موجود بالفعل';
                } else {
                    // Insert new user
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, 'Viewer', 1)");
                    if ($stmt->execute([$username, $email, $hashed, $first_name, $last_name])) {
                        $success = 'تم التسجيل بنجاح. يرجى تسجيل الدخول';
                    } else {
                        $error = 'خطأ في التسجيل';
                    }
                }
            }
        }
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>إنشاء حساب جديد</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial; background: #667eea; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .container { background: white; padding: 40px; border-radius: 10px; width: 100%; max-width: 500px; }
                h1 { margin-bottom: 20px; color: #333; text-align: center; }
                .form-group { margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
                button { width: 100%; padding: 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
                button:hover { background: #764ba2; }
                .error { color: red; padding: 10px; background: #ffe0e0; border-radius: 5px; margin-bottom: 15px; }
                .success { color: green; padding: 10px; background: #e0ffe0; border-radius: 5px; margin-bottom: 15px; }
                .link { text-align: center; margin-top: 15px; }
                .link a { color: #667eea; text-decoration: none; }
                .link a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>إنشاء حساب جديد</h1>
                <?php if (isset($error) && $error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success) && $success): ?>
                    <div class="success"><?php echo $success; ?> <a href="?page=login">اذهب للدخول</a></div>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>الاسم الأول</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>الاسم الأخير</label>
                            <input type="text" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label>اسم المستخدم</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>كلمة المرور</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirm" required>
                        </div>
                        <button type="submit">إنشاء الحساب</button>
                    </form>
                    <div class="link">
                        هل لديك حساب بالفعل؟ <a href="?page=login">تسجيل الدخول</a>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        break;
        
    case 'dashboard':
        requireLogin();
        
        $stats = [
            'users' => $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'] ?? 0,
            'samples' => $pdo->query("SELECT COUNT(*) as cnt FROM samples")->fetch()['cnt'] ?? 0,
            'tags' => $pdo->query("SELECT COUNT(*) as cnt FROM rfid_tags")->fetch()['cnt'] ?? 0,
        ];
        
        $recent = $pdo->query("SELECT * FROM samples ORDER BY created_at DESC LIMIT 5")->fetchAll() ?? [];
        
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>لوحة التحكم</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial; background: #f5f5f5; }
                header { background: #667eea; color: white; padding: 20px; }
                nav { margin-top: 10px; }
                nav a { color: white; margin: 0 15px; text-decoration: none; }
                nav a:hover { text-decoration: underline; }
                .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
                .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
                .stat-box { background: #667eea; color: white; padding: 20px; border-radius: 5px; text-align: center; }
                .stat-box h3 { font-size: 32px; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; text-align: right; border-bottom: 1px solid #ddd; }
                th { background: #f0f0f0; font-weight: bold; }
            </style>
        </head>
        <body>
            <header>
                <h1>نظام تتبع العينات</h1>
                <nav>
                    <a href="?page=dashboard">الرئيسية</a>
                    <a href="?page=samples">العينات</a>
                    <a href="?page=logout">تسجيل الخروج</a>
                </nav>
            </header>
            <div class="container">
                <div class="card">
                    <h2>مرحباً <?php echo e($_SESSION['username']); ?></h2>
                    <p>الدور: <?php echo e($_SESSION['user_role']); ?></p>
                </div>
                
                <div class="stats">
                    <div class="stat-box">
                        <h3><?php echo $stats['users']; ?></h3>
                        <p>المستخدمون</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['samples']; ?></h3>
                        <p>العينات</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['tags']; ?></h3>
                        <p>بطاقات RFID</p>
                    </div>
                </div>
                
                <div class="card">
                    <h3>آخر العينات</h3>
                    <?php if (!empty($recent)): ?>
                        <table>
                            <tr>
                                <th>رقم العينة</th>
                                <th>النوع</th>
                                <th>الفئة</th>
                                <th>الشخص</th>
                                <th>الحالة</th>
                            </tr>
                            <?php foreach ($recent as $sample): ?>
                                <tr>
                                    <td><?php echo e($sample['sample_number']); ?></td>
                                    <td><?php echo e($sample['sample_type']); ?></td>
                                    <td><?php echo e($sample['category']); ?></td>
                                    <td><?php echo e($sample['person_name']); ?></td>
                                    <td><?php echo e($sample['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>لا توجد عينات</p>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        break;
        
    case 'samples':
        requireLogin();
        $samples = $pdo->query("SELECT * FROM samples")->fetchAll() ?? [];
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>العينات</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial; background: #f5f5f5; }
                header { background: #667eea; color: white; padding: 20px; }
                nav { margin-top: 10px; }
                nav a { color: white; margin: 0 15px; text-decoration: none; }
                .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
                .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; text-align: right; border-bottom: 1px solid #ddd; }
                th { background: #f0f0f0; font-weight: bold; }
            </style>
        </head>
        <body>
            <header>
                <h1>نظام تتبع العينات</h1>
                <nav>
                    <a href="?page=dashboard">الرئيسية</a>
                    <a href="?page=samples">العينات</a>
                    <a href="?page=logout">تسجيل الخروج</a>
                </nav>
            </header>
            <div class="container">
                <div class="card">
                    <h2>قائمة العينات</h2>
                    <table>
                        <tr>
                            <th>رقم العينة</th>
                            <th>النوع</th>
                            <th>الفئة</th>
                            <th>الشخص</th>
                            <th>التاريخ</th>
                            <th>الموقع</th>
                            <th>الحالة</th>
                        </tr>
                        <?php foreach ($samples as $sample): ?>
                            <tr>
                                <td><?php echo e($sample['sample_number']); ?></td>
                                <td><?php echo e($sample['sample_type']); ?></td>
                                <td><?php echo e($sample['category']); ?></td>
                                <td><?php echo e($sample['person_name']); ?></td>
                                <td><?php echo e($sample['collected_date']); ?></td>
                                <td><?php echo e($sample['location']); ?></td>
                                <td><?php echo e($sample['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </body>
        </html>
        <?php
        break;
        
    default:
        requireLogin();
        header('Location: ?page=dashboard');
        exit;
}
?>

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
            
        case 'dashboard':
            requireLogin();
            $controller = new SampleController();
            $controller->dashboard();
            break;
            
        case 'samples':
            requireLogin();
            $controller = new SampleController();
            switch ($action) {
                case 'add':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->add();
                    } else {
                        $controller->showAdd();
                    }
                    break;
                case 'list':
                    $controller->listSamples();
                    break;
                case 'detail':
                    $id = sanitize($_GET['id'] ?? 0);
                    $controller->detail($id);
                    break;
                default:
                    $controller->listSamples();
            }
            break;
            
        case 'users':
            requireLogin();
            requireRole('Admin');
            $controller = new UserController();
            switch ($action) {
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->create();
                    } else {
                        $controller->showCreate();
                    }
                    break;
                case 'edit':
                    $id = sanitize($_GET['id'] ?? 0);
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->edit($id);
                    } else {
                        $controller->showEdit($id);
                    }
                    break;
                case 'delete':
                    $id = sanitize($_GET['id'] ?? 0);
                    $controller->delete($id);
                    break;
                case 'list':
                    $controller->listUsers();
                    break;
                default:
                    $controller->listUsers();
            }
            break;
            
        case 'reports':
            requireLogin();
            $controller = new ReportController();
            $controller->index();
            break;
            
        case 'profile':
            requireLogin();
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->updateProfile();
            } else {
                $controller->showProfile();
            }
            break;
            
        case 'password-reset':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->resetPassword();
            } else {
                $controller->showPasswordReset();
            }
            break;
            
        default:
            requireLogin();
            $controller = new SampleController();
            $controller->dashboard();
    }
} catch (Exception $e) {
    http_response_code(500);
    include __DIR__ . '/app/views/layout.php';
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
?>
            
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
            
        case 'dashboard':
            requireLogin();
            $controller = new SampleController();
            $controller->dashboard();
            break;
            
        case 'samples':
            requireLogin();
            $controller = new SampleController();
            switch ($action) {
                case 'add':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->add();
                    } else {
                        $controller->showAdd();
                    }
                    break;
                case 'list':
                    $controller->listSamples();
                    break;
                case 'detail':
                    $id = sanitize($_GET['id'] ?? 0);
                    $controller->detail($id);
                    break;
                default:
                    $controller->listSamples();
            }
            break;
            
        case 'users':
            requireLogin();
            requireRole('Admin');
            $controller = new UserController();
            switch ($action) {
                case 'create':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->create();
                    } else {
                        $controller->showCreate();
                    }
                    break;
                case 'edit':
                    $id = sanitize($_GET['id'] ?? 0);
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->edit($id);
                    } else {
                        $controller->showEdit($id);
                    }
                    break;
                case 'delete':
                    $id = sanitize($_GET['id'] ?? 0);
                    $controller->delete($id);
                    break;
                case 'list':
                    $controller->listUsers();
                    break;
                default:
                    $controller->listUsers();
            }
            break;
            
        case 'reports':
            requireLogin();
            $controller = new ReportController();
            $controller->index();
            break;
            
        case 'profile':
            requireLogin();
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->updateProfile();
            } else {
                $controller->showProfile();
            }
            break;
            
        case 'password-reset':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->resetPassword();
            } else {
                $controller->showPasswordReset();
            }
            break;
            
        default:
            requireLogin();
            $controller = new SampleController();
            $controller->dashboard();
    }
} catch (Exception $e) {
    http_response_code(500);
    include __DIR__ . '/app/views/layout.php';
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
