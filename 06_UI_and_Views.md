# 06 — Giao diện & Views (Bootstrap 5 Dark Mode)

> **Dành cho AI Agent:** Tất cả View phải theo chuẩn Bootstrap 5 Dark Mode. View chỉ được dùng `echo` và vòng lặp — không chứa SQL, không gọi Service.

---

## 1. Master Layout — Dark Mode

```php
<?php
// views/layouts/main.php
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CinemaX') ?></title>

    <!-- Bootstrap 5.3 Dark Mode -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body class="bg-dark text-light min-vh-100">

    <!-- Navbar -->
    <?php require VIEW_PATH . '/partials/navbar.php'; ?>

    <!-- Flash Messages -->
    <?php require VIEW_PATH . '/partials/flash_message.php'; ?>

    <!-- Main Content -->
    <main class="container py-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-secondary text-center py-3 mt-auto border-top border-secondary">
        <small>&copy; <?= date('Y') ?> CinemaX. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/seat_selector.js"></script>
</body>
</html>
```

---

## 2. Navbar Partial

```php
<?php
// views/partials/navbar.php
$currentUserId = Session::get('user_id');
$currentRole   = Session::get('user_role');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="/">
            <i class="bi bi-film"></i> CinemaX
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/movies">Phim đang chiếu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/movies?status=coming_soon">Phim sắp chiếu</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if ($currentUserId): ?>
                    <?php if ($currentRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/my-tickets">
                            <i class="bi bi-ticket-perforated"></i> Vé của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="/logout" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                Đăng xuất
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-warning ms-2" href="/login">Đăng nhập</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
```

---

## 3. Flash Message Partial

```php
<?php
// views/partials/flash_message.php
$error   = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3 border-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show m-3 border-0" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
```

---

## 4. Trang chủ — Movie Cards

```php
<?php
// views/home/index.php
?>
<!-- Hero Banner -->
<div class="text-center py-5 mb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 16px;">
    <h1 class="display-4 fw-bold text-warning">🎬 CinemaX</h1>
    <p class="lead text-light">Đặt vé xem phim nhanh chóng, tiện lợi</p>
</div>

<!-- Phim đang chiếu -->
<h2 class="h4 mb-3 text-warning border-start border-warning ps-3">
    <i class="bi bi-play-circle-fill me-2"></i>Đang chiếu
</h2>

<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-5">
    <?php foreach ($nowShowing as $movie): ?>
        <div class="col">
            <div class="card bg-secondary border-0 h-100 shadow movie-card"
                 onclick="location.href='/movies/<?= $movie->id ?>'">
                <div class="position-relative">
                    <img src="<?= htmlspecialchars($movie->posterUrl ?: '/assets/img/no-poster.jpg') ?>"
                         class="card-img-top img-fluid rounded-top"
                         alt="<?= htmlspecialchars($movie->title) ?>"
                         style="height: 280px; object-fit: cover;">
                    <!-- Age rating badge -->
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                        <?= htmlspecialchars($movie->ageRating ?? 'P') ?>
                    </span>
                </div>
                <div class="card-body p-2">
                    <h6 class="card-title text-light mb-1 text-truncate">
                        <?= htmlspecialchars($movie->title) ?>
                    </h6>
                    <small class="text-secondary">
                        <i class="bi bi-clock me-1"></i>
                        <?= htmlspecialchars($movie->getFormattedDuration()) ?>
                    </small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Phim sắp chiếu -->
<h2 class="h4 mb-3 text-info border-start border-info ps-3">
    <i class="bi bi-calendar-event me-2"></i>Sắp chiếu
</h2>
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
    <?php foreach ($comingSoon as $movie): ?>
        <!-- tương tự trên, thêm badge "Sắp chiếu" -->
    <?php endforeach; ?>
</div>
```

---

## 5. Sơ đồ Ghế Ngồi — Component quan trọng nhất

```php
<?php
// views/partials/seat_map.php
/**
 * Nhận vào $seatMap (SeatMapViewModel)
 * Render sơ đồ ghế bằng Bootstrap Grid + vòng lặp
 *
 * Màu sắc:
 * - available (trống):  btn-outline-success  (xanh lá viền)
 * - holding (đang giữ): btn-warning          (vàng - disabled)
 * - paid (đã bán):      btn-danger           (đỏ - disabled)
 * - selected (đang chọn, do JS toggle):      btn-success (xanh lá)
 */
?>

<!-- Legend — chú thích màu sắc -->
<div class="d-flex gap-3 justify-content-center mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-2">
        <div class="seat-legend bg-transparent border border-success rounded" style="width:24px;height:24px;"></div>
        <small class="text-light">Trống</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="seat-legend bg-success rounded" style="width:24px;height:24px;"></div>
        <small class="text-light">Đang chọn</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="seat-legend bg-warning rounded" style="width:24px;height:24px;"></div>
        <small class="text-light">Đang giữ</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="seat-legend bg-danger rounded" style="width:24px;height:24px;"></div>
        <small class="text-light">Đã bán</small>
    </div>
</div>

<!-- Màn chiếu -->
<div class="text-center mb-4">
    <div class="d-inline-block px-5 py-1 text-secondary rounded"
         style="background: linear-gradient(to bottom, #555, #222); min-width: 300px; font-size: 0.8rem; letter-spacing: 4px;">
        MÀN CHIẾU
    </div>
</div>

<!-- Sơ đồ ghế -->
<div class="seat-map-container overflow-auto">
    <?php for ($row = 0; $row < $seatMap->totalRows; $row++): ?>
        <?php $rowLabel = chr(ord('A') + $row); ?>

        <div class="d-flex justify-content-center align-items-center gap-1 mb-1">
            <!-- Nhãn hàng (A, B, C...) -->
            <span class="text-secondary me-2" style="width: 20px; text-align: right; font-size: 0.8rem;">
                <?= $rowLabel ?>
            </span>

            <?php for ($col = 1; $col <= $seatMap->seatsPerRow; $col++): ?>
                <?php
                    $seatCode = $rowLabel . $col;
                    $status   = $seatMap->getSeatStatus($seatCode);

                    // Map status → CSS class
                    [$btnClass, $disabled, $dataAttr] = match($status) {
                        'paid'    => ['btn-danger',          'disabled', ''],
                        'holding' => ['btn-warning',         'disabled', ''],
                        default   => ['btn-outline-success', '',         "data-seat=\"$seatCode\""],
                    };
                ?>
                <button
                    type="button"
                    class="btn btn-sm seat-btn <?= $btnClass ?>"
                    <?= $disabled ?>
                    <?= $dataAttr ?>
                    title="Ghế <?= $seatCode ?>"
                    style="width: 36px; height: 36px; padding: 0; font-size: 0.65rem;">
                    <?= $seatCode ?>
                </button>
            <?php endfor; ?>

            <!-- Nhãn hàng bên phải -->
            <span class="text-secondary ms-2" style="width: 20px; font-size: 0.8rem;">
                <?= $rowLabel ?>
            </span>
        </div>

    <?php endfor; ?>
</div>
```

---

## 6. Trang Booking — Sơ đồ ghế + Form đặt vé

```php
<?php
// views/booking/seat_map.php
?>
<div class="row g-4">

    <!-- Cột trái: Sơ đồ ghế -->
    <div class="col-lg-8">
        <div class="card bg-secondary border-0 shadow-lg">
            <div class="card-header bg-black border-bottom border-secondary">
                <h5 class="mb-0 text-warning">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Chọn ghế ngồi
                </h5>
                <small class="text-secondary">
                    <?= htmlspecialchars($seatMap->movieTitle) ?> —
                    <?= htmlspecialchars($seatMap->showDate) ?>
                    lúc <?= htmlspecialchars($seatMap->startTime) ?>
                    | <?= htmlspecialchars($seatMap->roomName) ?>
                </small>
            </div>
            <div class="card-body">
                <?php require VIEW_PATH . '/partials/seat_map.php'; ?>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thông tin đặt vé -->
    <div class="col-lg-4">
        <div class="card bg-secondary border-0 shadow-lg sticky-top" style="top: 20px;">
            <div class="card-header bg-black border-bottom border-secondary">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Thông tin đặt vé</h5>
            </div>
            <div class="card-body">

                <!-- Danh sách ghế đã chọn (cập nhật qua JS) -->
                <div class="mb-3">
                    <label class="text-secondary small">Ghế đã chọn:</label>
                    <div id="selected-seats-display" class="mt-1">
                        <span class="text-secondary fst-italic">Chưa chọn ghế</span>
                    </div>
                </div>

                <!-- Tổng tiền (JS cập nhật) -->
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Đơn giá:</span>
                    <span class="text-warning fw-bold"
                          data-price-per-seat="<?= $seatMap->pricePerSeat ?>">
                        <?= number_format($seatMap->pricePerSeat, 0, ',', '.') ?>₫
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Số lượng:</span>
                    <span id="seat-count" class="text-light fw-bold">0</span>
                </div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-light fw-bold">Tổng cộng:</span>
                    <span id="total-price" class="text-warning fw-bold fs-5">0₫</span>
                </div>

                <!-- Form giữ ghế — PHẢI có CSRF token -->
                <form id="booking-form" method="POST" action="/booking/hold">
                    <?= csrf_field() ?>   <!-- BẮT BUỘC -->
                    <input type="hidden" name="showtime_id"
                           value="<?= $seatMap->showtimeId ?>">
                    <!-- seat_codes[] được thêm bởi JS khi submit -->

                    <button type="submit"
                            id="btn-hold"
                            class="btn btn-warning w-100 fw-bold"
                            disabled>
                        <i class="bi bi-lock me-2"></i>Giữ ghế (10 phút)
                    </button>
                </form>

                <p class="text-secondary small text-center mt-2">
                    <i class="bi bi-shield-check me-1"></i>
                    Ghế được giữ trong 10 phút sau khi xác nhận
                </p>
            </div>
        </div>
    </div>
</div>
```

---

## 7. Trang Thanh Toán

```php
<?php
// views/payment/index.php
?>
<div class="row justify-content-center">
    <div class="col-lg-6">

        <!-- Countdown Timer -->
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-clock-history me-2 fs-5"></i>
            <div>
                Hoàn tất thanh toán trong:
                <strong id="countdown-timer" class="fs-5 ms-1">10:00</strong>
            </div>
        </div>

        <!-- Thông tin đặt vé -->
        <div class="card bg-secondary border-0 shadow mb-4">
            <div class="card-header bg-black">
                <h5 class="mb-0"><i class="bi bi-film me-2"></i>Chi tiết đặt vé</h5>
            </div>
            <div class="card-body">
                <p class="mb-1">
                    <strong class="text-warning"><?= htmlspecialchars($booking->movieTitle) ?></strong>
                </p>
                <p class="text-secondary mb-1">
                    <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($booking->showDate) ?>
                    lúc <?= htmlspecialchars($booking->startTime) ?>
                </p>
                <p class="text-secondary mb-2">
                    <i class="bi bi-door-open me-1"></i><?= htmlspecialchars($booking->roomName) ?>
                </p>
                <p class="mb-0">
                    <i class="bi bi-grid me-1"></i>Ghế:
                    <?php foreach ($booking->selectedSeats as $seat): ?>
                        <span class="badge bg-success me-1"><?= htmlspecialchars($seat) ?></span>
                    <?php endforeach; ?>
                </p>
            </div>
        </div>

        <!-- Mã giảm giá -->
        <div class="card bg-secondary border-0 shadow mb-4">
            <div class="card-body">
                <label class="form-label text-light">Mã giảm giá</label>
                <div class="input-group">
                    <input type="text" id="promo-input"
                           class="form-control bg-dark text-light border-secondary"
                           placeholder="Nhập mã giảm giá...">
                    <button class="btn btn-outline-warning" id="btn-apply-promo" type="button">
                        Áp dụng
                    </button>
                </div>
                <div id="promo-feedback" class="mt-2 small"></div>
            </div>
        </div>

        <!-- Tổng tiền -->
        <div class="card bg-secondary border-0 shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Tạm tính (<?= $booking->quantity ?> vé):</span>
                    <span><?= number_format($booking->subtotal, 0, ',', '.') ?>₫</span>
                </div>
                <div class="d-flex justify-content-between mb-2" id="discount-row"
                     style="<?= $booking->discount > 0 ? '' : 'display:none!important' ?>">
                    <span class="text-success">Giảm giá:</span>
                    <span class="text-success" id="discount-display">
                        -<?= number_format($booking->discount, 0, ',', '.') ?>₫
                    </span>
                </div>
                <hr class="border-secondary">
                <div class="d-flex justify-content-between">
                    <strong class="text-light">Tổng thanh toán:</strong>
                    <strong class="text-warning fs-5" id="total-display">
                        <?= number_format($booking->totalPrice, 0, ',', '.') ?>₫
                    </strong>
                </div>
            </div>
        </div>

        <!-- Form thanh toán — PHẢI có CSRF token -->
        <form method="POST" action="/payment/confirm">
            <?= csrf_field() ?>   <!-- BẮT BUỘC -->
            <input type="hidden" name="total_price"
                   id="final-total" value="<?= $booking->totalPrice ?>">
            <input type="hidden" name="promotion_code"
                   id="final-promo" value="">

            <div class="mb-4">
                <label class="form-label fw-bold text-light">Phương thức thanh toán</label>
                <div class="d-grid gap-2">
                    <div class="form-check bg-dark rounded p-3 border border-secondary">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="pay-vnpay" value="vnpay" required>
                        <label class="form-check-label text-light w-100" for="pay-vnpay">
                            <i class="bi bi-credit-card me-2 text-primary"></i>
                            <strong>VNPay</strong>
                            <small class="text-secondary d-block ms-4">Thẻ ATM, Internet Banking</small>
                        </label>
                    </div>
                    <div class="form-check bg-dark rounded p-3 border border-secondary">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="pay-momo" value="momo">
                        <label class="form-check-label text-light w-100" for="pay-momo">
                            <i class="bi bi-phone me-2 text-danger"></i>
                            <strong>MoMo</strong>
                        </label>
                    </div>
                    <div class="form-check bg-dark rounded p-3 border border-secondary">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="pay-cash" value="cash">
                        <label class="form-check-label text-light w-100" for="pay-cash">
                            <i class="bi bi-cash-coin me-2 text-success"></i>
                            <strong>Thanh toán tại quầy</strong>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 btn-lg fw-bold">
                <i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán
            </button>
        </form>
    </div>
</div>
```

---

## 8. JavaScript — Seat Selector & Countdown

```javascript
// public/assets/js/seat_selector.js

document.addEventListener('DOMContentLoaded', () => {

    // ── Seat Selection ────────────────────────────────────────
    const selectedSeats  = new Set();
    const MAX_SEATS      = 5;
    const pricePerSeat   = parseFloat(
        document.querySelector('[data-price-per-seat]')?.dataset.pricePer Seat ?? 0
    );

    document.querySelectorAll('.seat-btn[data-seat]').forEach(btn => {
        btn.addEventListener('click', () => {
            const seat = btn.dataset.seat;

            if (btn.classList.contains('btn-success')) {
                // Bỏ chọn
                btn.classList.replace('btn-success', 'btn-outline-success');
                selectedSeats.delete(seat);
            } else {
                // Kiểm tra giới hạn
                if (selectedSeats.size >= MAX_SEATS) {
                    alert(`Chỉ được chọn tối đa ${MAX_SEATS} ghế.`);
                    return;
                }
                // Chọn
                btn.classList.replace('btn-outline-success', 'btn-success');
                selectedSeats.add(seat);
            }

            updateSummary();
        });
    });

    function updateSummary() {
        const count = selectedSeats.size;
        const total = count * pricePerSeat;

        // Cập nhật UI
        document.getElementById('seat-count').textContent = count;
        document.getElementById('total-price').textContent =
            total.toLocaleString('vi-VN') + '₫';

        // Cập nhật danh sách ghế hiển thị
        const display = document.getElementById('selected-seats-display');
        if (count === 0) {
            display.innerHTML = '<span class="text-secondary fst-italic">Chưa chọn ghế</span>';
        } else {
            display.innerHTML = [...selectedSeats]
                .map(s => `<span class="badge bg-success me-1">${s}</span>`)
                .join('');
        }

        // Enable/disable nút Giữ ghế
        const btn = document.getElementById('btn-hold');
        if (btn) btn.disabled = count === 0;
    }

    // Inject seat_codes vào form trước khi submit
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', (e) => {
            if (selectedSeats.size === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất 1 ghế.');
                return;
            }
            // Xóa input cũ (nếu có)
            bookingForm.querySelectorAll('input[name="seat_codes[]"]').forEach(el => el.remove());

            // Thêm input cho từng ghế
            selectedSeats.forEach(seat => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'seat_codes[]';
                input.value = seat;
                bookingForm.appendChild(input);
            });
        });
    }

    // ── Countdown Timer ───────────────────────────────────────
    const timerEl = document.getElementById('countdown-timer');
    if (timerEl) {
        const expiryTime = new Date(timerEl.dataset.expiry ?? '').getTime();

        const interval = setInterval(() => {
            const remaining = Math.max(0, expiryTime - Date.now());
            const m = Math.floor(remaining / 60000);
            const s = Math.floor((remaining % 60000) / 1000);
            timerEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;

            if (remaining <= 30000) timerEl.classList.add('text-danger');
            if (remaining === 0) {
                clearInterval(interval);
                alert('Phiên giữ chỗ đã hết hạn. Vui lòng chọn lại ghế.');
                window.location.href = '/movies';
            }
        }, 1000);
    }

    // ── Apply Promo Code (AJAX) ───────────────────────────────
    const btnPromo = document.getElementById('btn-apply-promo');
    if (btnPromo) {
        btnPromo.addEventListener('click', async () => {
            const code     = document.getElementById('promo-input').value.trim();
            const subtotal = parseFloat(document.getElementById('final-total').value);
            const feedback = document.getElementById('promo-feedback');

            if (!code) return;

            const csrf = document.querySelector('input[name="_csrf_token"]').value;

            try {
                const res = await fetch('/booking/apply-promo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `code=${encodeURIComponent(code)}&subtotal=${subtotal}&_csrf_token=${csrf}`
                });
                const data = await res.json();

                if (data.success) {
                    feedback.innerHTML = `<span class="text-success">✓ Giảm ${data.discount.toLocaleString('vi-VN')}₫</span>`;
                    document.getElementById('total-display').textContent =
                        data.totalPrice.toLocaleString('vi-VN') + '₫';
                    document.getElementById('final-total').value = data.totalPrice;
                    document.getElementById('final-promo').value = code;
                    document.getElementById('discount-row').style.display = '';
                    document.getElementById('discount-display').textContent =
                        '-' + data.discount.toLocaleString('vi-VN') + '₫';
                } else {
                    feedback.innerHTML = `<span class="text-danger">✗ ${data.error}</span>`;
                }
            } catch {
                feedback.innerHTML = '<span class="text-danger">Lỗi kết nối. Thử lại.</span>';
            }
        });
    }
});
```

---

## 9. CSS Tùy chỉnh

```css
/* public/assets/css/app.css */

/* Movie card hover */
.movie-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.movie-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(255, 193, 7, 0.25) !important;
}

/* Seat buttons */
.seat-btn {
    transition: transform 0.1s ease, background-color 0.15s ease;
    border-radius: 6px !important;
    font-weight: 600;
}
.seat-btn:not(:disabled):hover {
    transform: scale(1.1);
}
.seat-btn.btn-success {
    box-shadow: 0 0 8px rgba(25, 135, 84, 0.7);
}

/* Scrollable seat map on small screens */
.seat-map-container {
    max-width: 100%;
    padding-bottom: 1rem;
}

/* Screen glow */
.screen-bar {
    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
}
```

---

## 10. Quy ước View — Checklist cho AI

| Quy tắc | Ví dụ |
|---------|-------|
| Luôn escape output | `<?= htmlspecialchars($var) ?>` |
| Mọi form POST có CSRF | `<?= csrf_field() ?>` |
| Không SQL trong View | ❌ `$pdo->query(...)` trong `.php` view |
| Không gọi Service trong View | ❌ `$movieService->get()` trong View |
| Dùng `$content` cho layout | Layout inject `<?= $content ?>` |
| Dark theme Bootstrap | `data-bs-theme="dark"` trên `<html>` |
| Ghế trống | `btn-outline-success` |
| Ghế đang chọn (JS) | `btn-success` |
| Ghế đang giữ | `btn-warning disabled` |
| Ghế đã bán | `btn-danger disabled` |
