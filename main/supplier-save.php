<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: suppliers.php");
    exit();
}

$supplier_name = trim($_POST['supplier_name'] ?? $_POST['name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$address       = trim($_POST['address'] ?? '');

if (!empty($supplier_name)) {
    try {
        $stmt = $db->prepare("INSERT INTO suppliers (supplier_name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$supplier_name, $phone, $address]);
        $_SESSION['success'] = "Supplier Added Successfully!";
    } catch (PDOException $e) {
        try {
            // fallback ករណី DB ប្រើ Column 'name' ជំនួស 'supplier_name'
            $stmt = $db->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
            $stmt->execute([$supplier_name, $phone, $address]);
            $_SESSION['success'] = "Supplier Added Successfully!";
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Database Error: " . $ex->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Supplier name is required!";
}

header("Location: suppliers.php");
exit();
?>