<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $stmt = $db->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Supplier Deleted Successfully!";
    } catch (PDOException $e) {
        try {
            $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Supplier Deleted Successfully!";
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Delete Error: " . $ex->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Invalid Supplier ID!";
}

header("Location: suppliers.php");
exit();
?>