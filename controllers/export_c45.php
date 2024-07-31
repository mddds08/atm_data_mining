<?php
require '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();

// Ambil hasil C4.5 dari database
$stmt = $db->prepare("SELECT * FROM c45_results");
$stmt->execute();
$c45_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header spreadsheet
$headers = ['Node', 'Attribute', 'Value', 'Total', 'Isi', 'Tidak Isi', 'Entropy', 'Gain'];
$columns = range('A', 'H'); // Definisikan kolom dari A hingga H
foreach ($headers as $index => $header) {
    $sheet->setCellValue("{$columns[$index]}1", $header);
}

// Tambahkan data ke spreadsheet
$rowIndex = 2;
$node = 1;
$lastAttribute = '';
$totalEntropyCell = '';
$gainData = [];
$attributeData = [];

foreach ($c45_results as $result) {
    if ($result['attribute_name'] !== $lastAttribute) {
        // Tambahkan baris header untuk setiap atribut
        $total = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'total_cases'));

        $isi = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'filled_cases'));

        $tidakIsi = array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
            return $r['attribute_name'] == $result['attribute_name'];
        }), 'empty_cases'));

        $entropyFormula = "=IF(D{$rowIndex}=0,0,-(IF(E{$rowIndex}=0,0,(E{$rowIndex}/D{$rowIndex})*LOG(E{$rowIndex}/D{$rowIndex},2)) + IF(F{$rowIndex}=0,0,(F{$rowIndex}/D{$rowIndex})*LOG(F{$rowIndex}/D{$rowIndex},2))))";
        $sheet->setCellValue("A{$rowIndex}", $node);
        $sheet->setCellValue("B{$rowIndex}", $result['attribute_name']);
        $sheet->setCellValue("D{$rowIndex}", $total);
        $sheet->setCellValue("E{$rowIndex}", $isi);
        $sheet->setCellValue("F{$rowIndex}", $tidakIsi);
        $sheet->setCellValue("G{$rowIndex}", $entropyFormula);
        $sheet->setCellValue("H{$rowIndex}", ""); // Kosongkan gain untuk saat ini

        $totalEntropyCell = "G{$rowIndex}"; // Simpan entropi total untuk perhitungan gain
        $gainData[$result['attribute_name']] = ['row' => $rowIndex, 'total' => $total];
        $attributeData[$result['attribute_name']] = [];
        $rowIndex++;
        $node++;
        $lastAttribute = $result['attribute_name'];
    }

    // Set data nilai atribut
    $sheet->setCellValue("B{$rowIndex}", $result['attribute_value']);
    $sheet->setCellValue("D{$rowIndex}", $result['total_cases']);
    $sheet->setCellValue("E{$rowIndex}", $result['filled_cases']);
    $sheet->setCellValue("F{$rowIndex}", $result['empty_cases']);
    $entropyFormula = "=IF(D{$rowIndex}=0,0,-(IF(E{$rowIndex}=0,0,(E{$rowIndex}/D{$rowIndex})*LOG(E{$rowIndex}/D{$rowIndex},2)) + IF(F{$rowIndex}=0,0,(F{$rowIndex}/D{$rowIndex})*LOG(F{$rowIndex}/D{$rowIndex},2))))";
    $sheet->setCellValue("G{$rowIndex}", $entropyFormula);
    $attributeData[$lastAttribute][] = $rowIndex;
    $rowIndex++;
}

// Hitung gain untuk setiap atribut
foreach ($gainData as $attribute => $data) {
    $totalCases = $data['total'];
    $weightedEntropyParts = [];
    foreach ($attributeData[$attribute] as $row) {
        $weightedEntropyParts[] = "(D{$row}/$totalCases)*G{$row}";
    }
    $weightedEntropyFormula = implode("+", $weightedEntropyParts);

    // Gain formula for the main attribute row
    $gainFormula = "={$totalEntropyCell} - ({$weightedEntropyFormula})";
    $sheet->setCellValue("H{$data['row']}", $gainFormula);
}

// Tulis spreadsheet ke file
$writer = new Xlsx($spreadsheet);
$filename = 'DataHasilC45_' . date('Ymd_His') . '.xlsx';
$filepath = __DIR__ . '/../exports/' . $filename;

// Periksa apakah direktori 'exports' ada, jika tidak, buatlah
if (!file_exists(__DIR__ . '/../exports')) {
    mkdir(__DIR__ . '/../exports', 0777, true);
}

try {
    $writer->save($filepath);

    // Arahkan ke file untuk diunduh
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    readfile($filepath);

    // Hapus file setelah diunduh
    unlink($filepath);
} catch (Exception $e) {
    // Penanganan kesalahan
    echo 'Error writing file: ', $e->getMessage();
    exit();
}

exit();
?>
