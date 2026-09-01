<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "soben_cafe";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // បន្ថែមបន្ទាត់នេះ ដើម្បីដោះស្រាយបញ្ហា Undefined variable $db
    $db = $conn;

} catch (PDOException $e) {
    die("Database Connection Failed : " . $e->getMessage());
}
?>