<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

// ១. ពិនិត្យ Session Login ឱ្យត្រូវ standard ប្រព័ន្ធ SOBEN CAFE
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

// ២. ពិនិត្យមើល REQUEST_METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: categories.php");
    exit();
}

// ៣. យកទិន្នន័យ និងសម្អាត Space
$category_name = trim($_POST['category_name'] ?? '');

// ៤. ផ្ទៀងផ្ទាត់ថាគ្មាន Field ទទេ
if (!empty($category_name)) {
    try {
        $stmt = $db->prepare("
            INSERT INTO categories (category_name)
            VALUES (?)
        ");

        $stmt->execute([$category_name]);

        $_SESSION['success'] = "Category Added Successfully";
        header("Location: categories.php?msg=added");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: categories.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Category name cannot be empty";
    // កែតម្រូវ Redirect មក categories.php វិញ (ព្រោះគ្មាន add_category.php)
    header("Location: categories.php?error=empty");
    exit();
}
?>