<?php
session_start();
require_once '../connect.php';
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
}

$lowStockItems = $db->query("SELECT * FROM ingredients WHERE stock_qty <= alert_qty ORDER BY stock_qty ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <title>Low Stock Alerts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-4">
    <h2>ការព្រមានគ្រឿងផ្សំជិតអស់ស្តុក (Low Stock Alert)</h2>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">Dashboard</a>
    <div class="card p-3">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ឈ្មោះគ្រឿងផ្សំ</th>
                    <th>ស្តុកបច្ចុប្បន្ន</th>
                    <th>កម្រិតព្រមាន</th>
                    <th>ឯកតា</th>
                    <th>ស្ថានភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lowStockItems)): ?>
                    <tr>
                        <td colspan="6" class="text-center">គ្មានគ្រឿងផ្សំជិតអស់ស្តុកទេ។</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lowStockItems as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['ingredient_name']) ?></td>
                        <td class="text-danger fw-bold"><?= $row['stock_qty'] ?></td>
                        <td><?= $row['alert_qty'] ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><span class="badge bg-danger">ជិតអស់ (Need Order)</span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>