<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ពិនិត្យមើល Session Login ឱ្យត្រូវ standard ប្រព័ន្ធ SOBEN CAFE
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// Fetch all categories from Database (ការពារករណី Column ID ខុសគ្នា id ឬ category_id)
$categories = [];
try {
    // ព្យាយាម Fetch ដោយប្រើ category_id
    $stmt = $db->query("SELECT * FROM categories ORDER BY category_id DESC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        // បើបរាជ័យ ព្យាយាម Fetch ដោយប្រើ id
        $stmt = $db->query("SELECT * FROM categories ORDER BY id DESC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $_SESSION['error'] = "Database Query Error: " . $ex->getMessage();
        $categories = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - Soben Cafe</title>

    <!-- Google Fonts & Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --cafe-dark: #1E120C;
            --cafe-primary: #5C3D2E;
            --cafe-accent: #C88A58;
            --cafe-cream: #F5EBE0;
            --cafe-bg: #FAF7F2;
            --text-main: #231914;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--cafe-bg);
            color: var(--text-main);
            min-height: 100vh;
        }

        .page-header {
            background: rgba(30, 18, 12, 0.95);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 16px 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-bottom: 2px solid var(--cafe-accent);
        }

        .main-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 12px 35px rgba(92, 61, 46, 0.08);
            padding: 28px;
            transition: all 0.3s ease;
        }

        .search-box {
            background: #FAF7F2;
            border: 2px solid rgba(200, 138, 88, 0.2);
            border-radius: 14px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            max-width: 380px;
        }

        .search-box:focus-within {
            border-color: var(--cafe-accent);
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(200, 138, 88, 0.15);
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-weight: 500;
        }

        .custom-table {
            border-collapse: separate;
            border-spacing: 0 10px;
            width: 100%;
        }

        .custom-table thead tr {
            background: linear-gradient(135deg, var(--cafe-dark) 0%, #351F14 100%);
            color: #ffffff;
        }

        .custom-table th {
            padding: 16px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border: none;
        }

        .custom-table th:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .custom-table th:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .custom-table tbody tr {
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(200, 138, 88, 0.1);
        }

        .custom-table tbody tr:hover {
            transform: translateY(-3px) scale(1.002);
            box-shadow: 0 10px 20px rgba(92, 61, 46, 0.12);
            border-color: var(--cafe-accent);
        }

        .custom-table td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.95rem;
            border-top: 1px solid #f8f3ed;
            border-bottom: 1px solid #f8f3ed;
        }

        .custom-table td:first-child {
            border-left: 1px solid #f8f3ed;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .custom-table td:last-child {
            border-right: 1px solid #f8f3ed;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .category-icon-box {
            width: 42px;
            height: 42px;
            background: var(--cafe-cream);
            color: var(--cafe-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            border: 1px solid rgba(200, 138, 88, 0.3);
            transition: all 0.3s ease;
        }

        .custom-table tbody tr:hover .category-icon-box {
            background: var(--cafe-accent);
            color: #ffffff;
            transform: rotate(-8deg) scale(1.1);
        }

        .btn-add-category {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 700;
            border: none;
            box-shadow: 0 8px 20px rgba(92, 61, 46, 0.25);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-category:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(92, 61, 46, 0.35);
            color: #fff;
            filter: brightness(1.05);
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }

        .btn-action:hover {
            transform: scale(1.15);
        }

        .btn-edit {
            background-color: #FFF8E7;
            color: #D97706;
        }

        .btn-edit:hover {
            background-color: #D97706;
            color: #fff;
        }

        .btn-delete {
            background-color: #FEE2E2;
            color: #DC2626;
        }

        .btn-delete:hover {
            background-color: #DC2626;
            color: #fff;
        }
    </style>
</head>

<body>

<!-- Header Bar -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-tags text-warning animate__animated animate__bounceIn"></i> Categories Management
        </h4>
    </div>
    <!-- បូតុងបើក Modal សម្រាប់ Add Category -->
    <button type="button" class="btn btn-add-category d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fa-solid fa-plus"></i> Add Category
    </button>
</div>

<div class="container-fluid px-4 mb-5">
    
    <!-- Main Card -->
    <div class="main-card animate__animated animate__fadeIn">
        
        <!-- Status Notification -->
        <?php if (isset($_GET['msg']) || isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php 
                    if (isset($_SESSION['success'])) {
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    } elseif (isset($_GET['msg']) && $_GET['msg'] == 'added') echo "Category added successfully!";
                    elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated') echo "Category updated successfully!";
                    elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "Category deleted successfully!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search & Info Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="search-box d-flex align-items-center gap-2 w-100">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                <input type="text" id="searchInput" placeholder="Search category name..." onkeyup="filterCategories()">
            </div>
            <div class="text-muted fw-semibold small">
                Total Categories: <span class="badge bg-dark rounded-pill" id="totalCount"><?php echo count($categories); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th scope="col" style="width: 10%;">ID</th>
                        <th scope="col" style="width: 65%;">Category Name</th>
                        <th scope="col" class="text-center" style="width: 25%;">Action</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $index => $cat): 
                            // ស្វែងរក ID ដែលមានក្នុង Table ស្វ័យប្រវត្តិ
                            $catId = $cat['category_id'] ?? $cat['id'] ?? ($index + 1);
                        ?>
                            <tr class="category-row animate__animated animate__fadeInUp" style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                                <td class="fw-bold text-muted">#<?php echo $catId; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="category-icon-box">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>
                                        <span class="fw-bold text-dark fs-6 category-name"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="category-edit.php?id=<?php echo $catId; ?>" class="btn-action btn-edit" title="Edit Category">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="category-delete.php?id=<?php echo $catId; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this category?');" title="Delete Category">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">No categories found. Click "Add Category" to create one.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Modal សម្រាប់ Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- ចុច Save ទៅ category-save.php -->
            <form action="category-save.php" method="POST">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name</label>
                        <input type="text" name="category_name" class="form-control form-control-lg" placeholder="e.g. Espresso, Bakery, Milk Tea" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-category">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterCategories() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.category-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.category-name').textContent.toLowerCase();
            if (name.includes(input)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('totalCount').textContent = visibleCount;
    }
</script>
</body>
</html>