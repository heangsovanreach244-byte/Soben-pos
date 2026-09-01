<?php
session_start();
require_once '../connect.php';

if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
}

$invoice = $_GET['invoice'] ?? ($_SESSION['receipt_invoice'] ?? '');

if (empty($invoice)) {
    die("Invoice Not Found");
}

$existingColumnsStmt = $db->query("SHOW COLUMNS FROM sales");
$existingColumns = $existingColumnsStmt->fetchAll(PDO::FETCH_COLUMN);

$hasCouponCol     = in_array('coupon_code', $existingColumns);
$hasDiscountCol   = in_array('discount_amount', $existingColumns);
$hasItemDiscCol   = in_array('item_discount', $existingColumns);
$hasCashRecCol    = in_array('cash_received', $existingColumns);
$hasChangeCol     = in_array('change_amount', $existingColumns);
$hasPayMethodCol  = in_array('payment_method', $existingColumns);
$hasBankProvCol   = in_array('bank_provider', $existingColumns);
$hasSplitCashCol  = in_array('split_cash', $existingColumns);
$hasSplitQrCol    = in_array('split_qr', $existingColumns);

$couponSelect     = $hasCouponCol ? "s.coupon_code" : "'' AS coupon_code";
$discountSelect   = $hasDiscountCol ? "s.discount_amount" : "0.00 AS discount_amount";
$itemDiscSelect   = $hasItemDiscCol ? "s.item_discount" : "0.00 AS item_discount";
$cashRecSelect    = $hasCashRecCol ? "s.cash_received" : "NULL AS cash_received";
$changeSelect     = $hasChangeCol ? "s.change_amount" : "NULL AS change_amount";
$payMethodSelect  = $hasPayMethodCol ? "s.payment_method" : "'cash' AS payment_method";
$bankProvSelect   = $hasBankProvCol ? "s.bank_provider" : "'' AS bank_provider";
$splitCashSelect  = $hasSplitCashCol ? "s.split_cash" : "0.00 AS split_cash";
$splitQrSelect    = $hasSplitQrCol ? "s.split_qr" : "0.00 AS split_qr";

$stmt = $db->prepare("
    SELECT 
        s.id AS sale_id,
        s.invoice_number,
        s.product_name,
        s.qty,
        s.price,
        s.amount,
        {$couponSelect},
        {$discountSelect},
        {$itemDiscSelect},
        {$cashRecSelect},
        {$changeSelect},
        {$payMethodSelect},
        {$bankProvSelect},
        {$splitCashSelect},
        {$splitQrSelect},
        s.cashier,
        s.sale_date,
        m.modifier_name,
        m.price AS modifier_price
    FROM sales s
    LEFT JOIN sale_item_modifiers m ON s.id = m.sale_detail_id
    WHERE s.invoice_number = ?
    ORDER BY s.id ASC
");
$stmt->execute([$invoice]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    die("Receipt Not Found");
}

$salesMap = [];
$totalItemDiscount = 0.00;

foreach ($rows as $row) {
    $saleId = $row['sale_id'];
    if (!isset($salesMap[$saleId])) {
        $itemDisc = floatval($row['item_discount'] ?? 0.00);
        $totalItemDiscount += $itemDisc;

        $salesMap[$saleId] = [
            'product_name'  => $row['product_name'],
            'qty'           => $row['qty'],
            'price'         => $row['price'],
            'amount'        => $row['amount'],
            'item_discount' => $itemDisc,
            'modifiers'     => []
        ];
    }
    if (!empty($row['modifier_name'])) {
        $salesMap[$saleId]['modifiers'][] = [
            'name'  => $row['modifier_name'],
            'price' => $row['modifier_price']
        ];
    }
}

$cashier       = $rows[0]['cashier'] ?? 'Cashier';
$sale_date     = $rows[0]['sale_date'] ?? date('Y-m-d H:i:s');
$coupon_code   = $rows[0]['coupon_code'] ?? '';
$payment_method= $rows[0]['payment_method'] ?? 'cash';
$bank_provider = $rows[0]['bank_provider'] ?? '';
$split_cash    = floatval($rows[0]['split_cash'] ?? 0.00);
$split_qr      = floatval($rows[0]['split_qr'] ?? 0.00);

$cart_discount  = floatval($rows[0]['discount_amount'] ?? 0.00);
$total_discount = $cart_discount + $totalItemDiscount;

$subtotal = 0;
foreach ($salesMap as $item) {
    $subtotal += (float)$item['amount'] + (float)$item['item_discount'];
}

$grand_total = max(0, $subtotal - $total_discount);

$dbCash   = $rows[0]['cash_received'] !== null ? floatval($rows[0]['cash_received']) : null;
$dbChange = $rows[0]['change_amount'] !== null ? floatval($rows[0]['change_amount']) : null;

if ($dbCash !== null) {
    $paid_cash  = $dbCash;
    $change_amt = $dbChange !== null ? $dbChange : max(0, $paid_cash - $grand_total);
} else {
    $paid_cash  = $_SESSION['receipt_cash'] ?? $grand_total;
    $change_amt = $_SESSION['receipt_change'] ?? max(0, $paid_cash - $grand_total);
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= htmlspecialchars($invoice, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #eef2f5;
            font-family: 'Kantumruy Pro', 'Segoe UI', sans-serif;
            color: #222;
        }

        .receipt {
            width: 80mm;
            max-width: 100%;
            margin: 20px auto;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #444;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .receipt-header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }

        .info-section {
            font-size: 11px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #444;
            padding-bottom: 8px;
        }

        .table-receipt {
            width: 100%;
            margin-bottom: 10px;
        }

        .table-receipt th,
        .table-receipt td {
            font-size: 11px;
            padding: 4px 0;
            vertical-align: top;
        }

        .table-receipt th {
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }

        .modifier-item {
            font-size: 10px;
            color: #555;
            padding-left: 8px;
        }

        .summary-section {
            font-size: 12px;
            border-top: 1px dashed #444;
            padding-top: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total-row {
            font-size: 14px;
            font-weight: 700;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            margin: 6px 0;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .receipt {
                border: none;
                box-shadow: none;
                width: 100%;
                padding: 0;
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>

<div class="receipt">
    <div class="receipt-header">
        <h3>SOBEN CAFE</h3>
        <p>វិក្កយបត្រ / Official Receipt</p>
    </div>
    
    <div class="info-section">
        <div class="d-flex justify-content-between">
            <span><strong>Inv:</strong> <?= htmlspecialchars($invoice, ENT_QUOTES, 'UTF-8'); ?></span>
            <span><strong>Cashier:</strong> <?= htmlspecialchars($cashier, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="text-start mt-1">
            <span><strong>Date:</strong> <?= date('d-M-Y h:i A', strtotime($sale_date)); ?></span>
        </div>
    </div>

    <table class="table-receipt">
        <thead>
            <tr>
                <th class="text-start">Item</th>
                <th class="text-center" style="width: 35px;">Qty</th>
                <th class="text-end" style="width: 60px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salesMap as $item): ?>
            <tr>
                <td class="text-start">
                    <strong><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    
                    <?php if (!empty($item['modifiers'])): ?>
                        <div>
                            <?php foreach ($item['modifiers'] as $mod): ?>
                                <div class="modifier-item">
                                    - <?= htmlspecialchars($mod['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?= ($mod['price'] > 0) ? '(+$' . number_format($mod['price'], 2) . ')' : ''; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($item['item_discount'] > 0): ?>
                        <div class="text-danger" style="font-size: 10px;">
                            (Disc: -$<?= number_format($item['item_discount'], 2); ?>)
                        </div>
                    <?php endif; ?>
                </td>
                <td class="text-center align-top"><?= (int)$item['qty']; ?></td>
                <td class="text-end align-top">$<?= number_format((float)$item['amount'] + (float)$item['item_discount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>$<?= number_format($subtotal, 2); ?></span>
        </div>

        <?php if ($total_discount > 0): ?>
        <div class="summary-row text-danger">
            <span>
                Discount / Coupon 
                <?= !empty($coupon_code) ? '('.htmlspecialchars($coupon_code, ENT_QUOTES, 'UTF-8').')' : ''; ?>:
            </span>
            <span>-$<?= number_format($total_discount, 2); ?></span>
        </div>
        <?php endif; ?>

        <div class="summary-row total-row">
            <span>Grand Total:</span>
            <span>$<?= number_format($grand_total, 2); ?></span>
        </div>

        <!-- ផ្នែកបង្ហាញទម្រង់បង់ប្រាក់ (Payment Method Info) -->
        <div class="summary-row text-muted" style="font-size: 11px;">
            <span>Payment:</span>
            <span class="text-uppercase fw-semibold">
                <?= htmlspecialchars($payment_method, ENT_QUOTES, 'UTF-8'); ?>
                <?= !empty($bank_provider) ? ' (' . htmlspecialchars($bank_provider, ENT_QUOTES, 'UTF-8') . ')' : ''; ?>
            </span>
        </div>

        <?php if ($payment_method === 'cash'): ?>
            <div class="summary-row">
                <span>Cash Paid:</span>
                <span>$<?= number_format($paid_cash, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Change:</span>
                <span>$<?= number_format($change_amt, 2); ?></span>
            </div>
        <?php elseif ($payment_method === 'split'): ?>
            <div class="summary-row" style="font-size: 11px;">
                <span>- Split Cash:</span>
                <span>$<?= number_format($split_cash, 2); ?></span>
            </div>
            <div class="summary-row" style="font-size: 11px;">
                <span>- Split QR/Bank:</span>
                <span>$<?= number_format($split_qr, 2); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-3" style="font-size: 11px; color: #444;">
        <p class="fw-bold mb-0">សូមអរគុណ សូមអញ្ជើញមកម្តងទៀត!</p>
        <p class="mb-0">Thank You For Visiting</p>
    </div>

    <div class="text-center mt-3 no-print d-flex gap-2 justify-content-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 rounded-3">
            <i class="fa-solid fa-print me-1"></i> Print
        </button>

        <a href="pos.php" class="btn btn-success btn-sm px-3 rounded-3">
            <i class="fa-solid fa-cart-shopping me-1"></i> Back to POS
        </a>
    </div>
</div>

</body>
</html>