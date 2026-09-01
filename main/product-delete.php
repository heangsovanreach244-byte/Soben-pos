<?php

require_once '../connect.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

try {
    // 1. ទាញយករូបភាពដើម្បីលុបចេញពី Folder uploads
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // ប្រសិនបើមានរូបភាព និងមិនមែនជារូប default ទេ ធ្វើការលុប file រូបនោះចេញ
        if (
            !empty($product['image']) && 
            $product['image'] != 'default-product.png' && 
            file_exists("../uploads/products/" . $product['image'])
        ) {
            unlink("../uploads/products/" . $product['image']);
        }

        // 2. លុប Record នៅក្នុង Database តាម `id`
        $delete = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete->execute([$id]);
    }

    header("Location: products.php?msg=deleted");
    exit();

} catch (PDOException $e) {
    die("Delete Error: " . $e->getMessage());
}

?>