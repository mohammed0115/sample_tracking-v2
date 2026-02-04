<?php
// User Management Controller

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';

class UserController {
    private $userModel;
    private $auditLogModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->auditLogModel = new AuditLog();
    }
    
    // List users
    public function index() {
        requireAdmin();
        
        $users = $this->userModel->getAll();
        include __DIR__ . '/../views/users/list.php';
    }
    
    // Show create form
    public function showCreate() {
        requireAdmin();
        include __DIR__ . '/../views/users/create.php';
    }
    
    // Create user
    public function create() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=create_user');
        }
        
        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'Viewer'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        
        if ($this->userModel->findByUsername($data['username'])) {
            $errors[] = 'Username already exists.';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        
        if (!in_array($data['role'], ['Admin', 'Operator', 'Viewer'])) {
            $errors[] = 'Invalid role.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            redirect(APP_URL . '/public/index.php?page=create_user');
        }
        
        // Create user
        $result = $this->userModel->create($data);
        
        if ($result) {
            $this->auditLogModel->log(getUserId(), "إنشاء مستخدم: {$data['username']} ({$data['role']})");
            setFlash('success', 'User created successfully.');
        } else {
            setFlash('error', 'Failed to create user.');
        }
        
        redirect(APP_URL . '/public/index.php?page=users');
    }
    
    // Show edit form
    public function showEdit() {
        requireAdmin();
        
        $userId = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        include __DIR__ . '/../views/users/edit.php';
    }
    
    // Update user
    public function update() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $userId = (int)($_POST['id'] ?? 0);
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'Viewer'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        
        if (!in_array($data['role'], ['Admin', 'Operator', 'Viewer'])) {
            $errors[] = 'Invalid role.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect(APP_URL . '/public/index.php?page=edit_user&id=' . $userId);
        }
        
        // Update user
        $result = $this->userModel->update($userId, $data);
        
        if ($result) {
            $actions = [];
            if ($user['role'] !== $data['role']) {
                $actions[] = "تغيير دور المستخدم إلى {$data['role']}";
            }
            if ($user['is_active'] != $data['is_active']) {
                $actions[] = $data['is_active'] ? 'تفعيل المستخدم' : 'إيقاف المستخدم';
            }
            if ($user['email'] !== $data['email'] || $user['first_name'] !== $data['first_name'] || $user['last_name'] !== $data['last_name']) {
                $actions[] = 'تعديل بيانات المستخدم';
            }
            
            foreach ($actions as $action) {
                $this->auditLogModel->log(getUserId(), "{$action}: {$user['username']}");
            }
            
            setFlash('success', 'User updated successfully.');
        } else {
            setFlash('error', 'Failed to update user.');
        }
        
        redirect(APP_URL . '/public/index.php?page=users');
    }
    
    // Toggle active status
    public function toggleActive() {
        requireAdmin();
        
        $userId = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $this->userModel->toggleActive($userId);
        $action = $user['is_active'] ? 'إيقاف' : 'تفعيل';
        $this->auditLogModel->log(getUserId(), "{$action} المستخدم: {$user['username']}");
        
        setFlash('success', 'User status updated.');
        redirect(APP_URL . '/public/index.php?page=users');
    }
    
    // Reset password
    public function resetPassword() {
        requireAdmin();
        
        $userId = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        // Generate random password
        $newPassword = bin2hex(random_bytes(4)); // 8 characters
        $this->userModel->updatePassword($userId, $newPassword);
        $this->auditLogModel->log(getUserId(), "إعادة تعيين كلمة المرور للمستخدم: {$user['username']}");
        
        $_SESSION['new_password'] = $newPassword;
        $_SESSION['password_reset_user'] = $user['username'];
        
        redirect(APP_URL . '/public/index.php?page=password_reset_result');
    }
    
    // Show password reset result
    public function showPasswordResetResult() {
        requireAdmin();
        
        if (!isset($_SESSION['new_password']) || !isset($_SESSION['password_reset_user'])) {
            redirect(APP_URL . '/public/index.php?page=users');
        }
        
        $newPassword = $_SESSION['new_password'];
        $username = $_SESSION['password_reset_user'];
        
        unset($_SESSION['new_password']);
        unset($_SESSION['password_reset_user']);
        
        include __DIR__ . '/../views/users/password_reset_result.php';
    }
}
