<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: suppliers.php");
    exit();
}

// ទទួល ID និង ទិន្នន័យពី Form
$supplier_id   = trim($_POST['supplier_id'] ?? $_POST['id'] ?? '');
$supplier_name = trim($_POST['supplier_name'] ?? $_POST['name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$address       = trim($_POST['address'] ?? '');

if (empty($supplier_id)) {
    $_SESSION['error'] = "Update Failed: Missing Supplier ID!";
    header("Location: suppliers.php");
    exit();
}

if (empty($supplier_name)) {
    $_SESSION['error'] = "Update Failed: Supplier Name is required!";
    header("Location: suppliers.php");
    exit();
}

$updated = false;

// Attempt 1: UPDATE (supplier_name, phone, address WHERE supplier_id)
try {
    $stmt = $db->prepare("UPDATE suppliers SET supplier_name = ?, phone = ?, address = ? WHERE supplier_id = ?");
    $stmt->execute([$supplier_name, $phone, $address, $supplier_id]);
    $updated = true;
} catch (PDOException $e1) {
    // Attempt 2: UPDATE (name, phone, address WHERE id)
    try {
        $stmt = $db->prepare("UPDATE suppliers SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$supplier_name, $phone, $address, $supplier_id]);
        $updated = true;
    } catch (PDOException $e2) {
        // Attempt 3: UPDATE (supplier_name, phone, address WHERE id)
        try {
            $stmt = $db->prepare("UPDATE suppliers SET supplier_name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$supplier_name, $phone, $address, $supplier_id]);
            $updated = true;
        } catch (PDOException $e3) {
            $_SESSION['error'] = "Update Failed: " . $e3->getMessage();
        }
    }
}

if ($updated && !isset($_SESSION['error'])) {
    $_SESSION['success'] = "Supplier Updated Successfully!";
}

header("Location: suppliers.php");
exit();
?>