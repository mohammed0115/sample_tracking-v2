<?php
// RFID Tag Model

class RFIDTag {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    // Get all RFID tags
    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM rfid_tags";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY uid ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    // Find by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM rfid_tags WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Find by UID
    public function findByUid($uid) {
        $stmt = $this->pdo->prepare("SELECT * FROM rfid_tags WHERE uid = ?");
        $stmt->execute([$uid]);
        return $stmt->fetch();
    }
    
    // Create RFID tag
    public function create($uid, $isActive = 1) {
        $stmt = $this->pdo->prepare("INSERT INTO rfid_tags (uid, is_active) VALUES (?, ?)");
        $result = $stmt->execute([$uid, $isActive]);
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    // Update RFID tag
    public function update($id, $uid, $isActive) {
        $stmt = $this->pdo->prepare("UPDATE rfid_tags SET uid = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([$uid, $isActive, $id]);
    }
    
    // Toggle active status
    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE rfid_tags SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Get available (unused) RFID tags
    public function getAvailable() {
        $sql = "SELECT r.* FROM rfid_tags r 
                LEFT JOIN samples s ON r.id = s.rfid_id 
                WHERE r.is_active = 1 AND s.id IS NULL 
                ORDER BY r.uid ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    // Check if UID is available
    public function isUidAvailable($uid, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM rfid_tags WHERE uid = ?";
        $params = [$uid];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }
}
