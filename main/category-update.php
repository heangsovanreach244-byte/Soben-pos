<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

// ១. ពិនិត្យ Auth Session
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

// ២. ពិនិត្យ REQUEST_METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: categories.php");
    exit();
}

// ៣. ទទួលយក ID (គាំទ្រ category_id, id, id_category)
$category_id = 0;
if (isset($_POST['category_id']) && intval($_POST['category_id']) > 0) {
    $category_id = intval($_POST['category_id']);
} elseif (isset($_POST['id']) && intval($_POST['id']) > 0) {
    $category_id = intval($_POST['id']);
}

// ទទួលយក Category Name (គាំទ្រ category_name, name)
$category_name = trim($_POST['category_name'] ?? $_POST['name'] ?? '');

// ៤. Update ទិន្នន័យចូល Database
if ($category_id > 0 && !empty($category_name)) {
    try {
        $stmt = $db->prepare("
            UPDATE categories
            SET category_name = ?
            WHERE category_id = ?
        ");

        $stmt->execute([
            $category_name,
            $category_id
        ]);

        $_SESSION['success'] = "Category Updated Successfully";
        header("Location: categories.php?msg=updated");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: categories.php");
        exit();
    }
} else {
    // បង្ហាញព័ត៌មានលម្អិតបើផ្ញើមកខុស
    $_SESSION['error'] = "Invalid input data! Received ID: {$category_id}, Name: '{$category_name}'";
    header("Location: categories.php");
    exit();
}
?>