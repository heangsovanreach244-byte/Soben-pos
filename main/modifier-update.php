<?php
require_once '../connect.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $group_id = intval($_POST['group_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if ($id > 0 && $group_id > 0 && !empty($name)) {
        try {
            // កែពី $pdo មកជា $db វិញ
            $stmt = $db->prepare("UPDATE modifiers SET group_id = ?, name = ?, price = ?, status = ? WHERE id = ?");
            $stmt->execute([$group_id, $name, $price, $status, $id]);
            header("Location: modifiers.php?msg=updated");
            exit;
        } catch (Exception $e) {
            die("មានបញ្ហាក្នុងការកែប្រែទិន្នន័យ៖ " . $e->getMessage());
        }
    }
}

header("Location: modifiers.php");
exit;