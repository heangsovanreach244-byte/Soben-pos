<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ពិនិត្យ Authentication
if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
} else if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// ទាញយកទិន្នន័យ Supplier ពី Database
$suppliers = [];
try {
    $stmt = $db->query("SELECT * FROM suppliers ORDER BY supplier_id DESC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $stmt = $db->query("SELECT * FROM suppliers ORDER BY id DESC");
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $_SESSION['error'] = "Query Error: " . $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management - Soben Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
        }

        .search-box {
            background: #FAF7F2;
            border: 2px solid rgba(200, 138, 88, 0.2);
            border-radius: 14px;
            padding: 10px 20px;
            max-width: 380px;
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
            border: none;
        }

        .custom-table th:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .custom-table th:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        .custom-table tbody tr {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(200, 138, 88, 0.1);
        }

        .custom-table td {
            padding: 16px;
            vertical-align: middle;
            border-top: 1px solid #f8f3ed;
            border-bottom: 1px solid #f8f3ed;
        }

        .custom-table td:first-child { border-left: 1px solid #f8f3ed; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .custom-table td:last-child { border-right: 1px solid #f8f3ed; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        .supplier-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .btn-add-supplier {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 700;
            border: none;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            text-decoration: none;
        }

        .btn-edit { background-color: #FFF8E7; color: #D97706; }
        .btn-edit:hover { background-color: #D97706; color: #fff; }
        .btn-delete { background-color: #FEE2E2; color: #DC2626; }
        .btn-delete:hover { background-color: #DC2626; color: #fff; }
    </style>
</head>
<body>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-truck text-warning"></i> Supplier Management
        </h4>
    </div>
    <button class="btn btn-add-supplier d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="fa-solid fa-plus"></i> Add Supplier
    </button>
</div>

<div class="container-fluid px-4 mb-5">
    <div class="main-card">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="search-box d-flex align-items-center gap-2 w-100">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                <input type="text" id="searchInput" placeholder="Search supplier name, phone, or address..." onkeyup="filterSuppliers()">
            </div>
            <div class="text-muted fw-semibold small">
                Total Suppliers: <span class="badge bg-dark rounded-pill" id="totalCount"><?php echo count($suppliers); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 32%;">Supplier Name</th>
                        <th style="width: 25%;">Phone Number</th>
                        <th style="width: 20%;">Address</th>
                        <th class="text-center" style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody id="supplierTableBody">
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $s): 
                            $sId = $s['supplier_id'] ?? $s['id'];
                            $sName = $s['supplier_name'] ?? $s['name'] ?? '';
                            $sPhone = $s['phone'] ?? $s['phone_number'] ?? 'N/A';
                            $sAddress = $s['address'] ?? 'N/A';

                            // បង្កើត Initials
                            $words = array_filter(explode(" ", trim($sName)));
                            $initials = '';
                            if (count($words) >= 2) {
                                $firstWord = reset($words);
                                $lastWord = end($words);
                                $initials = mb_strtoupper(mb_substr($firstWord, 0, 1, 'UTF-8') . mb_substr($lastWord, 0, 1, 'UTF-8'), 'UTF-8');
                            } else {
                                $initials = mb_strtoupper(mb_substr($sName, 0, 2, 'UTF-8'), 'UTF-8');
                            }
                        ?>
                            <tr class="supplier-row">
                                <td class="fw-bold text-muted">#<?php echo $sId; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="supplier-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                        <span class="fw-bold text-dark fs-6 supplier-name"><?php echo htmlspecialchars($sName); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-muted supplier-phone">
                                        <i class="fa-solid fa-phone me-2 text-warning"></i><?php echo htmlspecialchars($sPhone); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark p-2 supplier-address">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo htmlspecialchars($sAddress); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action btn-edit" title="Edit Supplier"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSupplierModal"
                                                data-id="<?php echo $sId; ?>"
                                                data-name="<?php echo htmlspecialchars($sName, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-phone="<?php echo htmlspecialchars($sPhone, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-address="<?php echo htmlspecialchars($sAddress, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="supplier-delete.php?id=<?php echo $sId; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this supplier?');" title="Delete Supplier">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No suppliers found in database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-truck text-warning me-2"></i>Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="supplier-save.php" method="POST">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Name *</label>
                        <input type="text" name="supplier_name" class="form-control rounded-3" required placeholder="e.g. Arabica Coffee Beans Co.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="e.g. 012 345 678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control rounded-3" placeholder="e.g. PHNOM PENH">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-supplier">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="supplier-update.php" method="POST">
                <input type="hidden" name="supplier_id" id="edit_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Name *</label>
                        <input type="text" name="supplier_name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" id="edit_address" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-supplier">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterSuppliers() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.supplier-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.supplier-name')?.textContent.toLowerCase() || '';
            const phone = row.querySelector('.supplier-phone')?.textContent.toLowerCase() || '';
            const address = row.querySelector('.supplier-address')?.textContent.toLowerCase() || '';

            if (name.includes(input) || phone.includes(input) || address.includes(input)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('totalCount').textContent = visibleCount;
    }

    const editModal = document.getElementById('editSupplierModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_name').value = button.getAttribute('data-name');
            document.getElementById('edit_phone').value = button.getAttribute('data-phone');
            document.getElementById('edit_address').value = button.getAttribute('data-address');
        });
    }
</script>
</body>
</html>