<?php
require_once '../connect.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/sidebar.php';

// កែពី $pdo មកជា $db វិញ
$stmtGroups = $db->query("SELECT * FROM modifier_groups ORDER BY id DESC");
$groups = $stmtGroups->fetchAll();
?>

<div class="content-wrapper p-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-plus-circle"></i> Add New Option / Modifier
                </div>
                <div class="card-body p-4">
                    <form action="modifier-save.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Group <span class="text-danger">*</span></label>
                            <select name="group_id" class="form-select" required>
                                <option value="">-- Select Group --</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Option / Add-on Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Large, 50% Sweet, Pearl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" value="0.00" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="modifiers.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>