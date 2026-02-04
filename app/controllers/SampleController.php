<?php
// Sample Controller

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Sample.php';
require_once __DIR__ . '/../models/RFIDTag.php';
require_once __DIR__ . '/../models/AuditLog.php';

class SampleController {
    private $sampleModel;
    private $rfidModel;
    private $auditLogModel;
    
    public function __construct() {
        $this->sampleModel = new Sample();
        $this->rfidModel = new RFIDTag();
        $this->auditLogModel = new AuditLog();
    }
    
    // Dashboard
    public function dashboard() {
        requireAuth();
        
        $stats = $this->sampleModel->getStatistics();
        $recentSamples = $this->sampleModel->getRecent(5);
        $rfidTags = $this->rfidModel->getAvailable();
        
        include __DIR__ . '/../views/samples/dashboard.php';
    }
    
    // List samples
    public function index() {
        requireAuth();
        
        $filters = [
            'sample_type' => sanitize($_GET['sample_type'] ?? ''),
            'category' => sanitize($_GET['category'] ?? ''),
            'date' => sanitize($_GET['date'] ?? ''),
            'search' => sanitize($_GET['q'] ?? '')
        ];
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->sampleModel->count($filters);
        $pagination = paginate($total, RECORDS_PER_PAGE, $page);
        
        $samples = $this->sampleModel->getAll(
            $filters,
            'collected_date DESC, id DESC',
            RECORDS_PER_PAGE,
            $pagination['offset']
        );
        
        include __DIR__ . '/../views/samples/list.php';
    }
    
    // Show add form
    public function showAdd() {
        requireAuth();
        
        $rfidTags = $this->rfidModel->getAvailable();
        include __DIR__ . '/../views/samples/add.php';
    }
    
    // Add sample
    public function add() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=add_sample');
        }
        
        $data = [
            'sample_type' => sanitize($_POST['sample_type'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'person_name' => sanitize($_POST['person_name'] ?? ''),
            'collected_date' => sanitize($_POST['collected_date'] ?? ''),
            'location' => sanitize($_POST['location'] ?? ''),
            'rfid_id' => (int)($_POST['rfid_id'] ?? 0)
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['sample_type'])) {
            $errors[] = 'Sample type is required.';
        }
        
        if (empty($data['category'])) {
            $errors[] = 'Category is required.';
        }
        
        if (empty($data['person_name'])) {
            $errors[] = 'Person name is required.';
        }
        
        if (empty($data['collected_date'])) {
            $errors[] = 'Collection date is required.';
        }
        
        if (empty($data['rfid_id'])) {
            $errors[] = 'RFID tag is required.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            redirect(APP_URL . '/public/index.php?page=add_sample');
        }
        
        // Create sample
        $sampleId = $this->sampleModel->create($data);
        
        if ($sampleId) {
            $sample = $this->sampleModel->findById($sampleId);
            $this->auditLogModel->log(getUserId(), 'إضافة عينة: ' . $sample['sample_number'], $sampleId);
            
            setFlash('success', 'Sample added successfully.');
            
            if (isset($_POST['add_another'])) {
                redirect(APP_URL . '/public/index.php?page=add_sample');
            } else {
                redirect(APP_URL . '/public/index.php?page=samples');
            }
        } else {
            setFlash('error', 'Failed to add sample.');
            redirect(APP_URL . '/public/index.php?page=add_sample');
        }
    }
    
    // Show sample details
    public function show() {
        requireAuth();
        
        $sampleNumber = sanitize($_GET['sample_number'] ?? '');
        if (empty($sampleNumber)) {
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        $sample = $this->sampleModel->findBySampleNumber($sampleNumber);
        if (!$sample) {
            setFlash('error', 'Sample not found.');
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        $logs = $this->auditLogModel->getBySample($sample['id'], 10);
        $canAct = isOperatorOrAdmin();
        
        include __DIR__ . '/../views/samples/detail.php';
    }
    
    // Process sample actions (RFID check, approve, reject)
    public function processAction() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        if (!isOperatorOrAdmin()) {
            http_response_code(403);
            die('Access denied.');
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrf)) {
            setFlash('error', 'Invalid request.');
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        $sampleNumber = sanitize($_POST['sample_number'] ?? '');
        $action = sanitize($_POST['action'] ?? '');
        
        $sample = $this->sampleModel->findBySampleNumber($sampleNumber);
        if (!$sample) {
            setFlash('error', 'Sample not found.');
            redirect(APP_URL . '/public/index.php?page=samples');
        }
        
        $userId = getUserId();
        
        switch ($action) {
            case 'rfid_check':
                if ($sample['status'] === 'pending') {
                    $this->sampleModel->updateStatus($sample['id'], 'checked');
                    $this->auditLogModel->log($userId, 'فحص RFID (UID: ' . $sample['rfid_uid'] . ')', $sample['id']);
                    setFlash('success', 'RFID check completed.');
                }
                break;
                
            case 'approve':
                if ($sample['status'] === 'checked') {
                    $this->sampleModel->updateStatus($sample['id'], 'approved');
                    $this->auditLogModel->log($userId, 'اعتماد العينة', $sample['id']);
                    setFlash('success', 'Sample approved.');
                }
                break;
                
            case 'reject':
                if (in_array($sample['status'], ['pending', 'checked', 'approved'])) {
                    $this->sampleModel->updateStatus($sample['id'], 'rejected');
                    $this->auditLogModel->log($userId, 'رفض العينة', $sample['id']);
                    setFlash('success', 'Sample rejected.');
                }
                break;
        }
        
        redirect(APP_URL . '/public/index.php?page=sample_detail&sample_number=' . urlencode($sampleNumber));
    }
    
    // Export to Excel
    public function exportExcel() {
        requireAuth();
        
        $filters = [
            'sample_type' => sanitize($_GET['sample_type'] ?? ''),
            'category' => sanitize($_GET['category'] ?? ''),
            'date' => sanitize($_GET['date'] ?? ''),
            'search' => sanitize($_GET['q'] ?? '')
        ];
        
        $samples = $this->sampleModel->getAll($filters);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="samples_export_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['Sample Number', 'Person Name', 'Type', 'Category', 'Date', 'Status', 'RFID']);
        
        // Data
        foreach ($samples as $sample) {
            fputcsv($output, [
                $sample['sample_number'],
                $sample['person_name'],
                $sample['sample_type'],
                $sample['category'],
                formatDate($sample['collected_date']),
                getStatusLabel($sample['status']),
                $sample['rfid_uid']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
