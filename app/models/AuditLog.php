<?php
// Audit Log Model

class AuditLog {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    // Create audit log entry
    public function log($userId, $action, $sampleId = null) {
        $stmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, sample_id, action) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $sampleId, $action]);
    }
    
    // Get all logs with filters
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT al.*, u.username, s.sample_number 
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                LEFT JOIN samples s ON al.sample_id = s.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['sample_id'])) {
            $sql .= " AND al.sample_id = ?";
            $params[] = $filters['sample_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(al.timestamp) >= ?";
            $params[] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(al.timestamp) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " ORDER BY al.timestamp DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Count logs with filters
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM audit_logs al WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['sample_id'])) {
            $sql .= " AND al.sample_id = ?";
            $params[] = $filters['sample_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(al.timestamp) >= ?";
            $params[] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(al.timestamp) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Get logs for a specific sample
    public function getBySample($sampleId, $limit = 10) {
        return $this->getAll(['sample_id' => $sampleId], $limit);
    }
    
    // Get logs for a specific user
    public function getByUser($userId, $limit = 10) {
        return $this->getAll(['user_id' => $userId], $limit);
    }
    
    // Get RFID check logs
    public function getRFIDCheckLogs($filters = []) {
        $filters['action'] = 'فحص RFID';
        return $this->getAll($filters);
    }
    
    // Get approval logs
    public function getApprovalLogs($filters = []) {
        $filters['action'] = 'اعتماد العينة';
        return $this->getAll($filters);
    }
}
