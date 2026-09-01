<?php
session_start();

require_once '../connect.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: pos.php");
    exit();
}

try {
    $db->beginTransaction();

    $invoice_no = 'INV' . date('YmdHis');
    $grand_total = 0;

    foreach ($_SESSION['cart'] as $item) {
        $grand_total += (float)$item['price'] * (int)$item['qty'];
    }

    $cashier = $_SESSION['full_name'] ?? 'Cashier';
    
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $cash_received  = isset($_POST['cash']) ? (float)$_POST['cash'] : 0.00;
    $bank_provider  = $_POST['bank'] ?? '';
    $split_cash     = isset($_POST['split_cash']) ? (float)$_POST['split_cash'] : 0.00;
    $split_qr       = isset($_POST['split_qr']) ? (float)$_POST['split_qr'] : 0.00;

    // បង្គត់តម្លៃដើម្បីការពារបញ្ហា floating point precision
    $grand_total   = round($grand_total, 2);
    $cash_received = round($cash_received, 2);

    // ពិនិត្យទឹកប្រាក់បង់ចូល
    if ($payment_method === 'cash' && $cash_received < $grand_total) {
        throw new Exception("Insufficient cash amount!");
    } elseif ($payment_method === 'split' && round($split_cash + $split_qr, 2) < $grand_total) {
        throw new Exception("Insufficient split payment amount!");
    }

    $change_amount = ($payment_method === 'cash') ? max(0, $cash_received - $grand_total) : 0.00;

    $existingColumnsStmt = $db->query("SHOW COLUMNS FROM sales");
    $existingColumns = $existingColumnsStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($_SESSION['cart'] as $item) {
        $price = (float)$item['price'];
        $qty = (int)$item['qty'];
        $amount = $price * $qty;
        $product_id = (int)$item['product_id'];
        $product_name = $item['product_name'] ?? 'Unknown Item';

        $insertData = [
            'invoice_number' => $invoice_no,
            'invoice_no'     => $invoice_no,
            'product_id'     => $product_id,
            'product_name'   => $product_name,
            'qty'            => $qty,
            'price'          => $price,
            'amount'         => $amount,
            'total_amount'   => $amount,
            'cashier'        => $cashier,
            'payment_method' => $payment_method,
            'bank_provider'  => $bank_provider,
            'cash_received'  => ($payment_method === 'cash') ? $cash_received : 0.00,
            'split_cash'     => $split_cash,
            'split_qr'       => $split_qr,
            'change_amount'  => $change_amount,
            'sale_date'      => date('Y-m-d H:i:s')
        ];

        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($insertData as $col => $val) {
            if (in_array($col, $existingColumns)) {
                $fields[] = "`$col`";
                $placeholders[] = "?";
                $values[] = $val;
            }
        }

        if (!empty($fields)) {
            $sql = "INSERT INTO sales (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
        }

        $update = $db->prepare("
            UPDATE products
            SET qty = qty - ?
            WHERE id = ?
        ");

        $update->execute([
            $qty,
            $product_id
        ]);
    }

    $db->commit();

    $_SESSION['last_invoice'] = $invoice_no;
    unset($_SESSION['cart']);

    header("Location: receipt.php?invoice=" . $invoice_no);
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>