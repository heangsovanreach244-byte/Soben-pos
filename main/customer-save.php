<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: customers.php");
    exit();
}

// កែប្រែត្រង់នេះ៖ ទទួលយក $_POST['name'] ឲ្យត្រូវតាម HTML Form
$customer_name = trim($_POST['name'] ?? $_POST['customer_name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$address       = trim($_POST['address'] ?? '');

if (!empty($customer_name)) {
    try {
        // ១. សាកល្បង Insert តាម Structure ទី ១ (customer_name)
        $stmt = $db->prepare("
            INSERT INTO customers (customer_name, phone, address)
            VALUES (?, ?, ?)
        ");

        $result = $stmt->execute([
            $customer_name,
            $phone,
            $address
        ]);

        if ($result) {
            $_SESSION['success'] = "Customer Added Successfully";
        } else {
            $_SESSION['error'] = "Failed to add customer!";
        }

    } catch (PDOException $e) {
        try {
            // ២. ប្រសិនបើ Database ប្រើ Column name ជំនួស customer_name
            $stmt = $db->prepare("
                INSERT INTO customers (name, phone, address)
                VALUES (?, ?, ?)
            ");

            $result = $stmt->execute([
                $customer_name,
                $phone,
                $address
            ]);

            if ($result) {
                $_SESSION['success'] = "Customer Added Successfully";
            } else {
                $_SESSION['error'] = "Failed to add customer!";
            }
        } catch (PDOException $ex) {
            $_SESSION['error'] = "Database Error: " . $ex->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Customer name is required!";
}

header("Location: customers.php");
exit();
?>