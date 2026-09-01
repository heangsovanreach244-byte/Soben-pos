<?php
session_start();
require_once '../connect.php'; //

// ទទួលទិន្នន័យពី POS Form/Modal
$product_id  = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$qty         = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
$size        = $_POST['size'] ?? 'Regular';
$sugar_level = $_POST['sugar_level'] ?? '100% Sweetness';
$ice_level   = $_POST['ice_level'] ?? 'Normal Ice';
$toppings    = $_POST['toppings'] ?? []; // Array នៃ toppings

if ($product_id <= 0) {
    header("Location: pos.php");
    exit();
}

// ទាញយកទិន្នន័យ Product
$stmt = $db->prepare("
    SELECT *
    FROM products
    WHERE product_id = ?
    LIMIT 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: pos.php");
    exit();
}

// គណនាតម្លៃបន្ថែម (Add-ons / Modifiers Price)
$base_price = floatval($product['price']);
$addon_price = 0.00;
$selected_modifiers = [];

// 1. Size Option
$size_price = ($size === 'Large') ? 0.50 : 0.00;
$addon_price += $size_price;
$selected_modifiers[] = [
    'name'  => "Size: $size",
    'price' => $size_price
];

// 2. Sugar & Ice Levels
$selected_modifiers[] = ['name' => $sugar_level, 'price' => 0.00];
$selected_modifiers[] = ['name' => $ice_level, 'price' => 0.00];

// 3. Extra Toppings
$topping_prices = [
    'Extra Shot'   => 0.50,
    'Pearl'        => 0.30,
    'Cream Cheese' => 0.50
];

foreach ($toppings as $top) {
    $p = isset($topping_prices[$top]) ? $topping_prices[$top] : 0.00;
    $addon_price += $p;
    $selected_modifiers[] = [
        'name'  => $top,
        'price' => $p
    ];
}

$unit_price = $base_price + $addon_price;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// បង្កើត Unique Key សម្រាប់ Cart Item
// (ដើម្បីកុំឱ្យច្រឡំគ្នានៅពេល Product ដូចគ្នា តែរើស Size ឬ Toppings ខុសគ្នា)
$cart_item_id = md5($product_id . '_' . $size . '_' . $sugar_level . '_' . $ice_level . '_' . implode(',', $toppings));

if (isset($_SESSION['cart'][$cart_item_id])) {
    // ប្រសិនបើកាហ្វេ និង Modifiers ដូចគ្នាទាំងស្រុង គ្រាន់តែថែមចំនួន Quantity
    $_SESSION['cart'][$cart_item_id]['qty'] += $qty;
    $_SESSION['cart'][$cart_item_id]['subtotal'] = $_SESSION['cart'][$cart_item_id]['unit_price'] * $_SESSION['cart'][$cart_item_id]['qty'];
} else {
    // ប្រសិនបើជម្រើសខុសគ្នា រក្សាទុកជា Item ថ្មី
    $_SESSION['cart'][$cart_item_id] = [
        'cart_item_id' => $cart_item_id,
        'product_id'   => $product['product_id'],
        'product_name' => $product['product_name'],
        'base_price'   => $base_price,
        'unit_price'   => $unit_price,
        'qty'          => $qty,
        'modifiers'    => $selected_modifiers,
        'subtotal'     => $unit_price * $qty
    ];
}

header("Location: pos.php");
exit();
?>