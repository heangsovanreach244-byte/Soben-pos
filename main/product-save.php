<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = trim($_POST['product_code'] ?? '');
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $price        = floatval($_POST['price'] ?? 0);
    $qty          = intval($_POST['qty'] ?? 0);
    $image_name   = 'default-product.png';

    // Upload Image Processing
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $destPath    = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_name = $newFileName;
            }
        }
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO products (product_code, product_name, category_id, price, qty, image) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$product_code, $product_name, $category_id, $price, $qty, $image_name]);

        header("Location: products.php?msg=added");
        exit();

    } catch (PDOException $e) {
        die("Error saving product: " . $e->getMessage());
    }
} else {
    header("Location: products.php");
    exit();
}
?>