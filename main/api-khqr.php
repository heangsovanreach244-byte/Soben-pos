<?php
require_once '../connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$amount = floatval($_REQUEST['amount'] ?? 0);
$bank = trim($_REQUEST['bank'] ?? 'aba');

if ($amount <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ទឹកប្រាក់មិនត្រឹមត្រូវ (Invalid amount)'
    ]);
    exit;
}

$qr_string = "00020101021229370016a0000006770101110113KHQR00000000000304" . uniqid();
$transaction_id = "TXN-" . time() . "-" . rand(1000, 9999);

echo json_encode([
    'status' => 'success',
    'bank' => strtoupper($bank),
    'amount' => number_format($amount, 2, '.', ''),
    'currency' => 'USD',
    'transaction_id' => $transaction_id,
    'qr_string' => $qr_string,
    'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qr_string)
]);
exit;