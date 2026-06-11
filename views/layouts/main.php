<?php
// views/layouts/main.php
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CinemaX - Đặt vé xem phim trực tuyến') ?></title>

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

    <!-- Flash Messages -->
    <?php require VIEW_PATH . '/partials/flash_message.php'; ?>

    <!-- Main Content -->
    <main class="container py-4 flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-secondary text-center py-3 mt-auto border-top border-secondary">
        <small>&copy; <?= date('Y') ?> CinemaX. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/seat_selector.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
