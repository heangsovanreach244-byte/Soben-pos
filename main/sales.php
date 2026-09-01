<?php
require_once '../connect.php';
require_once '../includes/auth.php';

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// ទាញយកទិន្នន័យ Sales History (ដូរពី transaction_id មកប្រើ id វិញ)
$sales = $db->query("
    SELECT 
        id,
        invoice_number,
        product_name,
        qty,
        price,
        amount,
        cashier,
        sale_date
    FROM sales
    ORDER BY id DESC
");
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h4 class="m-0 font-weight-bold text-primary">
                <i class="fa-solid fa-receipt me-2"></i> ប្រវត្តិការលក់ (Sales History)
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle datatable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>លេខវិក្កយបត្រ (Invoice)</th>
                            <th>មុខទំនិញ (Product)</th>
                            <th class="text-center">ចំនួន (Qty)</th>
                            <th class="text-end">តម្លៃរាយ</th>
                            <th class="text-end">សរុប (Amount)</th>
                            <th>អ្នកគិតប្រាក់ (Cashier)</th>
                            <th class="text-center">កាលបរិច្ឆេទ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        while ($row = $sales->fetch(PDO::FETCH_ASSOC)): 
                            $sale_id = $row['id'];
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++; ?></td>
                            <td class="fw-bold text-primary">
                                <?= htmlspecialchars($row['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td>
                                <span class="fw-bold"><?= htmlspecialchars($row['product_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                
                                <?php
                                // ទាញយក Modifiers/Add-ons តាម sale_detail_id
                                $stmtMods = $db->prepare("SELECT modifier_name, price FROM sale_item_modifiers WHERE sale_detail_id = ?");
                                $stmtMods->execute([$sale_id]);
                                $modifiers = $stmtMods->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($modifiers)):
                                ?>
                                    <div class="ms-2 mt-1 small text-muted">
                                        <?php foreach ($modifiers as $mod): ?>
                                            <div>
                                                <i class="fa-solid fa-angle-right text-secondary me-1"></i>
                                                <?= htmlspecialchars($mod['modifier_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?= ($mod['price'] > 0) ? ' <span class="text-success">(+$' . number_format($mod['price'], 2) . ')</span>' : ''; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= number_format((int)($row['qty'] ?? 0)); ?></td>
                            <td class="text-end">$<?= number_format((float)($row['price'] ?? 0), 2); ?></td>
                            <td class="text-end fw-bold text-success">$<?= number_format((float)($row['amount'] ?? 0), 2); ?></td>
                            <td><?= htmlspecialchars($row['cashier'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-center">
                                <?= !empty($row['sale_date']) ? date('d-M-Y h:i A', strtotime($row['sale_date'])) : '-'; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>