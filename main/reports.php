<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// កំណត់កាលបរិច្ឆេទ Filter (Default: ថ្ងៃដើមខែ ដល់ ថ្ងៃនេះ)
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to'] ?? date('Y-m-d');

// 1. Export ទៅជា CSV/Excel ប្រសិនបើមានការចុច Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sales_report_' . $date_from . '_to_' . $date_to . '.csv');
    
    $output = fopen('php://output', 'w');
    // បញ្ចូល UTF-8 BOM សម្រាប់គាំទ្រភាសាខ្មែរក្នុង Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header រាយការណ៍
    fputcsv($output, ['Invoice #', 'Product Name', 'Qty', 'Unit Price ($)', 'Total Amount ($)', 'Cashier', 'Sale Date']);
    
    $exportStmt = $db->prepare("
        SELECT id, product_name, qty, price, amount, cashier, sale_date 
        FROM sales 
        WHERE DATE(sale_date) BETWEEN ? AND ? 
        ORDER BY sale_date DESC
    ");
    $exportStmt->execute([$date_from, $date_to]);
    
    while ($row = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            '#INV-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            $row['product_name'],
            $row['qty'],
            number_format($row['price'], 2),
            number_format($row['amount'], 2),
            $row['cashier'],
            $row['sale_date']
        ]);
    }
    fclose($output);
    exit();
}

// 2. Fetch Sales Data (រួមទាំង id / invoice_number)
$stmt = $db->prepare("
    SELECT 
        id,
        product_name,
        qty,
        price,
        amount,
        cashier,
        sale_date
    FROM sales
    WHERE DATE(sale_date) BETWEEN ? AND ?
    ORDER BY sale_date DESC
");

$stmt->execute([$date_from, $date_to]);
$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Summary Aggregates (រួមបញ្ចូល SUM ក្នុង Query តែមួយដើម្បីល្បឿន Fast Performance)
$summaryStmt = $db->prepare("
    SELECT 
        IFNULL(SUM(amount), 0) AS grand_total,
        IFNULL(SUM(qty), 0) AS total_qty,
        COUNT(*) AS total_orders
    FROM sales
    WHERE DATE(sale_date) BETWEEN ? AND ?
");

$summaryStmt->execute([$date_from, $date_to]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

$grandTotal  = $summary['grand_total'];
$grandQty    = $summary['total_qty'];
$totalOrders = $summary['total_orders'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Soben Cafe</title>

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

        /* Summary Analytics Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 8px 25px rgba(92, 61, 46, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(92, 61, 46, 0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        /* Main Content Container */
        .main-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 12px 35px rgba(92, 61, 46, 0.08);
            padding: 28px;
        }

        /* Filter Form Styling */
        .filter-card {
            background: #FAF7F2;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(200, 138, 88, 0.15);
            margin-bottom: 28px;
        }

        .form-control {
            border: 2px solid rgba(200, 138, 88, 0.2);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--cafe-accent);
            box-shadow: 0 0 0 0.25rem rgba(200, 138, 88, 0.2);
        }

        .btn-search {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 11px 20px;
            font-weight: 700;
            border: none;
            box-shadow: 0 6px 15px rgba(92, 61, 46, 0.2);
            transition: all 0.3s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(92, 61, 46, 0.3);
            color: #fff;
        }

        /* Custom Table Styling */
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
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(200, 138, 88, 0.1);
        }

        .custom-table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(92, 61, 46, 0.1);
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

        .invoice-badge {
            background-color: var(--cafe-cream);
            color: var(--cafe-primary);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .cashier-badge {
            background-color: #E0F2FE;
            color: #0369A1;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .grand-total-box {
            background: linear-gradient(135deg, var(--cafe-dark) 0%, #351F14 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 20px 28px;
            border-left: 5px solid var(--cafe-accent);
        }

        @media print {
            .page-header, .filter-card, .btn-action-group {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .main-card {
                box-shadow: none;
                border: none;
                padding: 0;
            }
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
            <i class="fa-solid fa-chart-line text-warning animate__animated animate__bounceIn"></i> Sales Report
        </h4>
    </div>
    
    <div class="btn-action-group d-flex gap-2">
        <!-- Excel Export Button -->
        <a href="?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&export=csv" class="btn btn-success d-flex align-items-center gap-2 rounded-3 px-3 py-2 fw-semibold">
            <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
        <!-- Print Button -->
        <button onclick="window.print()" class="btn btn-outline-light d-flex align-items-center gap-2 rounded-3 px-3 py-2 fw-semibold">
            <i class="fa-solid fa-print"></i> Print Report
        </button>
    </div>
</div>

<div class="container-fluid px-4 mb-5">

    <!-- Summary Overview Cards -->
    <div class="row g-3 mb-4 animate__animated animate__fadeInDown">
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Revenue</span>
                    <h3 class="fw-bold mb-0 text-dark">$<?php echo number_format($grandTotal, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Items Sold</span>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($grandQty); ?> <span class="fs-6 text-muted font-normal">items</span></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Transactions</span>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalOrders); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="main-card animate__animated animate__fadeIn">

        <!-- Date Filter Form -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark small"><i class="fa-regular fa-calendar-minus me-1 text-warning"></i> From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark small"><i class="fa-regular fa-calendar-plus me-1 text-warning"></i> To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-search w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <a href="sales_report.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" title="Reset Filter">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Data -->
        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th scope="col" style="width: 12%;">Invoice #</th>
                        <th scope="col" style="width: 28%;">Product Name</th>
                        <th scope="col" class="text-center" style="width: 10%;">Qty</th>
                        <th scope="col" class="text-end" style="width: 12%;">Unit Price</th>
                        <th scope="col" class="text-end" style="width: 13%;">Total Amount</th>
                        <th scope="col" class="text-center" style="width: 12%;">Cashier</th>
                        <th scope="col" class="text-center" style="width: 13%;">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salesData)): ?>
                        <?php foreach ($salesData as $index => $row): ?>
                            <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo min($index * 0.02, 0.4); ?>s;">
                                <td>
                                    <!-- បង្ហាញ Invoice ID ពី Database ឬបង្កើតលេខរៀងបើគ្មាន id -->
                                    <span class="invoice-badge">
                                        #INV-<?php echo str_pad($row['id'] ?? ($index + 1), 5, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-mug-saucer text-warning"></i>
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?php echo htmlspecialchars($row['qty']); ?></span>
                                </td>
                                <td class="text-end fw-semibold text-muted">$<?php echo number_format($row['price'], 2); ?></td>
                                <td class="text-end fw-bold text-success fs-6">$<?php echo number_format($row['amount'], 2); ?></td>
                                <td class="text-center">
                                    <span class="cashier-badge">
                                        <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($row['cashier']); ?>
                                    </span>
                                </td>
                                <td class="text-center small text-muted font-monospace">
                                    <?php echo date('Y-m-d H:i', strtotime($row['sale_date'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fs-1 mb-3 text-secondary d-block"></i>
                                No sales records found for the selected date range.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Grand Total Footer Box -->
        <div class="d-flex justify-content-end mt-4">
            <div class="grand-total-box d-flex align-items-center gap-4 animate__animated animate__fadeInUp">
                <div>
                    <span class="text-uppercase small text-warning font-monospace fw-bold">Grand Total Sales</span>
                    <h2 class="m-0 fw-extrabold text-white">$<?php echo number_format($grandTotal, 2); ?></h2>
                </div>
                <div class="bg-white bg-opacity-10 p-3 rounded-circle text-warning fs-3">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>