<?php
require_once '../connect.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: products.php");
    exit();
}

$id           = intval($_POST['id'] ?? $_POST['product_id'] ?? 0);
$product_code = trim($_POST['product_code'] ?? '');
$product_name = trim($_POST['product_name'] ?? '');
$category_id  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
$price        = floatval($_POST['price'] ?? 0);
$qty          = intval($_POST['qty'] ?? 0);

try {
    // 1. ទាញយករូបភាពចាស់មកប្រើជាមុន
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: products.php");
        exit();
    }

    $image = $product['image'];

    // 2. ប្រសិនបើមាន Upload រូបភាពថ្មី
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/products/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImage = time() . '_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {
            // លុបរូបចាស់ចេញ ប្រសិនបើមិនមែន default
            if (!empty($image) && $image != 'default-product.png' && file_exists($uploadDir . $image)) {
                unlink($uploadDir . $image);
            }
            $image = $newImage;
        }
    }

    // 3. ធ្វើការ Update ទិន្នន័យ
    $updateStmt = $conn->prepare("
        UPDATE products
        SET
            product_code = ?,
            product_name = ?,
            category_id  = ?,
            price        = ?,
            qty          = ?,
            image        = ?
        WHERE id = ?
    ");

    $updateStmt->execute([
        $product_code,
        $product_name,
        $category_id,
        $price,
        $qty,
        $image,
        $id
    ]);

    $_SESSION['success'] = "Product Updated Successfully";

    header("Location: products.php?msg=updated");
    exit();

} catch (PDOException $e) {
    die("Update Error: " . $e->getMessage());
}
?>