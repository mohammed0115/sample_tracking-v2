<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lang.php';

function build_report(string $reportType, string $fromDate = '', string $toDate = '', string $userId = ''): array {
    $statusMap = [
        'pending' => __('status.pending'),
        'checked' => __('status.checked'),
        'approved' => __('status.approved'),
        'rejected' => __('status.rejected'),
    ];

    $columns = [];
    $rows = [];
    $title = '';

    $dateFilter = function (string $field, ?string $start, ?string $end, array &$params): string {
        $sql = '';
        if ($start) {
            $sql .= " AND DATE($field) >= ?";
            $params[] = $start;
        }
        if ($end) {
            $sql .= " AND DATE($field) <= ?";
            $params[] = $end;
        }
        return $sql;
    };

    if ($reportType === 'rfid') {
        $params = [];
        $sql = "SELECT al.*, s.sample_number, u.username FROM audit_logs al LEFT JOIN samples s ON al.sample_id = s.id LEFT JOIN users u ON al.user_id = u.id WHERE al.action LIKE 'فحص RFID%'";
        $sql .= $dateFilter('al.timestamp', $fromDate ?: null, $toDate ?: null, $params);
        if ($userId !== '') {
            $sql .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY al.timestamp DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $columns = [__('samples.sample_number'), __('reports.uid'), __('reports.rfid_time'), __('reports.user'), __('reports.result')];
        $title = __('reports.rfid');
        foreach ($logs as $log) {
            $uid = '';
            if (strpos($log['action'], 'UID:') !== false) {
                $uid = trim(str_replace(['فحص RFID (', ')'], '', $log['action']));
                if (strpos($uid, 'UID:') !== false) {
                    $uid = trim(str_replace('UID:', '', $uid));
                }
            }
            $rows[] = [
                $log['sample_number'] ?? '-',
                $uid,
                date('Y-m-d H:i', strtotime($log['timestamp'])),
                $log['username'] ?? '-',
                'نجاح',
            ];
        }
        return [$columns, $rows, $title];
    }

    if ($reportType === 'approval') {
        $params = [];
        $sql = "SELECT al.*, s.sample_number, u.username FROM audit_logs al LEFT JOIN samples s ON al.sample_id = s.id LEFT JOIN users u ON al.user_id = u.id WHERE al.action = 'اعتماد العينة'";
        $sql .= $dateFilter('al.timestamp', $fromDate ?: null, $toDate ?: null, $params);
        if ($userId !== '') {
            $sql .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY al.timestamp DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $columns = [__('samples.sample_number'), __('reports.approval_date'), __('reports.user'), __('reports.final_status'), __('reports.notes')];
        $title = __('reports.approval');
        foreach ($logs as $log) {
            $rows[] = [
                $log['sample_number'] ?? '-',
                date('Y-m-d H:i', strtotime($log['timestamp'])),
                $log['username'] ?? '-',
                'معتمدة',
                '',
            ];
        }
        return [$columns, $rows, $title];
    }

    if ($reportType === 'audit') {
        $params = [];
        $sql = "SELECT al.*, s.sample_number, u.username FROM audit_logs al LEFT JOIN samples s ON al.sample_id = s.id LEFT JOIN users u ON al.user_id = u.id WHERE 1=1";
        $sql .= $dateFilter('al.timestamp', $fromDate ?: null, $toDate ?: null, $params);
        if ($userId !== '') {
            $sql .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY al.timestamp DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $columns = [__('reports.user'), __('audit.title'), __('samples.sample_number'), __('reports.datetime')];
        $title = __('reports.audit');
        foreach ($logs as $log) {
            $rows[] = [
                $log['username'] ?? '-',
                $log['action'],
                $log['sample_number'] ?? '-',
                date('Y-m-d H:i', strtotime($log['timestamp'])),
            ];
        }
        return [$columns, $rows, $title];
    }

    $params = [];
    $sql = "SELECT * FROM samples WHERE 1=1";
    if ($fromDate !== '') {
        $sql .= " AND collected_date >= ?";
        $params[] = $fromDate;
    }
    if ($toDate !== '') {
        $sql .= " AND collected_date <= ?";
        $params[] = $toDate;
    }
    $sql .= " ORDER BY collected_date DESC, id DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $samples = $stmt->fetchAll();

    $approvalMap = [];
    $aParams = [];
    $aSql = "SELECT al.sample_id, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.action = 'اعتماد العينة'";
    if ($userId !== '') {
        $aSql .= " AND al.user_id = ?";
        $aParams[] = $userId;
    }
    $aSql .= " ORDER BY al.timestamp DESC";
    $aStmt = db()->prepare($aSql);
    $aStmt->execute($aParams);
    foreach ($aStmt->fetchAll() as $log) {
        if (!isset($approvalMap[$log['sample_id']])) {
            $approvalMap[$log['sample_id']] = $log['username'];
        }
    }

    $columns = [__('samples.sample_number'), __('samples.sample_type'), __('samples.category'), __('samples.collected_date'), __('samples.status'), __('reports.rfid'), __('reports.approval')];
    $title = __('reports.samples');
    foreach ($samples as $s) {
        $rfidChecked = in_array($s['status'], ['checked', 'approved'], true) ? 'نعم' : 'لا';
        $rows[] = [
            $s['sample_number'],
            $s['sample_type'],
            $s['category'],
            $s['collected_date'],
            $statusMap[$s['status']] ?? $s['status'],
            $rfidChecked,
            $approvalMap[$s['id']] ?? '-',
        ];
    }
    return [$columns, $rows, $title];
}
