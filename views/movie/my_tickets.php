<?php
// views/movie/my_tickets.php
?>
<h1 class="h3 fw-bold text-light border-start border-warning border-4 ps-3 mb-4">Vé Của Tôi</h1>

<?php if (empty($tickets)): ?>
    <div class="text-center py-5 card bg-black border border-secondary shadow-sm">
        <div class="card-body py-5 text-secondary">
            <i class="bi bi-ticket-perforated display-1 mb-3"></i>
            <p class="fs-5 fst-italic">Bạn chưa mua vé xem phim nào.</p>
            <a href="/movies" class="btn btn-warning fw-bold mt-2">Đặt Vé Ngay</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($tickets as $ticket): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card bg-black border border-secondary shadow-sm rounded overflow-hidden h-100" style="background: linear-gradient(135deg, #0f0f1b 0%, #15152b 100%);">
                    <div class="card-header bg-black py-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                        <span class="text-warning fw-bold"><i class="bi bi-ticket-perforated-fill me-2"></i>CinemaX Ticket</span>
                        <span class="badge bg-success">Đã thanh toán</span>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="text-light fw-bold mb-3"><?= htmlspecialchars($ticket['movie_title']) ?></h5>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6 text-secondary small">
                                <i class="bi bi-calendar3 me-1 text-light"></i>Ngày chiếu: <span class="text-light d-block fw-semibold mt-1"><?= date('d/m/Y', strtotime($ticket['show_date'])) ?></span>
                            </div>
                            <div class="col-6 text-secondary small">
                                <i class="bi bi-clock me-1 text-light"></i>Suất chiếu: <span class="text-light d-block fw-semibold mt-1"><?= date('H:i', strtotime($ticket['start_time'])) ?></span>
                            </div>
                            <div class="col-6 text-secondary small">
                                <i class="bi bi-grid-3x3-gap me-1 text-light"></i>Ghế ngồi: <span class="text-warning d-block fw-bold mt-1"><?= htmlspecialchars($ticket['seat_code']) ?></span>
                            </div>
                            <div class="col-6 text-secondary small">
                                <i class="bi bi-cash-coin me-1 text-light"></i>Giá vé: <span class="text-light d-block fw-semibold mt-1"><?= number_format($ticket['total_price'], 0, ',', '.') ?>₫</span>
                            </div>
                        </div>
                        
                        <hr class="border-secondary mb-3">
                        
                        <div class="d-flex justify-content-between align-items-center text-secondary small font-monospace">
                            <span>Mã vé: #CX<?= str_pad($ticket['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span>Mua lúc: <?= date('d/m H:i', strtotime($ticket['booked_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
