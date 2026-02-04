<?php
// Authentication Controller

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AuthController {
    private $userModel;
    private $auditLogModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->auditLogModel = new AuditLog();
    }
    
    // Show login form
    public function showLogin() {
        if (isLoggedIn()) {
            redirect(APP_URL . '/public/index.php?page=dashboard');
        }
        include __DIR__ . '/../views/auth/login.php';
    }
    
    // Process login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=login');
        }
        
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';
        
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request. Please try again.');
            redirect(APP_URL . '/public/index.php?page=login');
        }
        
        if (empty($username) || empty($password)) {
            setFlash('error', 'Please enter username and password.');
            redirect(APP_URL . '/public/index.php?page=login');
        }
        
        $user = $this->userModel->verifyPassword($username, $password);
        
        if ($user && $user['is_active']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role'],
                'avatar' => $user['avatar']
            ];
            
            // Log login
            $this->auditLogModel->log($user['id'], 'تسجيل الدخول');
            
            $redirect = $_GET['redirect'] ?? APP_URL . '/public/index.php?page=dashboard';
            redirect($redirect);
        } else {
            setFlash('error', 'Invalid username or password, or account is disabled.');
            redirect(APP_URL . '/public/index.php?page=login');
        }
    }
    
    // Logout
    public function logout() {
        if (isLoggedIn()) {
            $userId = getUserId();
            $this->auditLogModel->log($userId, 'تسجيل الخروج');
        }
        
        session_destroy();
        redirect(APP_URL . '/public/index.php?page=login');
    }
    
    // Show registration form
    public function showRegister() {
        include __DIR__ . '/../views/auth/register.php';
    }
    
    // Process registration
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=register');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=register');
        }
        
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        
        if ($this->userModel->findByUsername($username)) {
            $errors[] = 'Username already exists.';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email already exists.';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            redirect(APP_URL . '/public/index.php?page=register');
        }
        
        // Create user
        $result = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'Viewer', // Default role
            'is_active' => 1
        ]);
        
        if ($result) {
            setFlash('success', 'Registration successful. Please login.');
            redirect(APP_URL . '/public/index.php?page=login');
        } else {
            setFlash('error', 'Registration failed. Please try again.');
            redirect(APP_URL . '/public/index.php?page=register');
        }
    }
    
    // Show profile edit form
    public function showProfile() {
        requireAuth();
        $user = $this->userModel->findById(getUserId());
        include __DIR__ . '/../views/auth/profile.php';
    }
    
    // Update profile
    public function updateProfile() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=profile');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=profile');
        }
        
        $userId = getUserId();
        $user = $this->userModel->findById($userId);
        
        $email = sanitize($_POST['email'] ?? '');
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        
        // Check if email is taken by another user
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            setFlash('error', 'Email already in use.');
            redirect(APP_URL . '/public/index.php?page=profile');
        }
        
        // Update user
        $result = $this->userModel->update($userId, [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $user['role'],
            'is_active' => $user['is_active']
        ]);
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            try {
                $filename = uploadFile($_FILES['avatar']);
                $this->userModel->updateAvatar($userId, $filename);
                
                // Delete old avatar
                if ($user['avatar'] && file_exists(UPLOAD_DIR . $user['avatar'])) {
                    unlink(UPLOAD_DIR . $user['avatar']);
                }
            } catch (Exception $e) {
                setFlash('error', 'Avatar upload failed: ' . $e->getMessage());
            }
        }
        
        if ($result) {
            // Update session
            $_SESSION['user'] = array_merge($_SESSION['user'], [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName
            ]);
            
            $this->auditLogModel->log($userId, 'تحديث الملف الشخصي');
            setFlash('success', 'Profile updated successfully.');
        } else {
            setFlash('error', 'Profile update failed.');
        }
        
        redirect(APP_URL . '/public/index.php?page=profile');
    }
}
