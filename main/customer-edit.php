<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid Customer ID!";
    header("Location: customers.php");
    exit();
}

try {
    $stmt = $db->prepare("
        SELECT *
        FROM customers
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['error'] = "Customer not found!";
        header("Location: customers.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    header("Location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-dark text-white fw-bold">
            Edit Customer #<?php echo (int)$row['id']; ?>
        </div>
        <div class="card-body">
            <form action="customer-update.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo (int)$row['id']; ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($row['customer_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($row['address'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="customers.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>