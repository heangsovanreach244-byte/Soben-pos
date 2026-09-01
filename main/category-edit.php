<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// ទទួល ID តាម URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid Category ID!";
    header("Location: categories.php");
    exit();
}

// ស្វែងរក Column ID (category_id ឬ id)
$idField = 'category_id';
try {
    $db->query("SELECT category_id FROM categories LIMIT 1");
} catch (PDOException $e) {
    $idField = 'id';
}

// ដំណើរការ UPDATE នៅពេល Form ចុច Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        try {
            $stmt = $db->prepare("UPDATE categories SET category_name = :name WHERE $idField = :id");
            $stmt->execute([':name' => $category_name, ':id' => $id]);
            
            $_SESSION['success'] = "Category updated successfully!";
            header("Location: categories.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error updating category: " . $e->getMessage();
        }
    } else {
        $error = "Category name cannot be empty!";
    }
}

// Fetch ទិន្នន័យ Category យកមកបង្ហាញក្នុង Input
try {
    $stmt = $db->prepare("SELECT * FROM categories WHERE $idField = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        $_SESSION['error'] = "Category not found!";
        header("Location: categories.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    header("Location: categories.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Soben Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF7F2;
            color: #231914;
        }
        .card-custom {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 12px 35px rgba(92, 61, 46, 0.08);
        }
        .btn-cafe {
            background: linear-gradient(135deg, #C88A58 0%, #5C3D2E 100%);
            color: #fff;
            border-radius: 12px;
            font-weight: 600;
        }
        .btn-cafe:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container" style="max-width: 500px;">
    <div class="card card-custom p-4">
        <h4 class="fw-bold mb-3"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Category</h4>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-semibold">Category Name</label>
                <input type="text" name="category_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="categories.php" class="btn btn-light rounded-3">Cancel</a>
                <button type="submit" class="btn btn-cafe px-4">Update Category</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>