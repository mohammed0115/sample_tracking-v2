<?php
// Helper Functions

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format date
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

// Escape HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Get status label in Arabic
function getStatusLabel($status) {
    $labels = [
        'pending' => 'قيد الفحص',
        'checked' => 'تم التحقق',
        'approved' => 'معتمدة',
        'rejected' => 'مرفوضة'
    ];
    return $labels[$status] ?? $status;
}

// Get status CSS class
function getStatusClass($status) {
    $classes = [
        'pending' => 'pending',
        'checked' => 'checked',
        'approved' => 'approved',
        'rejected' => 'rejected'
    ];
    return $classes[$status] ?? '';
}

// Pagination
function paginate($total, $perPage, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
}

// Upload file
function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('File size exceeds limit.');
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new RuntimeException('Invalid file type.');
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }
    
    return $filename;
}

// Translate
function __($key) {
    static $translations = null;
    
    if ($translations === null) {
        $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
        $file = __DIR__ . "/../locale/{$lang}.php";
        if (file_exists($file)) {
            $translations = include $file;
        } else {
            $translations = [];
        }
    }
    
    return $translations[$key] ?? $key;
}

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('?page=login');
    }
}

// Check user role
function requireRole($role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        http_response_code(403);
        die("Access Denied: You do not have permission to access this page.");
    }
}
