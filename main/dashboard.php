<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

// Safe queries
$totalProducts   = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalCustomers  = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalSuppliers  = $db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$totalStock      = $db->query("SELECT IFNULL(SUM(qty),0) FROM products")->fetchColumn();

// បន្ថែមការទាញយកទិន្នន័យសរុบស្តុកគ្រឿងផ្សំ (Ingredients Stock)
$totalIngredients = 0;
$lowStockIngredients = 0;
try {
    $totalIngredients = $db->query("SELECT IFNULL(SUM(stock_qty),0) FROM ingredients")->fetchColumn();
    // ឆែកមើលគ្រឿងផ្សំណាដែលជិតអស់ក្នុងស្តុក (ឧទាហរណ៍តិចជាង ឬស្មើ ៥)
    $lowStockIngredients = $db->query("SELECT COUNT(*) FROM ingredients WHERE stock_qty <= 5")->fetchColumn();
} catch (Exception $e) {
    // ករណីតារាង ingredients មិនទាន់បង្កើត
}

try {
    $totalSales = $db->query("SELECT IFNULL(SUM(total_amount),0) FROM sales")->fetchColumn();
} catch (Exception $e) {
    try {
        $totalSales = $db->query("SELECT IFNULL(SUM(grand_total),0) FROM sales")->fetchColumn();
    } catch (Exception $e2) {
        try {
            $totalSales = $db->query("SELECT IFNULL(SUM(amount),0) FROM sales")->fetchColumn();
        } catch (Exception $e3) {
            $totalSales = 0;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soben Cafe POS - Premium Dashboard</title>
    
    <!-- Google Fonts & Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --cafe-dark: #2B1810;
            --cafe-deep: #3D2317;
            --cafe-primary: #6F4E37;
            --cafe-accent: #D4A373;
            --cafe-gold: #E6B800;
            --cafe-cream: #FAEDCD;
            --cafe-bg: #F8F5F0;
            --card-bg: rgba(255, 255, 255, 0.85);
            --text-main: #231914;
            --text-sub: #7A6960;
        }

        * {
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cafe-bg);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 10% 10%, rgba(212, 163, 115, 0.08) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(111, 78, 55, 0.05) 0%, transparent 40%);
        }

        /* Top Bar Header */
        .header-bar {
            background: linear-gradient(135deg, var(--cafe-dark) 0%, var(--cafe-deep) 100%);
            padding: 16px 0;
            box-shadow: 0 10px 30px rgba(43, 24, 16, 0.25);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .brand-logo {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--cafe-cream) !important;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo i {
            color: var(--cafe-accent);
            animation: steam 2.5s infinite ease-in-out;
        }

        @keyframes steam {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.9; }
            50% { transform: translateY(-4px) rotate(-3deg); opacity: 1; }
        }

        /* Custom Card Glassmorphism */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(111, 78, 55, 0.06);
            animation: fadeInUp 0.7s ease-out forwards;
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 40px rgba(111, 78, 55, 0.12);
            border-color: var(--cafe-accent);
        }

        /* Stat Cards Design */
        .stat-badge {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.6);
        }

        .bg-products { background: linear-gradient(135deg, #FFE8D6 0%, #FFD7BA 100%); color: #BC6C25; }
        .bg-categories { background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%); color: #0284C7; }
        .bg-customers { background: linear-gradient(135deg, #DCFCE7 0%, #BBF7D0 100%); color: #16A34A; }
        .bg-suppliers { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); color: #DC2626; }
        .bg-stock { background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%); color: #9333EA; }
        .bg-ingredients { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); color: #D97706; }
        .bg-sales { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); color: #059669; }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--cafe-dark);
            letter-spacing: -0.5px;
        }

        .stat-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* Quick Actions Grid */
        .action-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 18px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid rgba(212, 163, 115, 0.2);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, var(--cafe-primary) 0%, var(--cafe-deep) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(111, 78, 55, 0.18);
        }

        .action-card * {
            position: relative;
            z-index: 2;
        }

        .action-card:hover * {
            color: #FFFFFF !important;
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--cafe-cream);
            color: var(--cafe-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* POS Highlight Action Button */
        .btn-pos-glow {
            background: linear-gradient(135deg, #D4A373 0%, #6F4E37 100%) !important;
            color: #FFFFFF !important;
            box-shadow: 0 8px 25px rgba(212, 163, 115, 0.4);
            border: none !important;
        }

        .btn-pos-glow .action-icon {
            background: rgba(255,255,255,0.2) !important;
            color: #FFFFFF !important;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Live Clock Pill */
        .time-pill {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 6px 18px;
            color: var(--cafe-cream);
            font-weight: 600;
            font-size: 0.88rem;
        }
    </style>
</head>

<body>

<!-- Header Topbar -->
<header class="header-bar mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="brand-logo text-decoration-none" href="#">
            <i class="fa-solid fa-mug-hot"></i>
            <span>SOBEN CAFE POS</span>
        </a>

        <div class="d-flex align-items-center gap-3">
            <div class="time-pill d-none d-md-block" id="liveClock">
                <i class="fa-regular fa-clock me-2 text-warning"></i><span>00:00:00 AM</span>
            </div>

            <div class="dropdown">
                <button class="btn btn-dark btn-sm rounded-pill px-3 py-2 dropdown-toggle d-flex align-items-center gap-2 border-0" 
                        style="background: rgba(255,255,255,0.1);" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-circle-user text-warning fs-5"></i>
                    <span class="text-white fw-semibold">
                        <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Administrator'; ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2">
                    <li><a class="dropdown-menu-item dropdown-item text-danger py-2" href="../logout.php"><i class="fa-solid fa-power-off me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Main Dashboard Body -->
<div class="container pb-5">

    <!-- Welcome Banner Section -->
    <div class="glass-card mb-4" style="background: linear-gradient(135deg, rgba(250,237,205,0.6) 0%, rgba(255,255,255,0.9) 100%);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-extrabold mb-1" style="color: var(--cafe-dark);" id="greetingText">Good Day! ☕</h2>
                <p class="text-muted mb-0 fw-medium"> Here is your cafe's performance and real-time operations summary.</p>
            </div>
            <a href="pos.php" class="btn btn-pos-glow px-4 py-3 rounded-4 fw-bold d-flex align-items-center gap-2 text-decoration-none">
                <i class="fa-solid fa-cash-register fs-5"></i>
                <span>OPEN POS SYSTEM</span>
            </a>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="row g-4 mb-4">
        <!-- Total Products -->
        <div class="col-6 col-lg-3">
            <div class="glass-card">
                <div class="stat-badge bg-products">
                    <i class="fa-solid fa-mug-saucer"></i>
                </div>
                <div class="stat-value"><?php echo number_format($totalProducts); ?></div>
                <div class="stat-title">Menu Products</div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-6 col-lg-3">
            <div class="glass-card">
                <div class="stat-badge bg-categories">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="stat-value"><?php echo number_format($totalCategories); ?></div>
                <div class="stat-title">Categories</div>
            </div>
        </div>

        <!-- Customers -->
        <div class="col-6 col-lg-3">
            <div class="glass-card">
                <div class="stat-badge bg-customers">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($totalCustomers); ?></div>
                <div class="stat-title">Customers</div>
            </div>
        </div>

        <!-- Suppliers -->
        <div class="col-6 col-lg-3">
            <div class="glass-card">
                <div class="stat-badge bg-suppliers">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($totalSuppliers); ?></div>
                <div class="stat-title">Suppliers</div>
            </div>
        </div>
    </div>

    <!-- Stock & Revenue Summary (រួមបញ្ចូលទាំងស្តុកគ្រឿងផ្សំ Ingredients) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-title mb-2">Product Stock Qty</div>
                    <div class="stat-value" style="color: #9333EA;"><?php echo number_format($totalStock); ?> <span class="fs-6 text-muted font-normal">units</span></div>
                </div>
                <div class="stat-badge bg-stock mb-0" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>

        <!-- បان៉ែលថ្មីសម្រាប់បង្ហាញសរុបស្តុកគ្រឿងផ្សំ (Ingredients Stock) -->
        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between" style="border-left: 5px solid #D97706;">
                <div>
                    <div class="stat-title mb-2">Ingredients Stock</div>
                    <div class="stat-value" style="color: #D97706;"><?php echo number_format($totalIngredients); ?> <span class="fs-6 text-muted font-normal">items</span></div>
                    <?php if($lowStockIngredients > 0): ?>
                        <small class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $lowStockIngredients; ?> low stock</small>
                    <?php endif; ?>
                </div>
                <div class="stat-badge bg-ingredients mb-0" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fa-solid fa-mortar-pestle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between" style="border-left: 5px solid #059669;">
                <div>
                    <div class="stat-title mb-2">Total Revenue Earned</div>
                    <div class="stat-value text-success">$<?php echo number_format($totalSales, 2); ?></div>
                </div>
                <div class="stat-badge bg-sales mb-0" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Sales Chart Analytics -->
    <div class="glass-card mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0" style="color: var(--cafe-dark);">
                <i class="fa-solid fa-chart-area me-2 text-warning"></i>Weekly Sales Trend Analytics
            </h5>
            <span class="badge rounded-pill bg-light text-dark border px-3 py-2">Updated Live</span>
        </div>
        <div style="height: 280px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Quick Navigation Bar -->
    <div class="glass-card">
        <h5 class="fw-bold mb-4" style="color: var(--cafe-dark);">
            <i class="fa-solid fa-bolt me-2 text-warning"></i>Quick Management Options
        </h5>

        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <a href="pos.php" class="action-card btn-pos-glow">
                    <div class="action-icon">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">POS Sales</div>
                        <small style="opacity: 0.85;">Billing Counter</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="products.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-coffee"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Products</div>
                        <small class="text-muted">Manage Items</small>
                    </div>
                </a>
            </div>

            <!-- បន្ថែមតំណរភ្ជាប់ទៅកាន់ទំព័រគ្រប់គ្រងរូបមន្ត ឬគ្រឿងផ្សំ ប្រសិនបើមាន -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="ingredients.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-blender"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Ingredients</div>
                        <small class="text-muted">Recipe & Stock</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="categories.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Categories</div>
                        <small class="text-muted">Item Types</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="customers.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Customers</div>
                        <small class="text-muted">Member List</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="suppliers.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Suppliers</div>
                        <small class="text-muted">Partners</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="reports.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Reports</div>
                        <small class="text-muted">Sales Data</small>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="settings.php" class="action-card">
                    <div class="action-icon">
                        <i class="fa-solid fa-gear"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Settings</div>
                        <small class="text-muted">System Setup</small>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap JS & Interactive Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Dynamic Greeting & Live Clock Script
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour12: true });
        document.querySelector('#liveClock span').textContent = timeString;

        const hrs = now.getHours();
        let greeting = "Good Day! ☕";
        if (hrs < 12) greeting = "Good Morning! ☕";
        else if (hrs < 18) greeting = "Good Afternoon! ☕";
        else greeting = "Good Evening! ☕";
        
        document.getElementById('greetingText').textContent = greeting;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Chart.js Sales Graph Visualization
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesGradient = ctx.createLinearGradient(0, 0, 0, 250);
    salesGradient.addColorStop(0, 'rgba(212, 163, 115, 0.45)');
    salesGradient.addColorStop(1, 'rgba(212, 163, 115, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales ($)',
                data: [120, 190, 150, 220, 310, 450, 380],
                borderColor: '#6F4E37',
                borderWidth: 3,
                backgroundColor: salesGradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#D4A373',
                pointRadius: 6,
                pointHoverRadius: 9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });
</script>

</body>
</html>