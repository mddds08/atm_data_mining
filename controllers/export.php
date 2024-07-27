<?php
require '../vendor/autoload.php';
require '../config/database.php';
require '../models/atmData.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$database = new Database();
$db = $database->getConnection();
$atmData = new ATMData($db);

$data = $atmData->getAllData()->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('ATM Data');

// Header
$sheet->setCellValue('A1', 'Lokasi ATM');
$sheet->setCellValue('B1', 'Jarak Tempuh (km)');
$sheet->setCellValue('C1', 'Level Saldo (%)');
$sheet->setCellValue('D1', 'Status Isi');

// Data
$rowIndex = 2;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowIndex, $row['lokasi_atm']);
    $sheet->setCellValue('B' . $rowIndex, $row['jarak_tempuh']);
    $sheet->setCellValue('C' . $rowIndex, $row['level_saldo']);
    $sheet->setCellValue('D' . $rowIndex, $row['status_isi'] == 1 ? 'Isi' : 'Tidak Isi');
    $rowIndex++;
}

$writer = new Xlsx($spreadsheet);
$filename = 'ATM_Data_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit();
?>