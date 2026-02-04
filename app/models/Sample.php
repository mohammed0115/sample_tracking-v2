<?php
// Sample Model

class Sample {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    // Get all samples with filters
    public function getAll($filters = [], $orderBy = 'collected_date DESC, id DESC', $limit = null, $offset = 0) {
        $sql = "SELECT s.*, r.uid as rfid_uid 
                FROM samples s 
                LEFT JOIN rfid_tags r ON s.rfid_id = r.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['sample_type'])) {
            $sql .= " AND s.sample_type = ?";
            $params[] = $filters['sample_type'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND s.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND s.collected_date = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.sample_number LIKE ? OR s.person_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY $orderBy";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Count samples with filters
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM samples s WHERE 1=1";
        $params = [];
        
        if (!empty($filters['sample_type'])) {
            $sql .= " AND s.sample_type = ?";
            $params[] = $filters['sample_type'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND s.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND s.collected_date = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.sample_number LIKE ? OR s.person_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Find by sample number
    public function findBySampleNumber($sampleNumber) {
        $sql = "SELECT s.*, r.uid as rfid_uid 
                FROM samples s 
                LEFT JOIN rfid_tags r ON s.rfid_id = r.id 
                WHERE s.sample_number = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sampleNumber]);
        return $stmt->fetch();
    }
    
    // Find by ID
    public function findById($id) {
        $sql = "SELECT s.*, r.uid as rfid_uid 
                FROM samples s 
                LEFT JOIN rfid_tags r ON s.rfid_id = r.id 
                WHERE s.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Create sample
    public function create($data) {
        // Generate sample number if not provided
        if (empty($data['sample_number'])) {
            $data['sample_number'] = $this->generateSampleNumber();
        }
        
        $sql = "INSERT INTO samples (sample_number, sample_type, category, person_name, 
                collected_date, location, rfid_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['sample_number'],
            $data['sample_type'],
            $data['category'],
            $data['person_name'],
            $data['collected_date'],
            $data['location'] ?? null,
            $data['rfid_id'],
            $data['status'] ?? 'pending'
        ]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    // Update sample
    public function update($id, $data) {
        $sql = "UPDATE samples SET sample_type = ?, category = ?, person_name = ?, 
                collected_date = ?, location = ?, rfid_id = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['sample_type'],
            $data['category'],
            $data['person_name'],
            $data['collected_date'],
            $data['location'] ?? null,
            $data['rfid_id'],
            $id
        ]);
    }
    
    // Update status
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE samples SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    // Delete sample
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM samples WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Generate unique sample number
    private function generateSampleNumber() {
        $date = date('Ymd');
        $stmt = $this->pdo->prepare("SELECT sample_number FROM samples WHERE sample_number LIKE ? ORDER BY sample_number DESC LIMIT 1");
        $stmt->execute([$date . '%']);
        $lastSample = $stmt->fetch();
        
        if ($lastSample) {
            $lastNumber = (int) substr($lastSample['sample_number'], -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $date . $newNumber;
    }
    
    // Get recent samples
    public function getRecent($limit = 5) {
        return $this->getAll([], 'collected_date DESC, id DESC', $limit);
    }
    
    // Get statistics
    public function getStatistics() {
        $stats = [];
        
        // Total samples
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM samples");
        $stats['total'] = $stmt->fetchColumn();
        
        // By status
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM samples GROUP BY status");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // By type
        $stmt = $this->pdo->query("SELECT sample_type, COUNT(*) as count FROM samples GROUP BY sample_type ORDER BY count DESC LIMIT 5");
        $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // By category
        $stmt = $this->pdo->query("SELECT category, COUNT(*) as count FROM samples GROUP BY category ORDER BY count DESC LIMIT 5");
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }
}
