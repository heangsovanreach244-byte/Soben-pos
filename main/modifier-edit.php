<?php
require_once '../connect.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/sidebar.php';

$id = intval($_GET['id'] ?? 0);
// កែពី $pdo មកជា $db វិញ
$stmt = $db->prepare("SELECT * FROM modifiers WHERE id = ?");
$stmt->execute([$id]);
$modifier = $stmt->fetch();

if (!$modifier) {
    header("Location: modifiers.php");
    exit;
}

// កែពី $pdo មកជា $db វិញ
$stmtGroups = $db->query("SELECT * FROM modifier_groups ORDER BY id DESC");
$groups = $stmtGroups->fetchAll();
?>

<div class="content-wrapper p-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-pencil-square"></i> Edit Option / Modifier
                </div>
                <div class="card-body p-4">
                    <form action="modifier-update.php" method="POST">
                        <input type="hidden" name="id" value="<?= $modifier['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Group <span class="text-danger">*</span></label>
                            <select name="group_id" class="form-select" required>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= $g['id'] == $modifier['group_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Option / Add-on Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($modifier['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?= $modifier['price'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $modifier['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $modifier['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="modifiers.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>