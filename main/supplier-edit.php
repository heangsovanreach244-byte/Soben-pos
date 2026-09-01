<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php';

// ចាប់យក ID ពី URL (?id=... ឬ ?supplier_id=...)
$id = $_GET['id'] ?? $_GET['supplier_id'] ?? 0;

$row = null;

if (!empty($id)) {
    try {
        // ១. Query តាម Column supplier_id
        $stmt = $db->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // ២. បើរកមិនឃើញ Try id
        if (!$row) {
            $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Query Error: " . $e->getMessage();
    }
}

if (!$row) {
    $_SESSION['error'] = "Supplier not found!";
    header("Location: suppliers.php");
    exit();
}

// ចាប់យក ID ពិតប្រាកដចេញពី Database
$supplier_id   = $row['supplier_id'] ?? $row['id'] ?? 0;
$supplier_name = $row['supplier_name'] ?? $row['name'] ?? '';
$phone         = $row['phone'] ?? $row['phone_number'] ?? '';
$address       = $row['address'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-3" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header bg-warning text-dark fw-bold">
            Edit Supplier
        </div>

        <div class="card-body p-4">
            <form action="supplier-update.php" method="POST">

                <!-- HIDDEN INPUT ID -->
                <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier_id); ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Supplier Name *</label>
                    <input type="text" name="supplier_name" class="form-control" value="<?php echo htmlspecialchars($supplier_name); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-success px-4">Update</button>
                    <a href="suppliers.php" class="btn btn-secondary px-4">Back</a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>