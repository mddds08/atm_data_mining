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
$headers = ['Node', 'Attribute', 'Value', 'Total', 'Isi', 'Tidak Isi', 'Entropy', 'Gain'];
$columns = range('A', 'H'); // Define the columns A to H
foreach ($headers as $index => $header) {
    $sheet->setCellValue("{$columns[$index]}1", $header);
}

// Add data to spreadsheet
$rowIndex = 2;
$node = 1;
$lastAttribute = '';

foreach ($c45_results as $result) {
    if ($result['attribute_name'] !== $lastAttribute) {
        // Add header row for each attribute
        $total = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'total_cases'));

        $isi = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'filled_cases'));

        $tidakIsi = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'empty_cases'));

        $entropy = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'entropy')) / count(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }));

        $sheet->setCellValue("A{$rowIndex}", $node);
        $sheet->setCellValue("B{$rowIndex}", $result['attribute_name']);
        $sheet->setCellValue("D{$rowIndex}", $total);
        $sheet->setCellValue("E{$rowIndex}", $isi);
        $sheet->setCellValue("F{$rowIndex}", $tidakIsi);
        $sheet->setCellValue("G{$rowIndex}", number_format($entropy, 3));
        $sheet->setCellValue("H{$rowIndex}", number_format($result['gain'], 3));
        $rowIndex++;
        $node++;
        $lastAttribute = $result['attribute_name'];
    }

    // Set data row values
    $sheet->setCellValue("B{$rowIndex}", $result['attribute_value']);
    $sheet->setCellValue("D{$rowIndex}", $result['total_cases']);
    $sheet->setCellValue("E{$rowIndex}", $result['filled_cases']);
    $sheet->setCellValue("F{$rowIndex}", $result['empty_cases']);
    $rowIndex++;
}

// Add entropy formula
for ($i = 2; $i < $rowIndex; $i++) {
    $totalCasesCell = "D{$i}";
    $filledCasesCell = "E{$i}";
    $emptyCasesCell = "F{$i}";

    $entropyFormula = "=IF($totalCasesCell=0,0,-((IF($filledCasesCell=0,0,($filledCasesCell/$totalCasesCell)*LOG($filledCasesCell/$totalCasesCell,2)) + IF($emptyCasesCell=0,0,($emptyCasesCell/$totalCasesCell)*LOG($emptyCasesCell/$totalCasesCell,2)))))";
    $sheet->setCellValue("G{$i}", $entropyFormula);
    // $sheet->setCellValue("=SUM($entropyFormula", "G{$i})");
}

// Add gain formula
// Find the total entropy for the dataset
$totalEntropy = number_format(array_sum(array_column($c45_results, 'entropy')) / count($c45_results), 3);

for ($i = 2; $i < $rowIndex; $i++) {
    $entropyCell = "G{$i}";
    $gainFormula = "$totalEntropy - $entropyCell";
    $sheet->setCellValue("H{$i}", $gainFormula);
}

// Write spreadsheet to a file
$writer = new Xlsx($spreadsheet);
$filename = 'DataHasilC45_' . date('Ymd_His') . '.xlsx';
$filepath = __DIR__ . '/../exports/' . $filename;

// Check if 'exports' directory exists, if not, create it
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
    // Error handling
    echo 'Error writing file: ', $e->getMessage();
    exit();
}

exit();
?>