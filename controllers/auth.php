<?php
session_start();
require '../config/database.php';
require '../models/user.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($user->login($username, $password)) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user->getRole($username);
        header('Location: ../views/index.php');
    } else {
        $_SESSION['error_message'] = 'Username atau Password salah';
        header('Location: ../views/login.php');
    }
    exit();
}
?>