
<?php

$full_name = $_SESSION['full_name'] ?? 'Administrator';
$role      = $_SESSION['role'] ?? 'Admin';

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

    <div class="container-fluid">

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php">

            <i class="fa-solid fa-mug-hot text-warning"></i>

            Soben Cafe POS

        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarContent">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarContent">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item me-3">

                    <span class="text-light">

                        <i class="fa-solid fa-user"></i>

                        <?php echo htmlspecialchars($full_name); ?>

                    </span>

                </li>

                <li class="nav-item me-3">

                    <span class="badge bg-success">

                        <?php echo htmlspecialchars($role); ?>

                    </span>

                </li>

                <li class="nav-item">

                    <a
                        href="../logout.php"
                        class="btn btn-danger btn-sm">

                        <i class="fa-solid fa-right-from-bracket"></i>

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>

