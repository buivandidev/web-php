<?php
// views/admin/dashboard.php
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom border-secondary">
    <h1 class="h2 text-warning fw-bold">Thống Kê Chung</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-warning" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Làm mới dữ liệu
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Phim -->
    <div class="col-sm-6 col-lg-3">
        <div class="card bg-black border border-secondary p-3 rounded shadow-sm h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                    <i class="bi bi-film fs-3"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1">Số lượng phim</h6>
                    <h3 class="text-light fw-bold mb-0"><?= $stats['movie_count'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Thành viên -->
    <div class="col-sm-6 col-lg-3">
        <div class="card bg-black border border-secondary p-3 rounded shadow-sm h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1">Khách hàng</h6>
                    <h3 class="text-light fw-bold mb-0"><?= $stats['user_count'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Vé đã bán -->
    <div class="col-sm-6 col-lg-3">
        <div class="card bg-black border border-secondary p-3 rounded shadow-sm h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                    <i class="bi bi-ticket-perforated fs-3"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1">Vé đã bán</h6>
                    <h3 class="text-light fw-bold mb-0"><?= $stats['ticket_count'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Doanh thu -->
    <div class="col-sm-6 col-lg-3">
        <div class="card bg-black border border-secondary p-3 rounded shadow-sm h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1">Tổng doanh thu</h6>
                    <h3 class="text-light fw-bold mb-0"><?= number_format($stats['revenue'], 0, ',', '.') ?>₫</h3>
                </div>
            </div>
        </div>
    </div>
</div>
