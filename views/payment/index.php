<?php
// views/payment/index.php
$remainingSeconds = max(0, strtotime($booking->holdExpiryTime) - time());
?>
<div class="row justify-content-center">
    <div class="col-lg-7">

        <!-- Countdown Timer -->
        <div class="alert alert-warning border-0 shadow-lg d-flex align-items-center mb-4 p-3" role="alert" style="background-color: #382c0f; color: #ffc107;">
            <i class="bi bi-clock-history me-3 fs-3 animate-pulse"></i>
            <div class="timer-container ms-auto">
                <span class="d-block small text-secondary me-2">Thời gian giữ ghế:</span>
                <strong id="countdown-timer" class="timer-value" data-remaining-seconds="<?= $remainingSeconds ?>">--:--</strong>
            </div>
        </div>

        <!-- Thông tin đặt vé -->
        <div class="card bg-secondary border-0 shadow mb-4" style="background-color: #1a1a2e !important; border: 1px solid #2d2d44 !important;">
            <div class="card-header bg-black py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-film me-2 text-warning"></i>Chi tiết đặt vé</h5>
            </div>
            <div class="card-body p-4">
                <h4 class="text-warning fw-bold mb-3"><?= htmlspecialchars($booking->movieTitle) ?></h4>
                <div class="row g-2 mb-3">
                    <div class="col-sm-6 text-secondary">
                        <i class="bi bi-calendar3 me-2 text-light"></i>Ngày chiếu: <span class="text-light fw-semibold"><?= date('d/m/Y', strtotime($booking->showDate)) ?></span>
                    </div>
                    <div class="col-sm-6 text-secondary">
                        <i class="bi bi-clock me-2 text-light"></i>Suất chiếu: <span class="text-light fw-semibold"><?= date('H:i', strtotime($booking->startTime)) ?></span>
                    </div>
                    <div class="col-sm-6 text-secondary">
                        <i class="bi bi-door-open me-2 text-light"></i>Phòng chiếu: <span class="text-light fw-semibold"><?= htmlspecialchars($booking->roomName) ?></span>
                    </div>
                    <div class="col-sm-6 text-secondary">
                        <i class="bi bi-ticket me-2 text-light"></i>Số lượng: <span class="text-light fw-semibold"><?= $booking->quantity ?> vé</span>
                    </div>
                </div>
                <hr class="border-secondary">
                <div class="mb-0">
                    <label class="text-secondary small fw-bold d-block mb-2">Ghế ngồi đã chọn:</label>
                    <?php foreach ($booking->selectedSeats as $seat): ?>
                        <span class="badge bg-success fs-6 me-2 shadow-sm"><?= htmlspecialchars($seat) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Mã giảm giá -->
        <div class="card bg-secondary border-0 shadow mb-4" style="background-color: #1a1a2e !important; border: 1px solid #2d2d44 !important;">
            <div class="card-body p-4">
                <label class="form-label text-light fw-bold">Mã giảm giá (Voucher)</label>
                <div class="input-group">
                    <input type="text" id="promo-input"
                           class="form-control bg-dark text-light border-secondary py-2"
                           placeholder="Nhập mã giảm giá (ví dụ: GIAM20)..."
                           value="<?= htmlspecialchars($booking->promotionCode ?? '') ?>">
                    <button class="btn btn-warning fw-bold px-4" id="btn-apply-promo" type="button">
                        Áp dụng
                    </button>
                </div>
                <div id="promo-feedback" class="mt-2 small"></div>
            </div>
        </div>

        <!-- Tổng tiền -->
        <div class="card bg-secondary border-0 shadow mb-4" style="background-color: #1a1a2e !important; border: 1px solid #2d2d44 !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Tạm tính (<?= $booking->quantity ?> vé):</span>
                    <span class="fw-semibold text-light"><?= number_format($booking->subtotal, 0, ',', '.') ?>₫</span>
                </div>
                <div class="d-flex justify-content-between mb-2" id="discount-row"
                     style="<?= $booking->discount > 0 ? '' : 'display:none!important' ?>">
                    <span class="text-success">Giảm giá:</span>
                    <span class="text-success fw-bold" id="discount-display">
                        -<?= number_format($booking->discount, 0, ',', '.') ?>₫
                    </span>
                </div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-light fs-5">TỔNG THANH TOÁN:</strong>
                    <strong class="text-warning fs-3" id="total-display">
                        <?= number_format($booking->totalPrice, 0, ',', '.') ?>₫
                    </strong>
                </div>
            </div>
        </div>

        <!-- Form thanh toán — PHẢI có CSRF token -->
        <form method="POST" action="/payment/confirm" id="payment-form">
            <?= csrf_field() ?>
            <input type="hidden" name="total_price" id="final-total" value="<?= $booking->totalPrice ?>">
            <input type="hidden" name="promotion_code" id="final-promo" value="<?= htmlspecialchars($booking->promotionCode ?? '') ?>">

            <div class="mb-4">
                <label class="form-label fw-bold text-light mb-3">Phương thức thanh toán</label>
                <div class="d-grid gap-3">
                    <div class="form-check bg-black rounded p-3 border border-secondary shadow-sm hover-border-warning">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method"
                               id="pay-vnpay" value="vnpay" required>
                        <label class="form-check-label text-light w-100" for="pay-vnpay" style="cursor: pointer;">
                            <i class="bi bi-credit-card me-2 text-primary fs-5"></i>
                            <strong class="fs-6">VNPay</strong>
                            <small class="text-secondary d-block mt-1">Thẻ ATM nội địa, Internet Banking, Quét mã QR</small>
                        </label>
                    </div>
                    
                    <div class="form-check bg-black rounded p-3 border border-secondary shadow-sm hover-border-warning">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method"
                               id="pay-momo" value="momo">
                        <label class="form-check-label text-light w-100" for="pay-momo" style="cursor: pointer;">
                            <i class="bi bi-phone me-2 text-danger fs-5"></i>
                            <strong class="fs-6">Ví điện tử MoMo</strong>
                            <small class="text-secondary d-block mt-1">Thanh toán nhanh qua ứng dụng MoMo</small>
                        </label>
                    </div>

                    <div class="form-check bg-black rounded p-3 border border-secondary shadow-sm hover-border-warning">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method"
                               id="pay-cash" value="cash">
                        <label class="form-check-label text-light w-100" for="pay-cash" style="cursor: pointer;">
                            <i class="bi bi-cash-coin me-2 text-success fs-5"></i>
                            <strong class="fs-6">Thanh toán tại quầy</strong>
                            <small class="text-secondary d-block mt-1">Hoàn thành giao dịch trực tiếp tại rạp</small>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 btn-lg fw-bold py-3 shadow mb-4" id="btn-pay-submit">
                <i class="bi bi-check-circle me-2"></i>XÁC NHẬN THANH TOÁN
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Show spinner on payment form submit
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', () => {
            const btnSubmit = document.getElementById('btn-pay-submit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý giao dịch...';
            }
        });
    }
});
</script>
