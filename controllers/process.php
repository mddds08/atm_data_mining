<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['fileUpload'])) {
        $file = $_FILES['fileUpload']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $data = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $lokasi_atm = $sheet->getCell('A' . $row)->getValue();
            $jarak_tempuh = $sheet->getCell('B' . $row)->getValue();
            $level_saldo = $sheet->getCell('C' . $row)->getValue();
            $status_isi = $sheet->getCell('D' . $row)->getValue();

            // Validate and sanitize inputs
            $jarak_tempuh = is_numeric($jarak_tempuh) ? (float) $jarak_tempuh : null;
            $level_saldo = is_numeric($level_saldo) ? (float) $level_saldo : null;
            $status_isi = strtolower($status_isi) == 'isi' ? 1 : 0; // Convert 'ISI' to 1 and 'TIDAK ISI' to 0

            if ($jarak_tempuh !== null && $level_saldo !== null) {
                $data[] = [
                    'lokasi_atm' => $lokasi_atm,
                    'jarak_tempuh' => $jarak_tempuh,
                    'level_saldo' => $level_saldo,
                    'status_isi' => $status_isi
                ];
            }
        }

        // Save to database
        if ($atmData->saveBatch($data)) {
            $_SESSION['message'] = "Data berhasil diupload.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal mengupload data.";
            $_SESSION['message_type'] = "danger";
        }

        // Redirect to dataset page
        header('Location: ../views/decision_tree/dataset.php');
        exit();
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
        // Hapus semua data
        if ($atmData->deleteAllData()) {
            $_SESSION['message'] = 'Semua data berhasil dihapus.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menghapus semua data.';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: ../views/decision_tree/dataset.php');
        exit();
    }
}
?>