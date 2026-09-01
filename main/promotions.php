<?php
session_start();
require_once '../connect.php';
require_once '../includes/auth.php';

// Handle Add Promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_promo') {
    $promo_name     = trim($_POST['promo_name']);
    $promo_type     = $_POST['promo_type'];
    $buy_product_id = !empty($_POST['buy_product_id']) ? intval($_POST['buy_product_id']) : NULL;
    $buy_qty        = intval($_POST['buy_qty'] ?? 1);
    $get_product_id = !empty($_POST['get_product_id']) ? intval($_POST['get_product_id']) : NULL;
    $get_qty        = intval($_POST['get_qty'] ?? 1);
    $discount_type  = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value'] ?? 0);
    $start_date     = $_POST['start_date'];
    $end_date       = $_POST['end_date'];

    $stmt = $db->prepare("
        INSERT INTO promotions 
        (promo_name, promo_type, buy_product_id, buy_qty, get_product_id, get_qty, discount_type, discount_value, start_date, end_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$promo_name, $promo_type, $buy_product_id, $buy_qty, $get_product_id, $get_qty, $discount_type, $discount_value, $start_date, $end_date]);
    
    header("Location: promotions.php?status=success");
    exit();
}

// Fetch Products for Dropdown
$products = $db->query("SELECT id, product_name FROM products ORDER BY product_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch All Promotions
$promotions = $db->query("
    SELECT p.*, 
           p1.product_name AS buy_product_name, 
           p2.product_name AS get_product_name 
    FROM promotions p
    LEFT JOIN products p1 ON p.buy_product_id = p1.id
    LEFT JOIN products p2 ON p.get_product_id = p2.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Promotions Management - SOBEN CAFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-tags text-primary"></i> គ្រប់គ្រងកម្មវិធីប្រូម៉ូសិន (Promotions)</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromoModal">
            <i class="fa-solid fa-plus me-1"></i> បង្កើតប្រូម៉ូសិនថ្មី
        </button>
    </div>

    <!-- Promotions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>ឈ្មោះប្រូម៉ូសិន</th>
                            <th>ប្រភេទ</th>
                            <th>លក្ខខណ្ឌ (Buy / Discount)</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ស្ថានភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promotions as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><strong><?= htmlspecialchars($p['promo_name']); ?></strong></td>
                            <td>
                                <span class="badge bg-<?= $p['promo_type'] === 'buy_x_get_y' ? 'info' : 'warning'; ?>">
                                    <?= $p['promo_type'] === 'buy_x_get_y' ? 'Buy X Get Y' : 'Cart Discount'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['promo_type'] === 'buy_x_get_y'): ?>
                                    ទិញ <strong><?= $p['buy_qty']; ?></strong> <?= htmlspecialchars($p['buy_product_name']); ?> 
                                    => ថែម <strong><?= $p['get_qty']; ?></strong> <?= htmlspecialchars($p['get_product_name']); ?> Free
                                <?php else: ?>
                                    ចុះថ្លៃ <?= $p['discount_type'] === 'percent' ? $p['discount_value'] . '%' : '$' . number_format($p['discount_value'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td><small><?= $p['start_date']; ?> ដល់ <?= $p['end_date']; ?></small></td>
                            <td>
                                <span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?= ucfirst($p['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Promotion -->
<div class="modal fade" id="addPromoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="promotions.php" method="POST">
                <input type="hidden" name="action" value="add_promo">
                <div class="modal-header">
                    <h5 class="modal-title">បន្ថែមកម្មវិធីប្រូម៉ូសិន</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ឈ្មោះប្រូម៉ូសិន</label>
                        <input type="text" name="promo_name" class="form-control" required placeholder="ឧ. Buy 2 Coffee Get 1 Cake Free">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ប្រភេទប្រូម៉ូសិន</label>
                            <select name="promo_type" id="promo_type" class="form-select" onchange="togglePromoFields()">
                                <option value="buy_x_get_y">Buy X Get Y Free</option>
                                <option value="cart_discount">Cart Discount</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buy X Get Y Section -->
                    <div id="buy_x_get_y_section" class="row border rounded p-3 mb-3 bg-light">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">ទំនិញត្រូវទិញ (Buy)</label>
                            <select name="buy_product_id" class="form-select">
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?= $prod['id']; ?>"><?= htmlspecialchars($prod['product_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">ចំនួនត្រូវទិញ</label>
                            <input type="number" name="buy_qty" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ទំនិញត្រូវថែម (Get Free)</label>
                            <select name="get_product_id" class="form-select">
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?= $prod['id']; ?>"><?= htmlspecialchars($prod['product_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ចំនួនត្រូវថែម</label>
                            <input type="number" name="get_qty" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <!-- Cart Discount Section -->
                    <div id="cart_discount_section" class="row border rounded p-3 mb-3 bg-light d-none">
                        <div class="col-md-6">
                            <label class="form-label">ប្រភេទ Discount</label>
                            <select name="discount_type" class="form-select">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">តម្លៃ Discount</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control" value="0.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">ថ្ងៃចាប់ផ្តើម</label>
                            <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ថ្ងៃបញ្ចប់</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePromoFields() {
    let type = document.getElementById('promo_type').value;
    if (type === 'buy_x_get_y') {
        document.getElementById('buy_x_get_y_section').classList.remove('d-none');
        document.getElementById('cart_discount_section').classList.add('d-none');
    } else {
        document.getElementById('buy_x_get_y_section').classList.add('d-none');
        document.getElementById('cart_discount_section').classList.remove('d-none');
    }
}
</script>
</body>
</html>