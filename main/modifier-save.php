<?php
require_once '../connect.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = intval($_POST['group_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    if ($group_id > 0 && !empty($name)) {
        try {
            // កែពី $pdo មកជា $db វិញ
            $stmt = $db->prepare("INSERT INTO modifiers (group_id, name, price, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$group_id, $name, $price]);
            header("Location: modifiers.php?msg=added");
            exit;
        } catch (Exception $e) {
            die("មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ៖ " . $e->getMessage());
        }
    }
}

header("Location: modifiers.php");
exit;