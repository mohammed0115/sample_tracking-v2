<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/reports.php';

require_role(['Admin', 'Operator']);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
	http_response_code(500);
	header('Content-Type: text/plain; charset=utf-8');
	echo e(__('error.deps_missing'));
	exit;
}

require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$reportType = $_GET['report_type'] ?? 'samples';
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');
$userId = trim($_GET['user_id'] ?? '');

[$columns, $rows, $title] = build_report($reportType, $fromDate, $toDate, $userId);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report');

$sheet->fromArray($columns, null, 'A1');
$rowIndex = 2;
foreach ($rows as $row) {
	$sheet->fromArray($row, null, 'A' . $rowIndex);
	$rowIndex++;
}

$filename = $title ?: 'report';
$filename = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $filename);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
