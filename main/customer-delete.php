<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $db->prepare("
            DELETE FROM customers
            WHERE id = ?
        ");

        $result = $stmt->execute([$id]);

        if ($result && $stmt->rowCount() > 0) {
            $_SESSION['success'] = "Customer Deleted Successfully";
        } else {
            $_SESSION['error'] = "Customer not found or already deleted!";
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Cannot delete customer: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid Customer ID!";
}

header("Location: customers.php");
exit();
?>