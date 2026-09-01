<?php
require_once '../connect.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

// ទាញយកទិន្នន័យផលិតផលតាម id
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit();
}

// ទាញយកប្រភេទទំនិញ (Categories) មកបង្ហាញក្នុង Select Option
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Soben Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit Product</h4>
        </div>
        <div class="card-body p-4">
            <form action="product-update.php" method="POST" enctype="multipart/form-data">
                
                <!-- រក្សាទុក ID ដើមសម្រាប់ Update -->
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Product Code</label>
                    <input type="text" name="product_code" class="form-control" value="<?php echo htmlspecialchars($product['product_code']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Product Name</label>
                    <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Choose Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Stock Quantity</label>
                        <input type="number" name="qty" class="form-control" value="<?php echo $product['qty']; ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Current Image</label><br>
                    <img src="../uploads/products/<?php echo !empty($product['image']) ? $product['image'] : 'default-product.png'; ?>" width="80" height="80" class="rounded border mb-2" style="object-fit: cover;">
                    <input type="file" name="image" class="form-control">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success px-4">Update Product</button>
                    <a href="products.php" class="btn btn-secondary px-4">Back</a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>