<?php
require_once '../connect.php';
require_once '../includes/auth.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // កែពី $pdo មកជា $db វិញ
        $stmt = $db->prepare("DELETE FROM modifiers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: modifiers.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        die("មានបញ្ហាក្នុងការលុបទិន្នន័យ៖ " . $e->getMessage());
    }
}

header("Location: modifiers.php");
exit;