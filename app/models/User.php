<?php
// User Model

class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    // Find user by username
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    // Find user by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Find user by email
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Get all users
    public function getAll($orderBy = 'username ASC') {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY $orderBy");
        return $stmt->fetchAll();
    }
    
    // Create user
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['role'] ?? 'Viewer',
            $data['is_active'] ?? 1
        ]);
    }
    
    // Update user
    public function update($id, $data) {
        $sql = "UPDATE users SET email = ?, first_name = ?, last_name = ?, role = ?, is_active = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['email'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['role'] ?? 'Viewer',
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    // Update password
    public function updatePassword($id, $password) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
    }
    
    // Update avatar
    public function updateAvatar($id, $avatar) {
        $stmt = $this->pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatar, $id]);
    }
    
    // Toggle active status
    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Verify password
    public function verifyPassword($username, $password) {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
