<?php

require_once '../connect.php';
require_once '../includes/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $shop_name      = trim($_POST['shop_name'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $currency       = trim($_POST['currency'] ?? '$');
    $tax_rate       = floatval($_POST['tax_rate'] ?? 0);
    $receipt_footer = trim($_POST['receipt_footer'] ?? '');

    try {
        // ឆែកមើល Data ក្នុង Table settings
        $check = $db->query("SELECT id FROM settings LIMIT 1");
        $existingRow = $check->fetch(PDO::FETCH_ASSOC);

        if ($existingRow) {
            // UPDATE ប្រសិនបើមាន Row រួចហើយ
            $stmt = $db->prepare("
                UPDATE settings
                SET
                    shop_name = ?,
                    phone = ?,
                    address = ?,
                    currency = ?,
                    tax_rate = ?,
                    receipt_footer = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $shop_name,
                $phone,
                $address,
                $currency,
                $tax_rate,
                $receipt_footer,
                $existingRow['id']
            ]);
        } else {
            // INSERT ថ្មី ប្រសិនបើគ្មាន Row
            $stmt = $db->prepare("
                INSERT INTO settings (shop_name, phone, address, currency, tax_rate, receipt_footer)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $shop_name,
                $phone,
                $address,
                $currency,
                $tax_rate,
                $receipt_footer
            ]);
        }

        $message = "Settings Updated Successfully";
    } catch (PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// ទាញយកទិន្នន័យមកបង្ហាញលើ Form
$setting = [];
try {
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    $setting = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    //
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">
            <h4 class="mb-0">Shop Settings</h4>
        </div>

        <div class="card-body">

            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Shop Name</label>
                        <input
                            type="text"
                            name="shop_name"
                            class="form-control"
                            value="<?php echo htmlspecialchars($setting['shop_name'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?php echo htmlspecialchars($setting['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Currency</label>
                        <input
                            type="text"
                            name="currency"
                            class="form-control"
                            value="<?php echo htmlspecialchars($setting['currency'] ?? '$'); ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax Rate (%)</label>
                        <input
                            type="number"
                            step="0.01"
                            name="tax_rate"
                            class="form-control"
                            value="<?php echo htmlspecialchars($setting['tax_rate'] ?? '0.00'); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="<?php echo htmlspecialchars($setting['address'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Receipt Footer</label>
                    <textarea
                        name="receipt_footer"
                        rows="4"
                        class="form-control"><?php echo htmlspecialchars($setting['receipt_footer'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Settings
                </button>

            </form>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>