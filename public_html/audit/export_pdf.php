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

use Dompdf\Dompdf;
use Dompdf\Options;

$reportType = $_GET['report_type'] ?? 'samples';
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');
$userId = trim($_GET['user_id'] ?? '');

[$columns, $rows, $title] = build_report($reportType, $fromDate, $toDate, $userId);

$html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8">';
$html .= '<style>
body{font-family:DejaVu Sans, sans-serif;background:#fff;color:#111}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{border:1px solid #ddd;padding:8px;text-align:center}
th{background:#f1f5f9}
.title{font-size:18px;margin-bottom:10px;text-align:center}
</style></head><body>';
$html .= '<div class="title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>';
$html .= '<table><thead><tr>';
foreach ($columns as $col) {
	$html .= '<th>' . htmlspecialchars($col, ENT_QUOTES, 'UTF-8') . '</th>';
}
$html .= '</tr></thead><tbody>';
if (!$rows) {
	$html .= '<tr><td colspan="' . count($columns) . '">لا توجد بيانات</td></tr>';
} else {
	foreach ($rows as $row) {
		$html .= '<tr>';
		foreach ($row as $cell) {
			$html .= '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
		}
		$html .= '</tr>';
	}
}
$html .= '</tbody></table></body></html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = $title ?: 'report';
$filename = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $filename);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
echo $dompdf->output();
exit;
