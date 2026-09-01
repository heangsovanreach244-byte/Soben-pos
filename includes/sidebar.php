<?php
// ទាញយកឈ្មោះ Page បច្ចុប្បន្ន
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="row g-0">
    <div class="col-md-2">
        <div class="sidebar">

            <h5 class="text-white text-center py-3">
                MENU
            </h5>

            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
            </a>

            <a href="products.php" class="<?= ($current_page == 'products.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-mug-hot"></i>
                Products
            </a>

            <a href="categories.php" class="<?= ($current_page == 'categories.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-tags"></i>
                Categories
            </a>

            <!-- បន្ថែម Menu Modifiers ត្រង់នេះ -->
            <a href="modifiers.php" class="<?= (in_array($current_page, ['modifiers.php', 'modifier-add.php', 'modifier-edit.php'])) ? 'active' : ''; ?>">
                <i class="fa-solid fa-sliders"></i>
                Modifiers
            </a>

            <a href="customers.php" class="<?= ($current_page == 'customers.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                Customers
            </a>

            <a href="suppliers.php" class="<?= ($current_page == 'suppliers.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-truck"></i>
                Suppliers
            </a>

            <a href="pos.php" class="<?= ($current_page == 'pos.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-cash-register"></i>
                POS Sales
            </a>

            <a href="sales.php" class="<?= ($current_page == 'sales.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-receipt"></i>
                Sales History
            </a>

            <a href="reports.php" class="<?= ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i>
                Reports
            </a>

            <a href="settings.php" class="<?= ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gear"></i>
                Settings
            </a>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>
    </div>

    <div class="col-md-10">
        <div class="content p-4">