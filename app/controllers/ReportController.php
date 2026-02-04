<?php
// Report Controller

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Sample.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/User.php';

class ReportController {
    private $sampleModel;
    private $auditLogModel;
    private $userModel;
    
    public function __construct() {
        $this->sampleModel = new Sample();
        $this->auditLogModel = new AuditLog();
        $this->userModel = new User();
    }
    
    // Show reports
    public function index() {
        requireAuth();
        
        $reportType = sanitize($_GET['report_type'] ?? 'samples');
        $fromDate = sanitize($_GET['from_date'] ?? '');
        $toDate = sanitize($_GET['to_date'] ?? '');
        $userId = (int)($_GET['user_id'] ?? 0);
        
        $users = $this->userModel->getAll();
        $report = $this->buildReport($reportType, $fromDate, $toDate, $userId);
        
        include __DIR__ . '/../views/reports/index.php';
    }
    
    // Build report data
    private function buildReport($reportType, $fromDate, $toDate, $userId) {
        $filters = [];
        if ($fromDate) $filters['from_date'] = $fromDate;
        if ($toDate) $filters['to_date'] = $toDate;
        if ($userId) $filters['user_id'] = $userId;
        
        switch ($reportType) {
            case 'rfid':
                return $this->buildRFIDReport($filters);
            case 'approval':
                return $this->buildApprovalReport($filters);
            case 'audit':
                return $this->buildAuditReport($filters);
            default:
                return $this->buildSamplesReport($filters);
        }
    }
    
    // Build samples report
    private function buildSamplesReport($filters) {
        $sampleFilters = [];
        if (!empty($filters['from_date'])) {
            $sampleFilters['date'] = $filters['from_date'];
        }
        
        $samples = $this->sampleModel->getAll($sampleFilters);
        
        // Get approval user for each sample
        $approvalLogs = $this->auditLogModel->getApprovalLogs($filters);
        $approvalMap = [];
        foreach ($approvalLogs as $log) {
            if ($log['sample_id'] && !isset($approvalMap[$log['sample_id']])) {
                $approvalMap[$log['sample_id']] = $log['username'];
            }
        }
        
        $rows = [];
        foreach ($samples as $sample) {
            $rfidChecked = in_array($sample['status'], ['checked', 'approved']) ? 'نعم' : 'لا';
            $approver = $approvalMap[$sample['id']] ?? '-';
            
            $rows[] = [
                'رقم العينة' => $sample['sample_number'],
                'نوع العينة' => $sample['sample_type'],
                'التصنيف' => $sample['category'],
                'تاريخ الجمع' => formatDate($sample['collected_date']),
                'الحالة' => getStatusLabel($sample['status']),
                'تم فحص RFID' => $rfidChecked,
                'المعتمد' => $approver
            ];
        }
        
        return [
            'title' => 'تقرير العينات',
            'columns' => ['رقم العينة', 'نوع العينة', 'التصنيف', 'تاريخ الجمع', 'الحالة', 'تم فحص RFID', 'المعتمد'],
            'status_columns' => ['الحالة'],
            'rows' => $rows
        ];
    }
    
    // Build RFID check report
    private function buildRFIDReport($filters) {
        $logs = $this->auditLogModel->getRFIDCheckLogs($filters);
        
        $rows = [];
        foreach ($logs as $log) {
            $uid = '';
            if (preg_match('/UID:\s*([^\)]+)/', $log['action'], $matches)) {
                $uid = trim($matches[1]);
            }
            
            $rows[] = [
                'رقم العينة' => $log['sample_number'] ?? '-',
                'UID' => $uid,
                'وقت الفحص' => formatDate($log['timestamp'], DATETIME_FORMAT),
                'المستخدم' => $log['username'],
                'النتيجة' => 'نجاح'
            ];
        }
        
        return [
            'title' => 'تقرير فحص RFID',
            'columns' => ['رقم العينة', 'UID', 'وقت الفحص', 'المستخدم', 'النتيجة'],
            'status_columns' => [],
            'rows' => $rows
        ];
    }
    
    // Build approval report
    private function buildApprovalReport($filters) {
        $logs = $this->auditLogModel->getApprovalLogs($filters);
        
        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                'رقم العينة' => $log['sample_number'] ?? '-',
                'تاريخ الاعتماد' => formatDate($log['timestamp'], DATETIME_FORMAT),
                'المستخدم' => $log['username'],
                'الحالة النهائية' => 'معتمدة',
                'ملاحظات' => ''
            ];
        }
        
        return [
            'title' => 'تقرير الاعتماد',
            'columns' => ['رقم العينة', 'تاريخ الاعتماد', 'المستخدم', 'الحالة النهائية', 'ملاحظات'],
            'status_columns' => ['الحالة النهائية'],
            'rows' => $rows
        ];
    }
    
    // Build audit log report
    private function buildAuditReport($filters) {
        $logs = $this->auditLogModel->getAll($filters);
        
        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                'المستخدم' => $log['username'],
                'الإجراء' => $log['action'],
                'رقم العينة' => $log['sample_number'] ?? '-',
                'التاريخ والوقت' => formatDate($log['timestamp'], DATETIME_FORMAT)
            ];
        }
        
        return [
            'title' => 'تقرير النشاط',
            'columns' => ['المستخدم', 'الإجراء', 'رقم العينة', 'التاريخ والوقت'],
            'status_columns' => [],
            'rows' => $rows
        ];
    }
    
    // Export report to CSV
    public function exportCsv() {
        requireAuth();
        
        if (!isOperatorOrAdmin()) {
            http_response_code(403);
            die('Access denied.');
        }
        
        $reportType = sanitize($_GET['report_type'] ?? 'samples');
        $fromDate = sanitize($_GET['from_date'] ?? '');
        $toDate = sanitize($_GET['to_date'] ?? '');
        $userId = (int)($_GET['user_id'] ?? 0);
        
        $report = $this->buildReport($reportType, $fromDate, $toDate, $userId);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $report['title'] . '_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, $report['columns']);
        
        // Data
        foreach ($report['rows'] as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
    }
}
