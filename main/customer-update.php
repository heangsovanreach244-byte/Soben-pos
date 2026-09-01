<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: customers.php");
    exit();
}

// ទទួលយក ID (គាំទ្រទាំង customer_id និង id)
$id = 0;
if (isset($_POST['customer_id']) && (int)$_POST['customer_id'] > 0) {
    $id = (int)$_POST['customer_id'];
} elseif (isset($_POST['id']) && (int)$_POST['id'] > 0) {
    $id = (int)$_POST['id'];
}

// ទទួលយកឈ្មោះ (គាំទ្រទាំង customer_name និង name)
$customer_name = trim($_POST['customer_name'] ?? $_POST['name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$address       = trim($_POST['address'] ?? '');

if ($id > 0 && !empty($customer_name)) {
    try {
        $stmt = $db->prepare("
            UPDATE customers
            SET
                customer_name = ?,
                phone = ?,
                address = ?
            WHERE id = ?
        ");

        $result = $stmt->execute([
            $customer_name,
            $phone,
            $address,
            $id
        ]);

        if ($result) {
            $_SESSION['success'] = "Customer Updated Successfully";
        } else {
            $_SESSION['error'] = "Failed to update customer!";
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid data submitted! (ID: $id, Name: $customer_name)";
}

header("Location: customers.php");
exit();
?>