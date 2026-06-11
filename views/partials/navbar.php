<?php
// views/partials/navbar.php
use App\Core\Session;

$currentUserId = Session::get('user_id');
$currentRole   = Session::get('user_role');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning fs-3" href="/">
            <i class="bi bi-film"></i> CinemaX
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/movies">Phim đang chiếu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/movies?status=coming_soon">Phim sắp chiếu</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <?php if ($currentUserId): ?>
                    <?php if ($currentRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-semibold me-2" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-light me-2" href="/my-tickets">
                            <i class="bi bi-ticket-perforated"></i> Vé của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="/logout" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="bi bi-box-arrow-right"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-light me-3" href="/login">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-warning fw-semibold" href="/register">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
