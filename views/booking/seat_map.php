<?php
// views/booking/seat_map.php
?>
<div class="row g-4">

    <!-- Cột trái: Sơ đồ ghế -->
    <div class="col-lg-8">
        <div class="card bg-secondary border-0 shadow-lg" style="background-color: #1a1a2e !important; border: 1px solid #2d2d44 !important;">
            <div class="card-header bg-black border-bottom border-secondary p-3">
                <h5 class="mb-1 text-warning fw-bold">
                    <i class="bi bi-grid-3x3-gap me-2"></i>CHỌN GHẾ NGỒI
                </h5>
                <small class="text-secondary d-block mt-1">
                    <strong><?= htmlspecialchars($seatMap->movieTitle) ?></strong> —
                    <?= date('d/m/Y', strtotime($seatMap->showDate)) ?> lúc
                    <strong><?= date('H:i', strtotime($seatMap->startTime)) ?></strong>
                    | <?= htmlspecialchars($seatMap->roomName) ?>
                </small>
            </div>
            <div class="card-body p-4">
                <?php require VIEW_PATH . '/partials/seat_map.php'; ?>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thông tin đặt vé -->
    <div class="col-lg-4">
        <div class="card bg-secondary border-0 shadow-lg sticky-top" style="top: 20px; background-color: #1a1a2e !important; border: 1px solid #2d2d44 !important;">
            <div class="card-header bg-black border-bottom border-secondary p-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-warning"></i>THÔNG TIN ĐẶT VÉ</h5>
            </div>
            <div class="card-body p-4">

                <!-- Danh sách ghế đã chọn (cập nhật qua JS) -->
                <div class="mb-4">
                    <label class="text-secondary small fw-bold">Ghế đã chọn:</label>
                    <div id="selected-seats-display" class="mt-2 p-2 bg-dark rounded min-vh-10 d-flex flex-wrap gap-1 align-items-center" style="min-height: 48px; border: 1px dashed #444;">
                        <span class="text-secondary fst-italic">Chưa chọn ghế</span>
                    </div>
                </div>

                <!-- Tổng tiền (JS cập nhật) -->
                <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                    <span class="text-secondary">Đơn giá:</span>
                    <span class="text-warning fw-bold"
                          data-price-per-seat="<?= $seatMap->pricePerSeat ?>">
                        <?= number_format($seatMap->pricePerSeat, 0, ',', '.') ?>₫ / ghế
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                    <span class="text-secondary">Số lượng:</span>
                    <span id="seat-count" class="text-light fw-bold">0</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-light fw-bold fs-5">TỔNG CỘNG:</span>
                    <span id="total-price" class="text-warning fw-bold fs-4">0₫</span>
                </div>

                <!-- Form giữ ghế — PHẢI có CSRF token -->
                <form id="booking-form" method="POST" action="/booking/hold">
                    <?= csrf_field() ?>
                    <input type="hidden" name="showtime_id" value="<?= $seatMap->showtimeId ?>">

                    <button type="submit"
                            id="btn-hold"
                            class="btn btn-warning w-100 btn-lg fw-bold py-3 shadow"
                            disabled>
                        <i class="bi bi-lock me-2"></i>GIỮ GHẾ (10 PHÚT)
                    </button>
                </form>

                <p class="text-secondary small text-center mt-3 mb-0">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Ghế được khóa giữ chỗ trong 10 phút sau khi xác nhận
                </p>
            </div>
        </div>
    </div>
</div>

<!-- #6 Thanh Checkout ghim đáy — Mobile only -->
<div class="sticky-checkout" id="sticky-checkout">
    <div class="checkout-info">
        <div>
            <span class="text-secondary small" id="sticky-seat-info">Chưa chọn ghế</span>
            <span class="total-price d-block" id="sticky-total-price">0₫</span>
        </div>
        <button class="btn btn-warning fw-bold px-4" id="sticky-btn-hold" disabled
                onclick="document.getElementById('booking-form').requestSubmit();">
            <i class="bi bi-lock me-2"></i>Giữ Ghế
        </button>
    </div>
</div>
