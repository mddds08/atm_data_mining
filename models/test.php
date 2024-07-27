<?php
require 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$username = 'admin2';
$password = 'admin123'; // Replace with the desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

$query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':role', $role);
$stmt->execute();

echo "User inserted successfully.";
?>
