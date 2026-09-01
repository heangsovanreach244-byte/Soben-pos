<?php
require_once '../connect.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/sidebar.php';

// Handle Add New Modifier Group / Option
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_group') {
        $group_name = trim($_POST['group_name']);
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $is_multiple = isset($_POST['is_multiple']) ? 1 : 0;

        if (!empty($group_name)) {
            $stmt = $db->prepare("INSERT INTO modifier_groups (name, is_required, is_multiple) VALUES (?, ?, ?)");
            $stmt->execute([$group_name, $is_required, $is_multiple]);
            $message = "Modifier group added successfully!";
        }
    } elseif ($_POST['action'] === 'add_option') {
        $group_id = intval($_POST['group_id']);
        $option_name = trim($_POST['option_name']);
        $price = floatval($_POST['price']);

        if (!empty($option_name) && $group_id > 0) {
            $stmt = $db->prepare("INSERT INTO modifiers (group_id, name, price) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $option_name, $price]);
            $message = "Modifier option added successfully!";
        }
    }
}

// Fetch all modifier groups
$stmtGroups = $db->query("SELECT * FROM modifier_groups ORDER BY id DESC");
$groups = $stmtGroups->fetchAll();
?>

<div class="content-wrapper p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Manage Modifiers & Add-ons</h3>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                <i class="bi bi-folder-plus"></i> Add New Group
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                <i class="bi bi-plus-circle"></i> Add Option / Add-on
            </button>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($groups as $group): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold"><?= htmlspecialchars($group['name']) ?></h5>
                        <div>
                            <span class="badge bg-<?= $group['is_required'] ? 'danger' : 'secondary' ?>">
                                <?= $group['is_required'] ? 'Required' : 'Optional' ?>
                            </span>
                            <span class="badge bg-<?= $group['is_multiple'] ? 'info' : 'warning' ?>">
                                <?= $group['is_multiple'] ? 'Multiple Choice' : 'Single Choice' ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Option Name</th>
                                    <th>Additional Price ($)</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtOpt = $db->prepare("SELECT * FROM modifiers WHERE group_id = ? ORDER BY id ASC");
                                $stmtOpt->execute([$group['id']]);
                                $options = $stmtOpt->fetchAll();
                                if (count($options) > 0):
                                    foreach ($options as $opt):
                                ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($opt['name']) ?></td>
                                            <td class="text-success">+$<?= number_format($opt['price'], 2) ?></td>
                                            <td>
                                                <?php $status = $opt['status'] ?? 'active'; ?>
                                                <span class="badge bg-<?= $status === 'active' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="modifier-delete.php?id=<?= $opt['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this option?');">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No options found in this group yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal 1: Add New Modifier Group -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modifiers.php" method="POST">
                <input type="hidden" name="action" value="add_group">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Modifier Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Group Name</label>
                        <input type="text" name="group_name" class="form-control" placeholder="e.g. Size, Sugar Level, Extra Toppings" required>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_required" value="1" id="reqCheck">
                        <label class="form-check-label" for="reqCheck">
                            Required Selection
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_multiple" value="1" id="multiCheck">
                        <label class="form-check-label" for="multiCheck">
                            Allow Multiple Choices (Checkbox)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Add Option / Add-on -->
<div class="modal fade" id="addOptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modifiers.php" method="POST">
                <input type="hidden" name="action" value="add_option">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Option / Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Group</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">-- Select Group --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Option / Add-on Name</label>
                        <input type="text" name="option_name" class="form-control" placeholder="e.g. Large, 50% Sweet, Pearl" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Additional Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Option</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>