# Frontend Skills — Hệ Thống Đặt Vé Xem Phim CinemaX

> Tài liệu tổng hợp các kỹ thuật frontend nâng cao
> dành riêng cho giao diện website đặt vé xem phim.
> Mỗi mục đều kèm code mẫu sẵn sàng tích hợp.

---

## 1. Màn Chiếu Phát Sáng

Tạo thanh màn chiếu cong ở trên cùng sơ đồ ghế,
phát ra ánh sáng mờ xuống phía dưới
giống như rạp phim thật.

**CSS:**

```css
.cinema-screen {
    width: 80%;
    height: 8px;
    margin: 0 auto 3rem auto;
    background: linear-gradient(
        to right,
        transparent,
        #ffffff 20%,
        #ffffff 80%,
        transparent
    );
    border-radius: 0 0 50% 50% / 0 0 100% 100%;
    box-shadow:
        0 4px 20px rgba(255, 255, 255, 0.6),
        0 8px 40px rgba(255, 193, 7, 0.25),
        0 16px 80px rgba(255, 193, 7, 0.1);
}
```

**HTML:**

```html
<div class="text-center">
    <div class="cinema-screen"></div>
    <small class="text-secondary"
           style="letter-spacing: 6px;">
        MAN HINH
    </small>
</div>
```

---

## 2. Hiệu Ứng Nảy Ghế Khi Chọn

Khi người dùng click chọn ghế,
ghế sẽ phóng to rồi thu nhỏ lại tạo cảm giác nảy.
Kết hợp với viền phát sáng xanh lục.

**CSS:**

```css
@keyframes seat-pop {
    0%   { transform: scale(1);    }
    40%  { transform: scale(1.3);  }
    70%  { transform: scale(0.95); }
    100% { transform: scale(1);    }
}

.seat-btn.selected {
    animation: seat-pop 0.3s ease-out;
    background-color: #198754;
    border-color: #198754;
    color: #fff;
    box-shadow: 0 0 12px rgba(25, 135, 84, 0.7);
}
```

**JavaScript:**

```javascript
seatBtn.addEventListener('click', function() {
    this.classList.toggle('selected');
});
```

---

## 3. Poster Phim Nghiêng 3D Theo Chuột

Khi di chuột qua thẻ poster phim,
poster sẽ nghiêng nhẹ theo hướng chuột
tạo chiều sâu 3D.

**CSS:**

```css
.movie-card-3d {
    perspective: 800px;
}

.movie-card-3d .card-inner {
    transition: transform 0.15s ease-out;
    transform-style: preserve-3d;
}

.movie-card-3d .card-inner:hover {
    box-shadow:
        0 20px 40px rgba(255, 193, 7, 0.2);
}
```

**JavaScript:**

```javascript
document.querySelectorAll('.movie-card-3d').forEach(card => {
    const inner = card.querySelector('.card-inner');

    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = (centerY - y) / 12;
        const rotateY = (x - centerX) / 12;

        inner.style.transform =
            'rotateX(' + rotateX + 'deg) '
          + 'rotateY(' + rotateY + 'deg)';
    });

    card.addEventListener('mouseleave', () => {
        inner.style.transform = 'rotateX(0) rotateY(0)';
    });
});
```

---

## 4. Vé Xem Phim Lật 3D

Sau khi đặt vé thành công,
hiển thị tấm vé ảo có thể lật
để xem mã QR ở mặt sau.

**HTML:**

```html
<div class="ticket-flip">
    <div class="ticket-inner">

        <!-- Mat truoc -->
        <div class="ticket-front">
            <h5 class="text-warning fw-bold">
                CINEMAX TICKET
            </h5>
            <p class="mb-1">Phim: Avengers</p>
            <p class="mb-1">Ghe: A5, A6</p>
            <p class="mb-0 text-secondary small">
                20:00 - 15/07/2026
            </p>
        </div>

        <!-- Mat sau -->
        <div class="ticket-back">
            <p class="fw-bold mb-2">MA VE</p>
            <div class="bg-white p-3 rounded d-inline-block">
                <div style="width:100px; height:100px;
                            background:#333;
                            border-radius:8px;">
                </div>
            </div>
            <p class="font-monospace small mt-2">
                CX-938210
            </p>
        </div>

    </div>
</div>
```

**CSS:**

```css
.ticket-flip {
    width: 280px;
    height: 180px;
    perspective: 1000px;
    cursor: pointer;
    margin: 0 auto;
}

.ticket-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.6s ease;
    transform-style: preserve-3d;
}

.ticket-flip:hover .ticket-inner {
    transform: rotateY(180deg);
}

.ticket-front,
.ticket-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}

.ticket-front {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border: 1px solid #ffc107;
    color: #fff;
}

.ticket-back {
    background: linear-gradient(135deg, #16213e, #0f0f1b);
    border: 1px solid #ffc107;
    color: #fff;
    transform: rotateY(180deg);
}
```

---

## 5. Dong Ho Dem Nguoc Giu Ghe

Khi người dùng giữ ghế,
đồng hồ đếm ngược 10 phút sẽ hiển thị.
Khi còn dưới 60 giây, đồng hồ nhấp nháy đỏ.

**CSS:**

```css
@keyframes timer-pulse {
    0%   { opacity: 1; transform: scale(1);    }
    50%  { opacity: 0.5; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1);    }
}

.timer-urgent {
    color: #dc3545 !important;
    animation: timer-pulse 0.8s ease-in-out infinite;
}

.timer-container {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.timer-value {
    font-family: 'Courier New', monospace;
    font-size: 2rem;
    font-weight: 700;
    color: #ffc107;
    letter-spacing: 2px;
}
```

**JavaScript:**

```javascript
function startCountdown(seconds, element) {
    var remaining = seconds;

    var interval = setInterval(function() {
        if (remaining <= 0) {
            clearInterval(interval);
            alert('Het han giu ghe!');
            window.location.href = '/movies';
            return;
        }

        remaining--;

        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        var mm = String(m).padStart(2, '0');
        var ss = String(s).padStart(2, '0');
        element.textContent = mm + ':' + ss;

        if (remaining <= 60) {
            element.classList.add('timer-urgent');
        }
    }, 1000);
}
```

---

## 6. Thanh Checkout Ghim Day Man Hinh

Trên thiết bị di động,
ghim thanh thông tin đặt vé ở đáy màn hình
để người dùng luôn thấy số ghế đã chọn
và nút thanh toán mà không cần cuộn trang.

**CSS:**

```css
.sticky-checkout {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1050;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 193, 7, 0.3);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5);
}

.sticky-checkout .checkout-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 600px;
    margin: 0 auto;
}

.sticky-checkout .total-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffc107;
}

@media (min-width: 992px) {
    .sticky-checkout {
        display: none;
    }
}
```

**HTML:**

```html
<div class="sticky-checkout d-lg-none">
    <div class="checkout-info">
        <div>
            <span class="text-secondary small">
                2 ghe da chon
            </span>
            <span class="total-price d-block">
                180.000d
            </span>
        </div>
        <button class="btn btn-warning fw-bold px-4">
            Giu Ghe
        </button>
    </div>
</div>
```

---

## 7. Lich Chieu Truot Ngang

Lịch 7 ngày tới hiển thị dạng nút
cuộn trượt ngang trên mobile.
Ngày được chọn sáng lên màu vàng.

**CSS:**

```css
.date-slider {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: none;
}

.date-slider::-webkit-scrollbar {
    display: none;
}

.date-item {
    flex-shrink: 0;
    min-width: 80px;
    padding: 0.75rem 0.5rem;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #444;
    background: transparent;
    color: #ccc;
    cursor: pointer;
    transition: all 0.2s ease;
}

.date-item:hover {
    border-color: #ffc107;
    color: #fff;
}

.date-item.active {
    background: #ffc107;
    color: #000;
    font-weight: 700;
    border-color: #ffc107;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.date-item .day-name {
    font-size: 0.7rem;
    text-transform: uppercase;
    opacity: 0.7;
}

.date-item .day-number {
    font-size: 1.25rem;
    font-weight: 700;
}
```

**HTML:**

```html
<div class="date-slider">
    <div class="date-item active">
        <div class="day-name">Hom nay</div>
        <div class="day-number">15</div>
        <div class="day-name">Th7</div>
    </div>
    <div class="date-item">
        <div class="day-name">CN</div>
        <div class="day-number">16</div>
        <div class="day-name">Th7</div>
    </div>
    <div class="date-item">
        <div class="day-name">T2</div>
        <div class="day-number">17</div>
        <div class="day-name">Th7</div>
    </div>
    <!-- Tiep tuc cho 7 ngay -->
</div>
```

---

## 8. The Suat Chieu Dep

Hiển thị từng suất chiếu trong một thẻ riêng
với giờ chiếu nổi bật, tên phòng, giá vé
và số ghế trống còn lại.

**CSS:**

```css
.showtime-card {
    background: linear-gradient(135deg, #0f0f1b, #1a1a2e);
    border: 1px solid #2d2d44;
    border-radius: 12px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.showtime-card:hover {
    border-color: #ffc107;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(255, 193, 7, 0.15);
}

.showtime-card .time {
    font-size: 1.75rem;
    font-weight: 800;
    color: #fff;
    font-family: 'Inter', sans-serif;
}

.showtime-card .price {
    color: #ffc107;
    font-weight: 600;
}

.showtime-card .room-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    color: #ffc107;
}

.showtime-card .seats-left {
    font-size: 0.8rem;
    color: #6c757d;
}

.showtime-card .seats-left.low {
    color: #dc3545;
    font-weight: 600;
}
```

**HTML:**

```html
<div class="showtime-card">
    <div class="d-flex justify-content-between
                align-items-start mb-2">
        <span class="room-badge">Phong IMAX</span>
        <span class="seats-left low">
            Con 12 ghe
        </span>
    </div>
    <div class="time">20:30</div>
    <div class="price">90.000d</div>
</div>
```

---

## 9. Phan Biet Ghe Theo Loai

Hệ thống ghế nên phân biệt rõ
3 loại ghế bằng màu sắc khác nhau:
Standard, VIP và Sweetbox (ghế đôi).

**CSS:**

```css
/* Ghe Standard — Xanh luc */
.seat-standard {
    border: 2px solid #198754;
    color: #198754;
    background: transparent;
}
.seat-standard.selected {
    background: #198754;
    color: #fff;
    box-shadow: 0 0 8px rgba(25, 135, 84, 0.6);
}

/* Ghe VIP — Cam vang */
.seat-vip {
    border: 2px solid #fd7e14;
    color: #fd7e14;
    background: transparent;
}
.seat-vip.selected {
    background: #fd7e14;
    color: #fff;
    box-shadow: 0 0 8px rgba(253, 126, 20, 0.6);
}

/* Ghe Doi Sweetbox — Tim hong */
.seat-sweetbox {
    border: 2px solid #d63384;
    color: #d63384;
    background: transparent;
    width: 80px;
    border-radius: 20px;
}
.seat-sweetbox.selected {
    background: #d63384;
    color: #fff;
    box-shadow: 0 0 8px rgba(214, 51, 132, 0.6);
}

/* Ghe da ban — Do mo */
.seat-sold {
    background: #dc3545;
    color: #fff;
    opacity: 0.5;
    cursor: not-allowed;
}

/* Ghe dang giu — Vang mo */
.seat-holding {
    background: #ffc107;
    color: #000;
    opacity: 0.6;
    cursor: not-allowed;
}
```

**Chu Thich Mau:**

```html
<div class="d-flex flex-wrap gap-3
            justify-content-center my-4">
    <div class="d-flex align-items-center gap-2">
        <div style="width:20px; height:20px;
                    border:2px solid #198754;
                    border-radius:4px;">
        </div>
        <small class="text-light">Standard</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:20px; height:20px;
                    border:2px solid #fd7e14;
                    border-radius:4px;">
        </div>
        <small class="text-light">VIP</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:36px; height:20px;
                    border:2px solid #d63384;
                    border-radius:10px;">
        </div>
        <small class="text-light">Sweetbox</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div style="width:20px; height:20px;
                    background:#dc3545;
                    border-radius:4px;
                    opacity:0.5;">
        </div>
        <small class="text-light">Da ban</small>
    </div>
</div>
```

---

## 10. Hero Banner Trang Chu

Banner lớn ở trang chủ
với gradient tối, chữ lớn sáng
và hiệu ứng particles hoặc bokeh mờ.

**CSS:**

```css
.hero-banner {
    position: relative;
    overflow: hidden;
    padding: 5rem 2rem;
    text-align: center;
    border-radius: 16px;
    background: linear-gradient(
        135deg,
        #0a0a14 0%,
        #111128 40%,
        #1a1a3e 70%,
        #0f0f1b 100%
    );
    border: 1px solid #222244;
}

.hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(
        circle at 30% 40%,
        rgba(255, 193, 7, 0.08) 0%,
        transparent 50%
    );
    pointer-events: none;
}

.hero-banner h1 {
    font-size: 3.5rem;
    font-weight: 800;
    color: #ffc107;
    text-shadow: 0 4px 20px rgba(255, 193, 7, 0.3);
    position: relative;
    z-index: 1;
}

.hero-banner p {
    color: #ced4da;
    font-size: 1.15rem;
    max-width: 500px;
    margin: 1rem auto;
    position: relative;
    z-index: 1;
}
```

---

## Tom Tat Nhanh

| STT | Ky Thuat                     | Muc Dich                       |
|-----|------------------------------|--------------------------------|
| 1   | Man chieu phat sang          | Tao cam giac rap phim that     |
| 2   | Ghe nay khi chon             | Phan hoi truc quan cho user    |
| 3   | Poster nghieng 3D            | Tang chieu sau thi giac        |
| 4   | Ve phim lat 3D               | Trai nghiem sau thanh toan     |
| 5   | Dong ho dem nguoc            | Thuc day hoan tat thanh toan   |
| 6   | Checkout ghim day            | Toi uu mobile UX               |
| 7   | Lich chieu truot ngang       | Tiet kiem khong gian mobile    |
| 8   | The suat chieu dep           | Hien thi gio chieu noi bat     |
| 9   | Phan biet ghe theo loai      | Kich thich mua ghe cao cap     |
| 10  | Hero banner trang chu        | An tuong ngay lan dau truy cap |
