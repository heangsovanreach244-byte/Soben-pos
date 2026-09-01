
<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

$products = $db->query("
SELECT *
FROM products
ORDER BY product_name ASC
");

if(isset($_POST['stock_in'])){

    $product_id = $_POST['product_id'];
    $qty        = $_POST['qty'];

    $stmt = $db->prepare("
    UPDATE products
    SET qty = qty + ?
    WHERE product_id = ?
    ");

    $stmt->execute([
        $qty,
        $product_id
    ]);

    $history = $db->prepare("
    INSERT INTO stock_history
    (
        product_id,
        qty,
        transaction_type,
        transaction_date
    )
    VALUES
    (
        ?,?,
        'IN',
        NOW()
    )
    ");

    $history->execute([
        $product_id,
        $qty
    ]);

    $_SESSION['success'] = "Stock Added Successfully";

    header("Location: stock-in.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Stock In</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container py-4">

<h2 class="mb-4">
Stock In Management
</h2>

<div class="card">

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6">

<label>Product</label>

<select
name="product_id"
class="form-control"
required>

<option value="">
Select Product
</option>

<?php while($row=$products->fetch(PDO::FETCH_ASSOC)): ?>

<option value="<?php echo $row['product_id']; ?>">

<?php echo $row['product_name']; ?>

</option>

<?php endwhile; ?>

</select>

</div>

<div class="col-md-4">

<label>Quantity</label>

<input
type="number"
name="qty"
class="form-control"
required>

</div>

<div class="col-md-2">

<label>&nbsp;</label>

<button
type="submit"
name="stock_in"
class="btn btn-success w-100">

Save

</button>

</div>

</div>

</form>

</div>

</div>

</div>

</body>

</html>

