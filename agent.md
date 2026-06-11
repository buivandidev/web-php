# 🧭 Hướng Dẫn Dành Cho AI Agent Tiếp Theo (Next Agent Guide)

Chào bạn, đây là tài liệu tóm tắt nhanh toàn bộ dự án **CinemaX** để giúp bạn nhanh chóng nắm bắt cấu trúc và tiếp tục phát triển hệ thống mà không làm ảnh hưởng đến thiết kế gốc.

---

## 1. Tổng Quan Dự Án
* **Tên dự án**: CinemaX — Hệ thống đặt vé xem phim trực tuyến.
* **Mô hình kiến trúc**: **MVC tự phát triển (Custom PHP MVC)**, hoàn toàn **không dùng framework** (không Laravel, không Symfony, không CodeIgniter).
* **Nguyên tắc thiết kế**: **Thin Controller — Fat Model**. 
  - Controller chỉ điều hướng (nhận input, gọi Service, trả View).
  - Business Logic tập trung hoàn toàn ở Service Layer.
  - Data Access được cô lập hoàn toàn ở Repository Layer.
  - View chỉ hiển thị dữ liệu đã xử lý, không truy vấn DB hay gọi Service trực tiếp.

---

## 2. Tech Stack & Cấu Hình
* **Ngôn ngữ**: PHP 8.1+ (sử dụng nghiêm ngặt Namespaces & Type Hinting).
* **Cơ sở dữ liệu**: PostgreSQL 14+ (Sử dụng PDO driver).
* **CSS Framework**: Bootstrap 5.3.3 (Kèm chế độ tối `data-bs-theme="dark"`).
* **Autoloading**: Khai báo PSR-4 trong `composer.json` dưới namespace `App\`. Tuy nhiên, dự án sử dụng một **custom autoloader** linh hoạt trong [public/index.php](file:///Users/buidi/Documents/mcvphpweb/public/index.php) để tự động nạp các class mà không cần chạy `composer dump-autoload` trong môi trường thiếu Composer.

---

## 3. Bản Đồ Cấu Trúc Thư Mục
Dự án được cấu trúc như sau:
```
/Users/buidi/Documents/mcvphpweb/
├── app/
│   ├── Controllers/             # BaseController và các Controller điều hướng
│   ├── Core/                    # Hạ tầng: PDO Database, Router, Container (DI), Session, Csrf
│   │   ├── Exceptions/          # Các lớp ngoại lệ nghiệp vụ (Business, Concurrency, SeatUnavailable)
│   │   └── ValueObjects/        # Các DTO kết quả (HoldResult, PaymentResult...)
│   ├── Jobs/                    # Background Job (HoldExpiryJob quét nhả ghế hết hạn)
│   ├── Models/
│   │   ├── Domain/              # Thực thể ánh xạ bảng DB (User, Movie, Room, Showtime, Ticket, Promotion)
│   │   └── Repository/          # DAL Repositories (Interfaces & Implementations)
│   └── ViewModels/              # ViewModels đóng gói dữ liệu truyền xuống View
├── config/                      # app.php (DI Binding), database.php, routes.php
├── migrations/                  # Các file SQL tạo bảng & Seed và runner run.php
├── public/                      # Document Root duy nhất (index.php, CSS, JS, uploads/)
└── views/                       # Chứa các file giao diện (.php) và layout chung
```

---

## 4. Các Nguyên Tắc & Quy Ước Bắt Buộc

### 🛡️ Bảo Mật & CSRF
* Mọi HTTP POST request phải đi qua phương thức `$this->validateCsrf()` của `BaseController`.
* Trong View, sử dụng helper `<?= csrf_field() ?>` bên trong form để tự động render token ẩn.

### 🔒 Optimistic Locking (Khóa Lạc Quan)
* Khi thanh toán vé (`Ticket`), hệ thống sử dụng cơ chế Optimistic Locking qua cột `version` để tránh race condition (2 người mua cùng 1 ghế cùng 1 lúc).
* Câu lệnh update trạng thái vé phải so khớp `version` cũ:
  ```sql
  UPDATE tickets SET status = 'paid', version = version + 1 WHERE id = :id AND version = :version
  ```
  Nếu số dòng bị ảnh hưởng (`rowCount()`) trả về `0`, nghĩa là đã xảy ra tranh chấp và phải ném ra `ConcurrencyException`.

### 📦 ViewModels & DTOs
* **Tuyệt đối không truyền trực tiếp Domain Model xuống View** để tránh lộ các dữ liệu nhạy cảm (như mật khẩu hoặc dữ liệu chưa xử lý).
* Luôn đóng gói dữ liệu thành các class trong `app/ViewModels/` trước khi render.
* Mọi đầu ra trong View hiển thị thông tin động phải được bọc qua hàm `htmlspecialchars()` để chống lỗ hổng XSS.

### 💳 Strategy Pattern cho Thanh Toán
* `PaymentService` đóng vai trò điều phối. Việc xử lý thanh toán thực tế được thực hiện qua các lớp chiến lược cụ thể triển khai `IPaymentStrategy` (bao gồm `VNPayStrategy`, `MoMoStrategy`, `CashStrategy`). Để tích hợp thêm cổng thanh toán mới, chỉ cần viết thêm lớp strategy mà không thay đổi cốt lõi hệ thống.

---

## 5. Các Lệnh Vận Hành Nhanh

### Chạy Migration Thiết Lập Database
Dự án đã tích hợp sẵn một CLI runner để thiết lập tự động cấu trúc bảng và dữ liệu mẫu:
```bash
php migrations/run.php
```
*Lưu ý: Cấu hình DB nằm tại file [.env](file:///Users/buidi/Documents/mcvphpweb/.env) ở thư mục gốc.*

### Khởi Động Built-in Web Server
```bash
php -S localhost:8000 -t public
```

### Chạy Background Job Giải Phóng Ghế Hết Hạn
Để tự động hủy các ghế giữ chỗ quá 10 phút chưa thanh toán, hãy lập lịch cron hoặc chạy thủ công lệnh:
```bash
php app/Jobs/run_job.php HoldExpiryJob
```

Chúc bạn phát triển dự án thuận lợi! 🚀
