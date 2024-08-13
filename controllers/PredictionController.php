<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/atmData.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lokasi_atm'])) {
    $database = new Database();
    $db = $database->getConnection();
    $atmData = new ATMData($db);

    $lokasi_atm = $_POST['lokasi_atm'];
    $data = $atmData->getDataByLocation($lokasi_atm);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Data tidak ditemukan untuk lokasi ini."]);
        exit;
    }

    $level_saldo = categorizeLevelSaldo($data['level_saldo']);
    $jarak_tempuh = categorizeJarakTempuh($data['jarak_tempuh']);

    // Logika prediksi
    $prediction = determinePrediction($level_saldo, $jarak_tempuh);

    // Kirim respons dengan detail
    echo json_encode([
        "status" => "success",
        "prediction" => $prediction,
        "details" => [
            "lokasi_atm" => $lokasi_atm,
            "level_saldo" => $level_saldo,
            "jarak_tempuh" => $jarak_tempuh
        ]
    ]);
} else {
    // echo json_encode(["status" => "error", "message" => "Request tidak valid."]);
}

function categorizeLevelSaldo($level_saldo)
{
    if ($level_saldo <= 39) {
        return 'Rendah';
    } else {
        return 'Tinggi';
    }
}

function categorizeJarakTempuh($jarak_tempuh)
{
    if ($jarak_tempuh <= 10) {
        return 'Dekat';
    } else {
        return 'Jauh';
    }
}
function determinePrediction($level_saldo, $jarak_tempuh)
{
    if ($level_saldo == 'Rendah') {
        return 'Isi';
    } elseif ($level_saldo == 'Rendah' && $jarak_tempuh == 'Dekat') {
        return 'Isi';
    } elseif ($level_saldo == 'Rendah' && $jarak_tempuh == 'Jauh') {
        return 'Tidak Isi';
    } elseif ($level_saldo == 'Tinggi') {
        return 'Tidak Isi';
    }
}
?>