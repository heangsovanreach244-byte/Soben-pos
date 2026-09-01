<?php
session_start();
require_once '../connect.php';
require_once '../includes/auth.php';

// Handle Add Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coupon') {
    $code           = strtoupper(trim($_POST['code']));
    $discount_type  = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_spend      = floatval($_POST['min_spend'] ?? 0);
    $expiry_date    = $_POST['expiry_date'];
    $usage_limit    = intval($_POST['usage_limit'] ?? 100);

    $stmt = $db->prepare("
        INSERT INTO coupons (code, discount_type, discount_value, min_spend, expiry_date, usage_limit)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$code, $discount_type, $discount_value, $min_spend, $expiry_date, $usage_limit]);

    header("Location: coupons.php?status=success");
    exit();
}

// Fetch Coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Coupons Management - SOBEN CAFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-ticket text-success"></i> គ្រប់គ្រងកូដប្រូម៉ូសិន (Coupons)</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCouponModal">
            <i class="fa-solid fa-plus me-1"></i> បង្កើត Coupon ថ្មី
        </button>
    </div>

    <!-- Coupons Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min Spend</th>
                            <th>Expiry Date</th>
                            <th>Used / Limit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $index => $c): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><span class="badge bg-dark text-warning fs-6"><?= htmlspecialchars($c['code']); ?></span></td>
                            <td>
                                <?= $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : '$' . number_format($c['discount_value'], 2); ?>
                            </td>
                            <td>$<?= number_format($c['min_spend'], 2); ?></td>
                            <td><?= $c['expiry_date']; ?></td>
                            <td><?= $c['used_count']; ?> / <?= $c['usage_limit']; ?></td>
                            <td>
                                <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?= ucfirst($c['status']); ?>
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

<!-- Modal Add Coupon -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="coupons.php" method="POST">
                <input type="hidden" name="action" value="add_coupon">
                <div class="modal-header">
                    <h5 class="modal-title">បន្ថែម Coupon Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="ឧ. SOBEN10" style="text-transform: uppercase;">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ប្រភេទ Discount</label>
                            <select name="discount_type" class="form-select">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">តម្លៃ Discount</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ចំណាយអប្បបរមា (Minimum Spend)</label>
                        <input type="number" step="0.01" name="min_spend" class="form-control" value="0.00">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ថ្ងៃផុតកំណត់</label>
                            <input type="date" name="expiry_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ចំនួនដងអនុញ្ញាត</label>
                            <input type="number" name="usage_limit" class="form-control" value="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-success">រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>