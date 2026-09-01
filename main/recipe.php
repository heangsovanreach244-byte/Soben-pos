<?php
session_start();
require_once '../connect.php';
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $ingredientIds = $_POST['ingredient_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    $stmtDel = $db->prepare("DELETE FROM product_recipes WHERE product_id = ?");
    $stmtDel->execute([$productId]);

    $stmtIns = $db->prepare("INSERT INTO product_recipes (product_id, ingredient_id, quantity) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($ingredientIds); $i++) {
        if (!empty($ingredientIds[$i]) && !empty($quantities[$i])) {
            $stmtIns->execute([$productId, $ingredientIds[$i], $quantities[$i]]);
        }
    }
    header('Location: recipe.php?product_id=' . $productId);
    exit;
}

$products = $db->query("SELECT id, product_name FROM products ORDER BY product_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$ingredients = $db->query("SELECT * FROM ingredients ORDER BY ingredient_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$selectedProduct = $_GET['product_id'] ?? ($products[0]['id'] ?? 0);
$currentRecipe = [];
if ($selectedProduct) {
    $stmt = $db->prepare("SELECT * FROM product_recipes WHERE product_id = ?");
    $stmt->execute([$selectedProduct]);
    $currentRecipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <title>Product Recipe Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-4">
    <h2>កំណត់រូបមន្តផលិតផល (BOM)</h2>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">Dashboard</a>
    <div class="card p-3 mb-3">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label>ជ្រើសរើសផលិតផល</label>
                <select name="product_id" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $selectedProduct == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['product_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <div class="card p-3">
        <form method="POST">
            <input type="hidden" name="product_id" value="<?= $selectedProduct ?>">
            <div id="recipe-list">
                <?php if (empty($currentRecipe)): ?>
                    <div class="row g-3 mb-2 recipe-row">
                        <div class="col-md-6">
                            <select name="ingredient_id[]" class="form-control">
                                <option value="">-- ជ្រើសរើសគ្រឿងផ្សំ --</option>
                                <?php foreach ($ingredients as $ing): ?>
                                    <option value="<?= $ing['id'] ?>"><?= htmlspecialchars($ing['ingredient_name']) ?> (<?= $ing['unit'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" name="quantity[]" class="form-control" placeholder="បរិមាណ">
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($currentRecipe as $r): ?>
                        <div class="row g-3 mb-2 recipe-row">
                            <div class="col-md-6">
                                <select name="ingredient_id[]" class="form-control">
                                    <option value="">-- ជ្រើសរើសគ្រឿងផ្សំ --</option>
                                    <?php foreach ($ingredients as $ing): ?>
                                        <option value="<?= $ing['id'] ?>" <?= $ing['id'] == $r['ingredient_id'] ? 'selected' : '' ?>><?= htmlspecialchars($ing['ingredient_name']) ?> (<?= $ing['unit'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="quantity[]" class="form-control" value="<?= $r['quantity'] ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-success mt-3">រក្សាទុករូបមន្ត</button>
        </form>
    </div>
</div>
</body>
</html>