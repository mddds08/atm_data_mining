<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

// Get all data
$stmt = $atmData->getAllData();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Store data in session to use in view
$_SESSION['atm_data'] = $data;

// Redirect to attributes page
header('Location: ../views/decision_tree/attributes.php');
exit();
?>
