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

// ទាញយកទិន្នន័យ Customer ពី Database
$customers = [];
try {
    $stmt = $db->query("SELECT * FROM customers ORDER BY customer_id DESC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $stmt = $db->query("SELECT * FROM customers ORDER BY id DESC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Customer Management - Soben Cafe</title>

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

        /* Top Header Bar */
        .page-header {
            background: rgba(30, 18, 12, 0.95);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 16px 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-bottom: 2px solid var(--cafe-accent);
        }

        /* Container Card */
        .main-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 12px 35px rgba(92, 61, 46, 0.08);
            padding: 28px;
            transition: all 0.3s ease;
        }

        /* Search Input Styling */
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

        /* Custom Table Design */
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

        /* Table Row Cards */
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

        /* Customer Avatar Badge */
        .customer-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(92, 61, 46, 0.2);
            transition: all 0.3s ease;
        }

        .custom-table tbody tr:hover .customer-avatar {
            transform: scale(1.1) rotate(5deg);
        }

        /* Address Badge */
        .address-badge {
            background-color: var(--cafe-cream);
            color: var(--cafe-primary);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Add Button */
        .btn-add-customer {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 700;
            border: none;
            box-shadow: 0 8px 20px rgba(92, 61, 46, 0.25);
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-add-customer:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(92, 61, 46, 0.35);
            color: #fff;
            filter: brightness(1.05);
        }

        /* Action Buttons */
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
            cursor: pointer;
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
            <i class="fa-solid fa-users text-warning animate__animated animate__bounceIn"></i> Customer Management
        </h4>
    </div>
    <button class="btn btn-add-customer d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fa-solid fa-user-plus"></i> Add Customer
    </button>
</div>

<div class="container-fluid px-4 mb-5">
    
    <!-- Main Card -->
    <div class="main-card animate__animated animate__fadeIn">
        
        <!-- Flash Messages -->
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

        <!-- Search & Info Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="search-box d-flex align-items-center gap-2 w-100">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                <input type="text" id="searchInput" placeholder="Search name, phone, or address..." onkeyup="filterCustomers()">
            </div>
            <div class="text-muted fw-semibold small">
                Total Customers: <span class="badge bg-dark rounded-pill" id="totalCount"><?php echo count($customers); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th scope="col" style="width: 8%;">ID</th>
                        <th scope="col" style="width: 32%;">Customer Name</th>
                        <th scope="col" style="width: 25%;">Phone Number</th>
                        <th scope="col" style="width: 20%;">Address</th>
                        <th scope="col" class="text-center" style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    <?php if (!empty($customers)): ?>
                        <?php 
                        $delay = 0.05;
                        foreach ($customers as $c): 
                            $cId = $c['customer_id'] ?? $c['id'];
                            $cName = $c['name'] ?? $c['customer_name'] ?? '';
                            $cPhone = $c['phone'] ?? $c['phone_number'] ?? 'N/A';
                            $cAddress = $c['address'] ?? 'N/A';

                            // បង្កើត អក្សរកាត់ឈ្មោះ (Initials) សម្រាប់ Avatar
                            $words = explode(" ", trim($cName));
                            $initials = '';
                            if (count($words) >= 2) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
                            } else {
                                $initials = strtoupper(substr($cName, 0, 2));
                            }
                        ?>
                            <tr class="customer-row animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                                <td class="fw-bold text-muted">#<?php echo $cId; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="customer-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                        <span class="fw-bold text-dark fs-6 customer-name"><?php echo htmlspecialchars($cName); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-muted customer-phone">
                                        <i class="fa-solid fa-phone me-2 text-warning fs-6"></i><?php echo htmlspecialchars($cPhone); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="address-badge customer-address">
                                        <i class="fa-solid fa-location-dot text-danger"></i> <?php echo htmlspecialchars($cAddress); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Edit Modal Trigger Button -->
                                        <button type="button" class="btn-action btn-edit" title="Edit Customer"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editCustomerModal"
                                                data-id="<?php echo $cId; ?>"
                                                data-name="<?php echo htmlspecialchars($cName); ?>"
                                                data-phone="<?php echo htmlspecialchars($cPhone); ?>"
                                                data-address="<?php echo htmlspecialchars($cAddress); ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <!-- Delete Link -->
                                        <a href="customer-delete.php?id=<?php echo $cId; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this customer?');" title="Delete Customer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            $delay += 0.05;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No customers found in database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-warning me-2"></i>Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="customer-save.php" method="POST">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name *</label>
                        <input type="text" name="name" class="form-control rounded-3" required placeholder="e.g. THANN SOKHENG">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="e.g. 077 465 236">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control rounded-3" placeholder="e.g. PREY VENG">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-customer">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen text-warning me-2"></i>Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="customer-update.php" method="POST">
                <input type="hidden" name="customer_id" id="edit_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
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
                    <button type="submit" class="btn btn-add-customer">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dynamic Filter/Search Function
    function filterCustomers() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.customer-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.customer-name')?.textContent.toLowerCase() || '';
            const phone = row.querySelector('.customer-phone')?.textContent.toLowerCase() || '';
            const address = row.querySelector('.customer-address')?.textContent.toLowerCase() || '';

            if (name.includes(input) || phone.includes(input) || address.includes(input)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('totalCount').textContent = visibleCount;
    }

    // Modal Auto-Fill Data when Clicking Edit
    const editModal = document.getElementById('editCustomerModal');
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