<?php
// views/layouts/admin.php
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CinemaX Admin Dashboard') ?></title>

    <!-- Bootstrap 5.3 Dark Mode -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body class="bg-dark text-light min-vh-100 d-flex flex-column">

    <!-- Navbar -->
    <?php require VIEW_PATH . '/partials/navbar.php'; ?>

    <div class="container-fluid flex-grow-1 d-flex">
        <div class="row w-100 flex-grow-1">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 bg-black border-end border-secondary py-4 sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item">
                            <a class="nav-link text-light p-2 d-flex align-items-center gap-2 rounded hover-bg" href="/admin/dashboard">
                                <i class="bi bi-speedometer2 text-warning"></i>
                                Thống kê chung
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light p-2 d-flex align-items-center gap-2 rounded hover-bg" href="/admin/movies">
                                <i class="bi bi-film text-warning"></i>
                                Quản lý phim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light p-2 d-flex align-items-center gap-2 rounded hover-bg" href="/">
                                <i class="bi bi-arrow-left text-secondary"></i>
                                Về Trang chủ
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Panel Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-dark">
                <!-- Flash Messages -->
                <?php require VIEW_PATH . '/partials/flash_message.php'; ?>
                
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-secondary text-center py-3 border-top border-secondary">
        <small>&copy; <?= date('Y') ?> CinemaX. Admin Panel.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
