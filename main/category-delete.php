<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // ស្វែងរក Column ID (category_id ឬ id)
    $idField = 'category_id';
    try {
        $db->query("SELECT category_id FROM categories LIMIT 1");
    } catch (PDOException $e) {
        $idField = 'id';
    }

    try {
        $stmt = $db->prepare("DELETE FROM categories WHERE $idField = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "Category deleted successfully!";
    } catch (PDOException $e) {
        // ការពារករណី Category នេះកំពុងជាប់ Foreign Key ជាមួយ Product
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "Cannot delete: This category is associated with existing products!";
        } else {
            $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Invalid Category ID!";
}

header("Location: categories.php");
exit();