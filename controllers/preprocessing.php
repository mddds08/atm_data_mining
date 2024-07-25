<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

// Preprocessing: Remove duplicates and fill missing values
$data = $atmData->getDataForC45();

// Remove duplicates
$data = array_unique($data, SORT_REGULAR);

// Fill missing values (simple example: replace with mean or mode)
$fill_value = 0; // Example, you can implement mean/mode based on your data
foreach ($data as &$row) {
    foreach ($row as $key => $value) {
        if ($value === null || $value === '') {
            $row[$key] = $fill_value;
        }
    }
}

// Save preprocessed data back to database
$atmData->deleteAllData();
$atmData->saveBatch($data);

$_SESSION['message'] = "Preprocessing berhasil dilakukan.";
$_SESSION['message_type'] = "success";

// Redirect to dataset page
header('Location: ../views/decision_tree/dataset.php');
exit();
?>