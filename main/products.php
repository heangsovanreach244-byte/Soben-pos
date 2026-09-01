<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// Fetch Products from Database
try {
    $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Soben Cafe</title>

    <!-- Google Fonts & Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --cafe-dark: #1E120C;
            --cafe-primary: #5C3D2E;
            --cafe-accent: #C88A58;
            --cafe-cream: #F5EBE0;
            --cafe-bg: #FAF7F2;
            --text-main: #231914;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--cafe-bg);
            color: var(--text-main);
            min-height: 100vh;
        }

        .page-header {
            background: rgba(30, 18, 12, 0.95);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 16px 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-bottom: 2px solid var(--cafe-accent);
        }

        .main-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 12px 35px rgba(92, 61, 46, 0.08);
            padding: 28px;
            transition: all 0.3s ease;
        }

        .search-box {
            background: #FAF7F2;
            border: 2px solid rgba(200, 138, 88, 0.2);
            border-radius: 14px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            max-width: 380px;
        }

        .search-box:focus-within {
            border-color: var(--cafe-accent);
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(200, 138, 88, 0.15);
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-weight: 500;
        }

        .custom-table {
            border-collapse: separate;
            border-spacing: 0 10px;
            width: 100%;
        }

        .custom-table thead tr {
            background: linear-gradient(135deg, var(--cafe-dark) 0%, #351F14 100%);
            color: #ffffff;
        }

        .custom-table th {
            padding: 16px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border: none;
        }

        .custom-table th:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .custom-table th:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .custom-table tbody tr {
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(200, 138, 88, 0.1);
        }

        .custom-table tbody tr:hover {
            transform: translateY(-3px) scale(1.002);
            box-shadow: 0 10px 20px rgba(92, 61, 46, 0.12);
            border-color: var(--cafe-accent);
        }

        .custom-table td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.95rem;
            border-top: 1px solid #f8f3ed;
            border-bottom: 1px solid #f8f3ed;
        }

        .custom-table td:first-child {
            border-left: 1px solid #f8f3ed;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .custom-table td:last-child {
            border-right: 1px solid #f8f3ed;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .product-img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--cafe-cream);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .product-img:hover {
            transform: scale(1.2) rotate(-3deg);
        }

        .btn-add-product {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 700;
            border: none;
            box-shadow: 0 8px 20px rgba(92, 61, 46, 0.25);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(92, 61, 46, 0.35);
            color: #fff;
            filter: brightness(1.05);
        }

        .code-badge {
            background-color: var(--cafe-cream);
            color: var(--cafe-primary);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            border: 1px dashed rgba(200, 138, 88, 0.4);
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }

        .btn-action:hover {
            transform: scale(1.15);
        }

        .btn-edit {
            background-color: #FFF8E7;
            color: #D97706;
        }

        .btn-edit:hover {
            background-color: #D97706;
            color: #fff;
        }

        .btn-delete {
            background-color: #FEE2E2;
            color: #DC2626;
        }

        .btn-delete:hover {
            background-color: #DC2626;
            color: #fff;
        }

        .stock-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .stock-high {
            background-color: #DCFCE7;
            color: #15803D;
        }
        .stock-low {
            background-color: #FEE2E2;
            color: #B91C1C;
        }
    </style>
</head>

<body>

<!-- Header Bar -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-mug-hot text-warning animate__animated animate__bounceIn"></i> Products Management
        </h4>
    </div>
    <a href="product-add.php" class="btn btn-add-product d-flex align-items-center gap-2">
        <i class="fa-solid fa-plus"></i> Add Product
    </a>
</div>

<div class="container-fluid px-4 mb-5">
    
    <div class="main-card animate__animated animate__fadeIn">
        
        <!-- Search & Status Notification Bar -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php 
                    if ($_GET['msg'] == 'added') echo "Product added successfully!";
                    elseif ($_GET['msg'] == 'updated') echo "Product updated successfully!";
                    elseif ($_GET['msg'] == 'deleted') echo "Product deleted successfully!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="search-box d-flex align-items-center gap-2 w-100">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                <input type="text" id="searchInput" placeholder="Search product name or code..." onkeyup="filterProducts()">
            </div>
            <div class="text-muted fw-semibold small">
                Total Products: <span class="badge bg-dark rounded-pill"><?php echo count($products); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table align-middle" id="productsTable">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%;">ID</th>
                        <th scope="col" style="width: 10%;">Image</th>
                        <th scope="col" style="width: 15%;">Code</th>
                        <th scope="col" style="width: 25%;">Product Name</th>
                        <th scope="col" style="width: 12%;">Cost</th>
                        <th scope="col" style="width: 12%;">Price</th>
                        <th scope="col" style="width: 11%;">Stock</th>
                        <th scope="col" class="text-center" style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $index => $item): 
                            $p_id    = $item['id'] ?? $item['product_id'] ?? 0;
                            $p_name  = $item['product_name'] ?? 'N/A';
                            $p_code  = $item['product_code'] ?? 'N/A';
                            $p_cost  = $item['cost'] ?? 0;
                            $p_price = $item['price'] ?? 0;
                            $p_stock = $item['qty'] ?? $item['stock'] ?? 0;
                            
                            $img_file = !empty($item['image']) ? $item['image'] : 'default-product.png';
                            $img_src  = '../uploads/products/' . $img_file;
                        ?>
                            <tr class="product-row animate__animated animate__fadeInUp" style="animation-delay: <?php echo min($index * 0.04, 0.4); ?>s;">
                                <td class="fw-bold text-muted">#<?php echo htmlspecialchars($p_id); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Product" class="product-img" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=100&auto=format&fit=crop&q=80'">
                                </td>
                                <td><span class="code-badge"><i class="fa-solid fa-barcode me-1"></i><span class="search-code"><?php echo htmlspecialchars($p_code); ?></span></span></td>
                                <td class="fw-bold text-dark search-name"><?php echo htmlspecialchars($p_name); ?></td>
                                <td class="text-muted">$<?php echo number_format($p_cost, 2); ?></td>
                                <td class="fw-bold text-success fs-6">$<?php echo number_format($p_price, 2); ?></td>
                                <td>
                                    <span class="stock-pill <?php echo $p_stock > 10 ? 'stock-high' : 'stock-low'; ?>">
                                        <?php echo htmlspecialchars($p_stock); ?> units
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="product-edit.php?id=<?php echo $p_id; ?>" class="btn-action btn-edit" title="Edit Product">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="product-delete.php?id=<?php echo $p_id; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this product?');" title="Delete Product">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fs-1 mb-2 opacity-40 d-block"></i>
                                No products found. Click "+ Add Product" to create one.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterProducts() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.product-row');

        rows.forEach(row => {
            const name = row.querySelector('.search-name').textContent.toLowerCase();
            const code = row.querySelector('.search-code').textContent.toLowerCase();
            
            if (name.includes(input) || code.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>