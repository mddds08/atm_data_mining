<?php
session_start();
require '../config/database.php';

// Koneksi ke database
$database = new Database();
$db = $database->getConnection();

// Fungsi untuk mendapatkan semua nilai atribut
function getNilaiAtribut($db)
{
    $query = "SELECT * FROM nilai_atribut";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menambah nilai atribut
function addNilaiAtribut($db, $label_atribut, $atribut_pendukung, $nilai_atribut)
{
    $query = "INSERT INTO nilai_atribut (label_atribut, atribut_pendukung, nilai_atribut) VALUES (:label_atribut, :atribut_pendukung, :nilai_atribut)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':label_atribut', $label_atribut);
    $stmt->bindParam(':atribut_pendukung', $atribut_pendukung);
    $stmt->bindParam(':nilai_atribut', $nilai_atribut);
    return $stmt->execute();
}

// Fungsi untuk mengupdate nilai atribut
function updateNilaiAtribut($db, $id, $label_atribut, $atribut_pendukung, $nilai_atribut)
{
    $query = "UPDATE nilai_atribut SET label_atribut = :label_atribut, atribut_pendukung = :atribut_pendukung, nilai_atribut = :nilai_atribut WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':label_atribut', $label_atribut);
    $stmt->bindParam(':atribut_pendukung', $atribut_pendukung);
    $stmt->bindParam(':nilai_atribut', $nilai_atribut);
    return $stmt->execute();
}

// Fungsi untuk menghapus nilai atribut
function deleteNilaiAtribut($db, $id)
{
    $query = "DELETE FROM nilai_atribut WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $label_atribut = $_POST['label_atribut'];
        $atribut_pendukung = $_POST['atribut_pendukung'];
        $nilai_atribut = $_POST['nilai_atribut'];
        if (updateNilaiAtribut($db, $id, $label_atribut, $atribut_pendukung, $nilai_atribut)) {
            $_SESSION['message'] = 'Nilai atribut berhasil diupdate!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal mengupdate nilai atribut.';
            $_SESSION['message_type'] = 'danger';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'];
        if (deleteNilaiAtribut($db, $id)) {
            $_SESSION['message'] = 'Nilai atribut berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menghapus nilai atribut.';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $label_atribut = $_POST['label_atribut'];
        $atribut_pendukung = $_POST['atribut_pendukung'];
        $nilai_atribut = $_POST['nilai_atribut'];
        if (addNilaiAtribut($db, $label_atribut, $atribut_pendukung, $nilai_atribut)) {
            $_SESSION['message'] = 'Nilai atribut berhasil ditambahkan!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menambahkan nilai atribut.';
            $_SESSION['message_type'] = 'danger';
        }
    }
}

// Redirect ke halaman nilai atribut setelah memproses form
header("Location: ../views/decision_tree/nilai_atribut.php");
exit();
?>