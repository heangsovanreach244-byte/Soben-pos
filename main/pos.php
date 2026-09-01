<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categoriesStmt = $db->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$productsStmt = $db->query("
    SELECT 
        p.id,
        p.product_code,
        p.product_name AS item_name,
        p.price AS item_price,
        p.qty AS stock,
        c.category_name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.product_name ASC
");
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

$modQuery = "
    SELECT 
        mg.id AS group_id, 
        mg.name AS group_name, 
        m.id AS item_id, 
        m.name AS item_name, 
        m.price AS item_price
    FROM modifier_groups mg
    LEFT JOIN modifiers m ON mg.id = m.group_id
    ORDER BY mg.id ASC, m.id ASC
";
$modStmt = $db->query($modQuery);
$rawModifiers = $modStmt->fetchAll(PDO::FETCH_ASSOC);

$modifiersGrouped = [];
foreach ($rawModifiers as $row) {
    $groupId = $row['group_id'];
    if (!isset($modifiersGrouped[$groupId])) {
        $modifiersGrouped[$groupId] = [
            'group_name' => $row['group_name'],
            'items' => []
        ];
    }
    if (!empty($row['item_id'])) {
        $modifiersGrouped[$groupId]['items'][] = [
            'id'    => $row['item_id'],
            'name'  => $row['item_name'],
            'price' => (float)$row['item_price']
        ];
    }
}
$modifiersData = array_values($modifiersGrouped);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soben Cafe - POS System</title>
    
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
            background: var(--cafe-bg);
            color: var(--text-main);
            min-height: 100vh;
        }

        .pos-header {
            background: rgba(30, 18, 12, 0.96);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 12px 24px;
            border-bottom: 2px solid var(--cafe-accent);
        }

        .category-pill {
            background: #fff;
            border: 1px solid rgba(200, 138, 88, 0.3);
            color: var(--cafe-dark);
            padding: 8px 18px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .category-pill.active, .category-pill:hover {
            background: var(--cafe-primary);
            color: #fff;
            border-color: var(--cafe-primary);
        }

        .product-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(200, 138, 88, 0.15);
            padding: 16px;
            transition: all 0.25s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .product-card:hover:not(.out-of-stock) {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(92, 61, 46, 0.12);
            border-color: var(--cafe-accent);
        }

        .product-card.out-of-stock {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        .product-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--cafe-cream);
            color: var(--cafe-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .product-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--cafe-dark);
            margin-top: 10px;
        }

        .product-price {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--cafe-accent);
        }

        .cart-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(200, 138, 88, 0.2);
            box-shadow: 0 10px 30px rgba(92, 61, 46, 0.06);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 100px);
            position: sticky;
            top: 80px;
        }

        .cart-header {
            background: var(--cafe-cream);
            padding: 16px 20px;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .cart-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 16px;
        }

        .qty-btn {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            border: none;
            background: #EFE3D3;
            color: var(--cafe-dark);
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .qty-btn:hover {
            background: var(--cafe-accent);
            color: #fff;
        }

        .cart-footer {
            padding: 18px 20px;
            background: #FAF7F2;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            border-top: 1px solid rgba(200, 138, 88, 0.2);
        }

        .total-badge {
            background: var(--cafe-dark);
            color: #fff;
            padding: 14px 18px;
            border-radius: 14px;
        }

        .btn-checkout {
            background: linear-gradient(135deg, var(--cafe-accent) 0%, var(--cafe-primary) 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
        }

        .search-box {
            background: #ffffff;
            border: 2px solid rgba(200, 138, 88, 0.2);
            border-radius: 14px;
            padding: 8px 16px;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            background: transparent;
        }

        .modifier-tag {
            font-size: 0.75rem;
            color: #6c757d;
            display: block;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="pos-header d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h5 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-mug-hot text-warning"></i> Soben Cafe POS
        </h5>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
            <i class="fa-solid fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Cashier', ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </div>
</div>

<div class="container-fluid px-3">
    <div class="row g-3">
        
        <div class="col-lg-7 col-xl-8">
            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <div class="search-box d-flex align-items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        <input type="text" id="searchProduct" placeholder="Search product..." onkeyup="filterProducts()">
                    </div>
                </div>
                <div class="col-md-7 d-flex align-items-center gap-2 overflow-auto py-1">
                    <button class="category-pill active" onclick="filterCategory('all', this)">All</button>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <button class="category-pill" onclick="filterCategory('<?php echo htmlspecialchars(strtolower($cat['category_name']), ENT_QUOTES, 'UTF-8'); ?>', this)">
                                <?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-3" id="productList">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $prod): 
                        $p_id = (int)($prod['id'] ?? 0);
                        $p_name = $prod['item_name'] ?? 'Item';
                        $p_price = (float)($prod['item_price'] ?? 0.00);
                        $p_cat = strtolower($prod['category_name'] ?? 'uncategorized');
                        $p_stock = (int)($prod['stock'] ?? 0);
                        $is_out_of_stock = $p_stock <= 0;
                    ?>
                        <div class="col-6 col-md-4 col-xl-3 product-item" data-name="<?php echo htmlspecialchars(strtolower($p_name), ENT_QUOTES, 'UTF-8'); ?>" data-category="<?php echo htmlspecialchars($p_cat, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="product-card <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>" 
                                 onclick="<?php echo !$is_out_of_stock ? 'openModifierModal(' . $p_id . ', ' . htmlspecialchars(json_encode($p_name), ENT_QUOTES, 'UTF-8') . ', ' . $p_price . ')' : 'void(0);'; ?>">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="product-icon">
                                            <i class="fa-solid fa-mug-saucer"></i>
                                        </div>
                                        <span class="badge <?php echo $p_stock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> rounded-pill">
                                            Stock: <?php echo $p_stock; ?>
                                        </span>
                                    </div>
                                    <div class="product-title"><?php echo htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="product-price">$<?php echo number_format($p_price, 2); ?></div>
                                    <span class="badge bg-light text-dark border"><i class="fa-solid fa-plus"></i></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted col-12">
                        <p>No products available in the database!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <div class="cart-card">
                <div class="cart-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0 text-dark">
                        <i class="fa-solid fa-basket-shopping text-warning me-1"></i> Order List
                    </h6>
                    <button class="btn btn-sm btn-outline-danger border-0 rounded-pill px-2" onclick="clearCart()">
                        <i class="fa-solid fa-trash-can me-1"></i> Clear All
                    </button>
                </div>

                <div class="cart-body">
                    <table class="table w-100 align-middle">
                        <thead>
                            <tr class="small text-muted">
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody"></tbody>
                    </table>
                </div>

                <div class="cart-footer">
                    <div class="total-badge d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 text-white-50">Grand Total</span>
                        <span class="fs-4 fw-bold" id="grandTotalText">$0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Payment Method</label>
                        <select id="paymentMethodSelect" class="form-select rounded-3 py-2 fw-bold" onchange="handlePaymentMethodChange()">
                            <option value="cash">Cash (សាច់ប្រាក់)</option>
                            <option value="khqr">KHQR / Mobile Banking</option>
                            <option value="credit_card">Credit Card (កាតឥណទាន)</option>
                            <option value="split">Split Payment (បង់ចម្រុះ Cash + QR)</option>
                        </select>
                    </div>

                    <div id="singlePaymentSection" class="row g-2 mb-3">
                        <div class="col-6" id="cashInputContainer">
                            <label class="form-label small fw-bold text-muted mb-1" id="receivedLabel">Cash Received ($)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3 py-2 fw-bold" id="cashReceived" placeholder="0.00" oninput="calculateChange()">
                        </div>
                        <div class="col-6" id="changeInputContainer">
                            <label class="form-label small fw-bold text-muted mb-1" id="changeLabel">Change ($)</label>
                            <input type="text" class="form-control rounded-3 py-2 fw-bold bg-light text-success" id="changeAmount" value="$0.00" readonly>
                        </div>
                        <div class="col-12 mt-2" id="bankSelectContainer" style="display: none;">
                            <label class="form-label small fw-bold text-muted mb-1">Select Bank</label>
                            <select id="bankProvider" class="form-select rounded-3 py-2 fw-bold" onchange="triggerDynamicKHQR()">
                                <option value="aba">ABA Bank</option>
                                <option value="acleda">ACLEDA Bank</option>
                                <option value="wing">Wing Bank</option>
                                <option value="canadia">Canadia Bank</option>
                            </select>
                        </div>
                    </div>

                    <div id="splitPaymentSection" class="row g-2 mb-3" style="display: none;">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Cash Amount ($)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3 py-2 fw-bold" id="splitCash" placeholder="0.00" oninput="calculateSplit()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">QR Amount ($)</label>
                            <input type="text" class="form-control rounded-3 py-2 fw-bold bg-light text-primary" id="splitQrAmount" value="$0.00" readonly>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold text-muted mb-1">Select QR Bank</label>
                            <select id="splitBankProvider" class="form-select rounded-3 py-2 fw-bold" onchange="triggerDynamicKHQR()">
                                <option value="aba">ABA Bank</option>
                                <option value="acleda">ACLEDA Bank</option>
                                <option value="wing">Wing Bank</option>
                                <option value="canadia">Canadia Bank</option>
                            </select>
                        </div>
                    </div>

                    <div id="qrDisplayBox" class="text-center mb-3 p-2 bg-white rounded-3 border" style="display: none;">
                        <img id="dynamicQrImage" src="" alt="KHQR Code" style="width: 150px; height: 150px;" class="mb-1">
                        <div class="small fw-bold text-danger" id="qrAmountText">Scan to Pay: $0.00</div>
                    </div>

                    <button class="btn-checkout d-flex justify-content-center align-items-center gap-2" onclick="completeSale()">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>COMPLETE SALE</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modifierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-header-title fw-bold" id="modalProductName">Customize Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modifierForm">
                    <input type="hidden" id="modalProductId">
                    <input type="hidden" id="modalBasePrice">
                    <div id="modifiersContainer"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 py-2 rounded-3 fw-bold" onclick="addCustomizedToCart()">
                    Add to Cart - <span id="modalTotalPrice">$0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let cart = [];
    let currentSelectedProduct = null;
    let selectedCategory = 'all';
    const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
    const availableModifiers = <?php echo json_encode($modifiersData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const modifierModal = new bootstrap.Modal(document.getElementById('modifierModal'));

    function openModifierModal(id, name, price) {
        currentSelectedProduct = { id, name, basePrice: parseFloat(price) };
        document.getElementById('modalProductId').value = id;
        document.getElementById('modalBasePrice').value = price;
        document.getElementById('modalProductName').innerText = name + ' ($' + currentSelectedProduct.basePrice.toFixed(2) + ')';

        const container = document.getElementById('modifiersContainer');
        container.innerHTML = '';

        if (!availableModifiers || availableModifiers.length === 0 || availableModifiers.every(g => !g.items || g.items.length === 0)) {
            addToCart(id, name, currentSelectedProduct.basePrice, []);
            return;
        }

        availableModifiers.forEach((group, index) => {
            if (!group.items || group.items.length === 0) return;

            let html = `<div class="mb-3"><label class="fw-bold mb-2 small text-uppercase text-muted">${escapeHtml(group.group_name)}</label><div>`;
            const isTopping = group.group_name.toLowerCase().includes('topping');
            const inputType = isTopping ? 'checkbox' : 'radio';
            const inputName = `mod_group_${index}`;

            group.items.forEach((item, itemIdx) => {
                const isChecked = (inputType === 'radio' && itemIdx === 0) ? 'checked' : '';
                html += `
                    <div class="form-check form-check-inline mb-2">
                        <input class="form-check-input mod-option" type="${inputType}" name="${inputName}" id="mod_${index}_${item.id}" value="${escapeHtml(item.name)}" data-price="${item.price}" ${isChecked} onchange="updateModalTotal()">
                        <label class="form-check-label small" for="mod_${index}_${item.id}">
                            ${escapeHtml(item.name)} ${item.price > 0 ? '(+$' + parseFloat(item.price).toFixed(2) + ')' : ''}
                        </label>
                    </div>
                `;
            });

            html += `</div></div><hr class="my-2">`;
            container.innerHTML += html;
        });

        updateModalTotal();
        modifierModal.show();
    }

    function updateModalTotal() {
        if (!currentSelectedProduct) return;
        let total = currentSelectedProduct.basePrice;
        const selectedOptions = document.querySelectorAll('.mod-option:checked');
        selectedOptions.forEach(opt => {
            total += parseFloat(opt.getAttribute('data-price') || 0);
        });
        document.getElementById('modalTotalPrice').innerText = '$' + total.toFixed(2);
    }

    function addCustomizedToCart() {
        const selectedMods = [];
        let unitPrice = currentSelectedProduct.basePrice;

        const checkedOptions = document.querySelectorAll('.mod-option:checked');
        checkedOptions.forEach(opt => {
            const price = parseFloat(opt.getAttribute('data-price') || 0);
            selectedMods.push({ name: opt.value, price: price });
            unitPrice += price;
        });

        addToCart(currentSelectedProduct.id, currentSelectedProduct.name, unitPrice, selectedMods);
        modifierModal.hide();
    }

    function addToCart(id, name, unitPrice, modifiers) {
        const sortedMods = [...modifiers].sort((a, b) => a.name.localeCompare(b.name));
        const cartKey = id + '_' + JSON.stringify(sortedMods);
        const existingIndex = cart.findIndex(item => item.cartKey === cartKey);

        if (existingIndex > -1) {
            cart[existingIndex].qty += 1;
        } else {
            cart.push({
                cartKey: cartKey,
                product_id: id,
                product_name: name,
                unit_price: unitPrice,
                qty: 1,
                modifiers: sortedMods
            });
        }
        renderCart();
    }

    function updateQty(cartKey, change) {
        const item = cart.find(item => item.cartKey === cartKey);
        if (item) {
            item.qty += change;
            if (item.qty <= 0) {
                cart = cart.filter(i => i.cartKey !== cartKey);
            }
        }
        renderCart();
    }

    function removeItem(cartKey) {
        cart = cart.filter(item => item.cartKey !== cartKey);
        renderCart();
    }

    function clearCart() {
        if(cart.length === 0) return;
        cart = [];
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        tbody.innerHTML = '';
        let grandTotal = 0;

        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-mug-saucer fs-2 mb-2 d-block opacity-40"></i>
                        <span class="small">Cart is empty</span>
                    </td>
                </tr>
            `;
        } else {
            cart.forEach(item => {
                const itemTotal = item.unit_price * item.qty;
                grandTotal += itemTotal;

                let modsHtml = '';
                if (item.modifiers && item.modifiers.length > 0) {
                    modsHtml = item.modifiers.map(m => `<span class="modifier-tag">- ${escapeHtml(m.name)} ${m.price > 0 ? '(+$'+m.price.toFixed(2)+')' : ''}</span>`).join('');
                }

                tbody.innerHTML += `
                    <tr>
                        <td>
                            <strong class="text-dark d-block">${escapeHtml(item.product_name)}</strong>
                            ${modsHtml}
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="qty-btn" onclick="updateQty('${item.cartKey}', -1)">-</button>
                                <span class="fw-bold px-1">${item.qty}</span>
                                <button class="qty-btn" onclick="updateQty('${item.cartKey}', 1)">+</button>
                            </div>
                        </td>
                        <td class="text-end text-muted">$${item.unit_price.toFixed(2)}</td>
                        <td class="text-end fw-bold text-dark">$${itemTotal.toFixed(2)}</td>
                        <td class="text-end ps-2">
                            <i class="fa-solid fa-xmark text-danger cursor-pointer" onclick="removeItem('${item.cartKey}')"></i>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById('grandTotalText').innerText = `$${grandTotal.toFixed(2)}`;
        calculateChange();
        calculateSplit();
        if(document.getElementById('paymentMethodSelect').value === 'khqr' || document.getElementById('paymentMethodSelect').value === 'split') {
            triggerDynamicKHQR();
        }
    }

    function handlePaymentMethodChange() {
        const method = document.getElementById('paymentMethodSelect').value;
        const singleSec = document.getElementById('singlePaymentSection');
        const splitSec = document.getElementById('splitPaymentSection');
        const cashInputCont = document.getElementById('cashInputContainer');
        const changeInputCont = document.getElementById('changeInputContainer');
        const bankSelectCont = document.getElementById('bankSelectContainer');
        const qrBox = document.getElementById('qrDisplayBox');

        qrBox.style.display = 'none';
        bankSelectCont.style.display = 'none';
        cashInputCont.style.display = 'block';
        changeInputCont.style.display = 'block';

        if (method === 'cash') {
            singleSec.style.display = 'flex';
            splitSec.style.display = 'none';
            document.getElementById('receivedLabel').innerText = 'Cash Received ($)';
        } else if (method === 'khqr') {
            singleSec.style.display = 'flex';
            splitSec.style.display = 'none';
            cashInputCont.style.display = 'none';
            changeInputCont.style.display = 'none';
            bankSelectCont.style.display = 'block';
            qrBox.style.display = 'block';
            triggerDynamicKHQR();
        } else if (method === 'credit_card') {
            singleSec.style.display = 'flex';
            splitSec.style.display = 'none';
            cashInputCont.style.display = 'none';
            changeInputCont.style.display = 'none';
        } else if (method === 'split') {
            singleSec.style.display = 'none';
            splitSec.style.display = 'flex';
            qrBox.style.display = 'block';
            calculateSplit();
            triggerDynamicKHQR();
        }
    }

    function calculateChange() {
        const grandTotal = cart.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
        const cash = parseFloat(document.getElementById('cashReceived').value) || 0;
        const change = cash - grandTotal;
        const changeInput = document.getElementById('changeAmount');

        if (cash > 0 && change >= 0) {
            changeInput.value = `$${change.toFixed(2)}`;
            changeInput.className = 'form-control rounded-3 py-2 fw-bold bg-light text-success';
        } else if (cash > 0 && change < 0) {
            changeInput.value = `-$${Math.abs(change).toFixed(2)}`;
            changeInput.className = 'form-control rounded-3 py-2 fw-bold bg-light text-danger';
        } else {
            changeInput.value = '$0.00';
            changeInput.className = 'form-control rounded-3 py-2 fw-bold bg-light text-success';
        }
    }

    function calculateSplit() {
        const grandTotal = cart.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
        const splitCash = parseFloat(document.getElementById('splitCash').value) || 0;
        let qrAmount = grandTotal - splitCash;
        if (qrAmount < 0) qrAmount = 0;

        document.getElementById('splitQrAmount').value = `$${qrAmount.toFixed(2)}`;
        triggerDynamicKHQR();
    }

    function triggerDynamicKHQR() {
        const method = document.getElementById('paymentMethodSelect').value;
        const grandTotal = cart.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
        let amountToPay = grandTotal;
        let selectedBank = document.getElementById('bankProvider').value;

        if (method === 'split') {
            const splitCash = parseFloat(document.getElementById('splitCash').value) || 0;
            amountToPay = grandTotal - splitCash;
            if (amountToPay < 0) amountToPay = 0;
            selectedBank = document.getElementById('splitBankProvider').value;
        }

        if (amountToPay <= 0) {
            document.getElementById('qrDisplayBox').style.display = 'none';
            return;
        }

        document.getElementById('qrDisplayBox').style.display = 'block';

        fetch(`api-khqr.php?amount=${amountToPay}&bank=${selectedBank}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('dynamicQrImage').src = data.qr_image_url;
                    document.getElementById('qrAmountText').innerText = `Scan to Pay (${data.bank}): $${data.amount}`;
                }
            })
            .catch(err => console.error('KHQR Error:', err));
    }

    function filterCategory(cat, btn) {
        selectedCategory = cat;
        document.querySelectorAll('.category-pill').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        filterProducts();
    }

    function filterProducts() {
        const query = document.getElementById('searchProduct').value.toLowerCase().trim();
        const items = document.querySelectorAll('.product-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const category = item.getAttribute('data-category');
            const matchesSearch = name.includes(query);
            const matchesCategory = (selectedCategory === 'all' || category === selectedCategory);
            item.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
        });
    }

    function completeSale() {
        if (cart.length === 0) {
            alert('Please select at least one item before checkout!');
            return;
        }

        const grandTotal = cart.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
        const method = document.getElementById('paymentMethodSelect').value;
        let cash = 0;
        let bank = '';
        let splitCash = 0;
        let splitQr = 0;

        if (method === 'cash') {
            cash = parseFloat(document.getElementById('cashReceived').value) || 0;
            if (cash < grandTotal) {
                alert('Insufficient cash received!');
                return;
            }
        } else if (method === 'khqr') {
            cash = 0;
            bank = document.getElementById('bankProvider').value;
        } else if (method === 'credit_card') {
            cash = grandTotal;
            bank = 'credit_card';
        } else if (method === 'split') {
            splitCash = parseFloat(document.getElementById('splitCash').value) || 0;
            splitQr = grandTotal - splitCash;
            bank = document.getElementById('splitBankProvider').value;
            if (splitCash <= 0 || splitQr <= 0) {
                alert('Please enter valid split payment amounts!');
                return;
            }
        }

        fetch('save-sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                payment_method: method,
                cash: cash,
                bank: bank,
                split_cash: splitCash,
                split_qr: splitQr,
                grand_total: grandTotal,
                cart_data: JSON.stringify(cart)
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'receipt.php?invoice=' + data.invoice;
            } else {
                alert('Save failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Network error! Please check your connection.'));
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    renderCart();
</script>

</body>
</html>