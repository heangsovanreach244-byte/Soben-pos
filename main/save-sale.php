<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit();
}

try {
    require_once '../connect.php';

    $payment_method  = trim($_POST['payment_method'] ?? 'cash');
    $cash            = isset($_POST['cash']) ? floatval($_POST['cash']) : 0.00;
    $bank_provider   = trim($_POST['bank'] ?? '');
    $split_cash      = isset($_POST['split_cash']) ? floatval($_POST['split_cash']) : 0.00;
    $split_qr        = isset($_POST['split_qr']) ? floatval($_POST['split_qr']) : 0.00;
    $coupon_code     = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : null;
    $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0.00;
    $order_type      = isset($_POST['order_type']) ? trim($_POST['order_type']) : 'Dine-in';
    $table_id        = !empty($_POST['table_id']) ? intval($_POST['table_id']) : null;
    $cartJson        = $_POST['cart_data'] ?? '[]';
    $cart            = json_decode($cartJson, true);

    if (empty($cart) || !is_array($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Your cart is empty!']);
        exit();
    }

    $subtotal = 0.00;
    foreach ($cart as $item) {
        $unitPrice = floatval($item['unit_price'] ?? $item['price'] ?? 0);
        $qty       = intval($item['qty'] ?? 0);
        
        $modTotal = 0.00;
        if (!empty($item['modifiers']) && is_array($item['modifiers'])) {
            foreach ($item['modifiers'] as $mod) {
                $modTotal += floatval($mod['price'] ?? 0);
            }
        }
        
        $subtotal += (($unitPrice + $modTotal) * $qty);
    }

    $calculatedTotal = max(0, $subtotal - $discount_amount);

    if ($payment_method === 'cash' && round($cash, 2) < round($calculatedTotal, 2)) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient cash amount!']);
        exit();
    } elseif ($payment_method === 'split' && round(($split_cash + $split_qr), 2) < round($calculatedTotal, 2)) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient split payment amount!']);
        exit();
    }

    $invoice       = "INV" . date("YmdHis") . rand(10, 99);
    $change_amount = ($payment_method === 'cash') ? max(0, $cash - $calculatedTotal) : 0.00;
    $cashier_name  = $_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'Cashier');

    $existingColumnsStmt = $db->query("SHOW COLUMNS FROM sales");
    $existingColumns = $existingColumnsStmt->fetchAll(PDO::FETCH_COLUMN);

    $db->beginTransaction();

    $stmtCheckStock = $db->prepare("SELECT qty, product_name FROM products WHERE id = ? FOR UPDATE");
    $stmtStock      = $db->prepare("UPDATE products SET qty = qty - ? WHERE id = ?");

    // រៀបចំសម្រាប់ឆែកស្តុក និងកាត់ដកគ្រឿងផ្សំដើម (Recipe / BOM)
    $stmtRecipe = $db->prepare("SELECT r.ingredient_id, r.quantity, i.ingredient_name, i.stock_qty, i.unit FROM product_recipes r JOIN ingredients i ON r.ingredient_id = i.id WHERE r.product_id = ?");
    $stmtCheckIng = $db->prepare("SELECT stock_qty, ingredient_name, unit FROM ingredients WHERE id = ? FOR UPDATE");
    $stmtUpdateIng = $db->prepare("UPDATE ingredients SET stock_qty = stock_qty - ? WHERE id = ?");

    // ប្រមូលបញ្ជីគ្រឿងផ្សំសរុបដែលត្រូវកាត់ដកក្នុងវិក្កយបត្រនេះ ដើម្បីការពារករណីទំនិញជាន់គ្នា
    $totalRequiredIngredients = [];

    foreach ($cart as $item) {
        $product_id = intval($item['product_id'] ?? 0);
        $qty        = intval($item['qty'] ?? 1);

        if ($product_id > 0) {
            $stmtRecipe->execute([$product_id]);
            $recipes = $stmtRecipe->fetchAll(PDO::FETCH_ASSOC);

            foreach ($recipes as $rec) {
                $ingId = $rec['ingredient_id'];
                $needed = $rec['quantity'] * $qty;
                if (!isset($totalRequiredIngredients[$ingId])) {
                    $totalRequiredIngredients[$ingId] = [
                        'name' => $rec['ingredient_name'],
                        'unit' => $rec['unit'],
                        'needed' => 0
                    ];
                }
                $totalRequiredIngredients[$ingId]['needed'] += $needed;
            }
        }
    }

    // ពិនិត្យស្តុកគ្រឿងផ្សំសរុបថាតើគ្រប់គ្រាន់ដែរឬទេ
    foreach ($totalRequiredIngredients as $ingId => $data) {
        $stmtCheckIng->execute([$ingId]);
        $ingData = $stmtCheckIng->fetch(PDO::FETCH_ASSOC);
        if ($ingData && $ingData['stock_qty'] < $data['needed']) {
            throw new Exception("គ្រឿងផ្សំ '{$data['name']}' មិនគ្រប់គ្រាន់ទេក្នុងស្តុក (ត្រូវការ: {$data['needed']} {$data['unit']}, មានសល់: {$ingData['stock_qty']} {$data['unit']})");
        }
    }

    // ប្រតិបត្តិការកាត់ដកស្តុកគ្រឿងផ្សំពិតប្រាកដ
    foreach ($totalRequiredIngredients as $ingId => $data) {
        $stmtUpdateIng->execute([$data['needed'], $ingId]);
    }

    $isFirstItem = true;
    foreach ($cart as $item) {
        $product_id    = intval($item['product_id'] ?? 0);
        $product_name  = trim($item['product_name'] ?? '');
        $qty           = intval($item['qty'] ?? 1);
        $unit_price    = floatval($item['unit_price'] ?? $item['price'] ?? 0);
        $item_discount = floatval($item['item_discount'] ?? 0);
        
        $modPriceTotal = 0.00;
        if (!empty($item['modifiers']) && is_array($item['modifiers'])) {
            foreach ($item['modifiers'] as $mod) {
                $modPriceTotal += floatval($mod['price'] ?? 0);
            }
        }

        $item_total = (($unit_price + $modPriceTotal) * $qty) - $item_discount;

        if ($product_id > 0) {
            $stmtCheckStock->execute([$product_id]);
            $prodData = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

            if ($prodData && isset($prodData['qty']) && $prodData['qty'] < $qty) {
                throw new Exception("Insufficient stock for product: " . ($prodData['product_name'] ?? $product_name));
            }

            $stmtStock->execute([$qty, $product_id]);
        }

        $currentCartDiscount = $isFirstItem ? $discount_amount : 0.00;

        $insertData = [
            'invoice_number'  => $invoice,
            'invoice_no'      => $invoice,
            'order_type'      => $order_type,
            'table_id'        => $table_id,
            'product_id'      => $product_id,
            'product_name'    => $product_name,
            'qty'             => $qty,
            'price'           => $unit_price,
            'item_discount'   => $item_discount,
            'amount'          => $item_total,
            'total_amount'    => $item_total,
            'payment_method'  => $payment_method,
            'bank_provider'   => $bank_provider,
            'cash_received'   => $cash,
            'split_cash'      => $split_cash,
            'split_qr'        => $split_qr,
            'coupon_code'     => $coupon_code,
            'discount_amount' => $currentCartDiscount,
            'cashier'         => $cashier_name,
            'change_amount'   => $change_amount,
            'sale_date'       => date("Y-m-d H:i:s")
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

        $sale_detail_id = null;
        if (!empty($fields)) {
            $sqlSale = "INSERT INTO sales (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmtSale = $db->prepare($sqlSale);
            $stmtSale->execute($values);
            $sale_detail_id = $db->lastInsertId();
        }

        if (!empty($sale_detail_id) && !empty($item['modifiers']) && is_array($item['modifiers'])) {
            $checkModTable = $db->query("SHOW TABLES LIKE 'sale_item_modifiers'")->rowCount();
            if ($checkModTable > 0) {
                $stmtMod = $db->prepare("
                    INSERT INTO sale_item_modifiers (sale_detail_id, modifier_name, price)
                    VALUES (?, ?, ?)
                ");
                foreach ($item['modifiers'] as $mod) {
                    $stmtMod->execute([
                        $sale_detail_id,
                        $mod['name'],
                        floatval($mod['price'] ?? 0)
                    ]);
                }
            }
        }

        $isFirstItem = false;
    }

    if (!empty($coupon_code)) {
        $checkCouponTable = $db->query("SHOW TABLES LIKE 'coupons'")->rowCount();
        if ($checkCouponTable > 0) {
            $stmtUpdateCoupon = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
            $stmtUpdateCoupon->execute([$coupon_code]);
        }
    }

    $db->commit();

    echo json_encode([
        'status'          => 'success',
        'message'         => 'Sale saved successfully',
        'invoice'         => $invoice,
        'subtotal'        => $subtotal,
        'discount_amount' => $discount_amount,
        'coupon_code'     => $coupon_code,
        'grand_total'     => $calculatedTotal,
        'payment_method'  => $payment_method,
        'cash_received'   => $cash,
        'change'          => $change_amount
    ]);
    exit();

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
}