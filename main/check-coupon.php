<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../connect.php';

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $cart_total = floatval($_POST['cart_total'] ?? 0);

    if (empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'សូមបញ្ចូលកូដ Coupon!']);
        exit();
    }

    // ទាញយកព័ត៌មាន Coupon ដែលនៅ Active និងមិនទាន់ ផុតកំណត់
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE()
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        echo json_encode(['status' => 'error', 'message' => 'កូដប្រូម៉ូសិនមិនត្រឹមត្រូវ ឬផុតកំណត់!']);
        exit();
    }

    if ($coupon['used_count'] >= $coupon['usage_limit']) {
        echo json_encode(['status' => 'error', 'message' => 'កូដប្រូម៉ូសិននេះត្រូវ បានប្រើអស់ចំនួនកំណត់ហើយ!']);
        exit();
    }

    if ($cart_total < $coupon['min_spend']) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'ទាមទារការទិញអប្បបរមាចាប់ពី $' . number_format($coupon['min_spend'], 2) . ' ឡើងទៅ!'
        ]);
        exit();
    }

    // គណនា Discount Amount
    $discount_amount = 0;
    if ($coupon['discount_type'] === 'percent') {
        $discount_amount = ($cart_total * $coupon['discount_value']) / 100;
    } else {
        $discount_amount = $coupon['discount_value'];
    }

    // ការពារកុំឱ្យ Discount លើស Cart Total
    if ($discount_amount > $cart_total) {
        $discount_amount = $cart_total;
    }

    echo json_encode([
        'status'          => 'success',
        'code'            => $coupon['code'],
        'discount_type'   => $coupon['discount_type'],
        'discount_value'  => floatval($coupon['discount_value']),
        'discount_amount' => $discount_amount,
        'message'         => 'អនុវត្ត Coupon ដោយជោគជ័យ!'
    ]);
    exit();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    exit();
}