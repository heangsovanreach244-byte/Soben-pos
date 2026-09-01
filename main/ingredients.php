<?php
session_start();
require_once '../connect.php';
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['ingredient_name']);
    $unit = trim($_POST['unit']);
    $stock = floatval($_POST['stock_qty']);
    $alert = floatval($_POST['alert_qty']);

    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE ingredients SET ingredient_name = ?, unit = ?, stock_qty = ?, alert_qty = ? WHERE id = ?");
        $stmt->execute([$name, $unit, $stock, $alert, $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO ingredients (ingredient_name, unit, stock_qty, alert_qty) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $unit, $stock, $alert]);
    }
    header('Location: ingredients.php');
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM ingredients WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: ingredients.php');
    exit;
}

$ingredients = $db->query("SELECT * FROM ingredients ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM ingredients WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Ingredients Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-4">
    <h2 class="mb-3">Ingredients Management</h2>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">Dashboard</a>
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Ingredient Name</label>
                        <input type="text" name="ingredient_name" class="form-control" value="<?= htmlspecialchars($editData['ingredient_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit (g, ml, pcs)</label>
                        <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($editData['unit'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" step="0.01" name="stock_qty" class="form-control" value="<?= $editData['stock_qty'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alert Qty</label>
                        <input type="number" step="0.01" name="alert_qty" class="form-control" value="<?= $editData['alert_qty'] ?? '10.00' ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-3">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Alert Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['ingredient_name']) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= $row['stock_qty'] ?></td>
                            <td><?= $row['alert_qty'] ?></td>
                            <td>
                                <a href="ingredients.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="ingredients.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>