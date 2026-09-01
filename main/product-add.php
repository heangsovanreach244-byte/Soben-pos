<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// Fetch Categories for Dropdown
try {
    $stmt = $db->query("SELECT * FROM categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Soben Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF7F2; }
        .card-custom { background: #fff; border-radius: 16px; border: 1px solid rgba(200, 138, 88, 0.2); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-save { background: linear-gradient(135deg, #C88A58, #5C3D2E); color: #fff; border-radius: 10px; font-weight: 700; border: none; }
        .btn-save:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-plus-circle text-warning me-2"></i>Add New Product</h4>
                    <a href="products.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                </div>

                <form action="product-save.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Code</label>
                            <input type="text" name="product_code" class="form-control" placeholder="e.g. P004" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name</label>
                            <input type="text" name="product_name" class="form-control" placeholder="e.g. Cappuccino" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stock Qty</label>
                            <input type="number" name="qty" class="form-control" placeholder="10" value="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-save w-100 py-2.5"><i class="fa-solid fa-floppy-disk me-2"></i>Save Product</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>