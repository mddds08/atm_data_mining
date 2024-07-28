<?php
require '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Instantiate database
$database = new Database();
$db = $database->getConnection();

// Get C4.5 results from database
$stmt = $db->prepare("SELECT * FROM c45_results");
$stmt->execute();
$c45_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set spreadsheet headers
$headers = ['Atribut', 'Nilai', 'Jumlah Kasus', 'Isi', 'Tidak Isi', 'Entropy', 'Gain'];
$columns = range('A', 'G'); // Define the columns A to G
foreach ($headers as $index => $header) {
    $sheet->setCellValue("{$columns[$index]}1", $header);
}

// Add data to spreadsheet
$rowIndex = 2;
foreach ($c45_results as $result) {
    $sheet->setCellValue("A{$rowIndex}", $result['attribute_name']);
    $sheet->setCellValue("B{$rowIndex}", $result['attribute_value']);
    $sheet->setCellValue("C{$rowIndex}", $result['total_cases']);
    $sheet->setCellValue("D{$rowIndex}", $result['filled_cases']);
    $sheet->setCellValue("E{$rowIndex}", $result['empty_cases']);
    $sheet->setCellValue("F{$rowIndex}", ($result['entropy'] == 1.000) ? 1 : number_format($result['entropy'], 3));
    $sheet->setCellValue("G{$rowIndex}", ($result['gain'] == 1.000) ? 1 : number_format($result['gain'], 3));
    $rowIndex++;
}

// Write spreadsheet to a file
$writer = new Xlsx($spreadsheet);
$filename = 'hasil_c45_' . date('Ymd_His') . '.xlsx';
$filepath = __DIR__ . '/../exports/' . $filename;

// Cek apakah direktori 'exports' ada, jika tidak, buat direktori tersebut
if (!file_exists(__DIR__ . '/../exports')) {
    mkdir(__DIR__ . '/../exports', 0777, true);
}

try {
    $writer->save($filepath);

    // Redirect to the file for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    readfile($filepath);

    // Delete the file after download
    unlink($filepath);
} catch (Exception $e) {
    // Penanganan kesalahan
    echo 'Error writing file: ', $e->getMessage();
    exit();
}

exit();
?>