
<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

$stmt = $db->query("
SELECT
    sh.id,
    p.product_name,
    sh.qty,
    sh.transaction_type,
    sh.transaction_date
FROM stock_history sh
INNER JOIN products p
ON sh.product_id = p.product_id
ORDER BY sh.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Stock History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

</head>

<body>

<div class="container py-4">

<h2 class="mb-4">

<i class="fa fa-history"></i>

Stock History

</h2>

<div class="card shadow">

<div class="card-body">

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Product</th>
<th>Quantity</th>
<th>Type</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['product_name']; ?></td>

<td><?php echo $row['qty']; ?></td>

<td>

<?php if($row['transaction_type']=='IN'): ?>

<span class="badge bg-success">
Stock In
</span>

<?php else: ?>

<span class="badge bg-danger">
Stock Out
</span>

<?php endif; ?>

</td>

<td>
<?php echo $row['transaction_date']; ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>

