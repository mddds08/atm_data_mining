<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

$k = isset($_POST['k']) ? (int) $_POST['k'] : 10;
$kfold_result = $atmData->performKFoldCrossValidation($k);

$_SESSION['kfold_result'] = $kfold_result;

// Redirect to result page
header('Location: ../views/decision_tree/kfold_result.php');
exit();
?>