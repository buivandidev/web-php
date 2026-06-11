# 04 — Business Logic & Services

> **Dành cho AI Agent:** Đây là file QUAN TRỌNG NHẤT. Toàn bộ logic nghiệp vụ phải nằm ở đây — không nằm trong Controller, không nằm trong View.

---

## 1. Interface tổng quan

```php
<?php
// app/Models/Services/Interfaces/ITicketService.php

interface ITicketService
{
    public function holdSeats(int $userId, int $showtimeId, array $seatCodes): HoldResult;
    public function confirmPayment(array $ticketIds, int $userId, string $paymentMethod): bool;
    public function releaseExpiredHolds(): int;     // Trả về số vé đã hủy
    public function getUserTickets(int $userId): array;
}

// app/Models/Services/Interfaces/IMovieService.php
interface IMovieService
{
    public function getNowShowing(): array;
    public function getComingSoon(): array;
    public function getDetail(int $movieId): Movie;
    public function getShowtimesByDate(int $movieId, string $date): array;
}

// app/Models/Services/Interfaces/IPromotionService.php
interface IPromotionService
{
    public function applyPromotion(string $code, float $subtotal): PromotionResult;
    public function validateCode(string $code): bool;
}

// app/Models/Services/Interfaces/IPaymentService.php
interface IPaymentService
{
    public function process(string $method, PaymentRequest $request): PaymentResult;
}
```

---

## 2. TicketService — Nghiệp vụ cốt lõi

```php
<?php
// app/Models/Services/Implementations/TicketService.php

class TicketService implements ITicketService
{
    public function __construct(
        private readonly ITicketRepository $ticketRepo,
        private readonly Database          $db
    ) {}

    // =========================================================
    // BƯỚC 1: Giữ chỗ (Soft Hold)
    // =========================================================
    public function holdSeats(int $userId, int $showtimeId, array $seatCodes): HoldResult
    {
        // --- Validate số lượng vé ---
        $count = count($seatCodes);
        if ($count < ValidationRules::MIN_TICKETS_PER_BOOKING) {
            throw new BusinessException('Vui lòng chọn ít nhất 1 ghế.');
        }
        if ($count > ValidationRules::MAX_TICKETS_PER_BOOKING) {
            throw new BusinessException(
                'Chỉ được đặt tối đa ' . ValidationRules::MAX_TICKETS_PER_BOOKING . ' vé mỗi lần.'
            );
        }

        // --- Kiểm tra ghế có sẵn không ---
        $takenSeats = $this->ticketRepo->getActiveSeats($showtimeId, $seatCodes);
        if (!empty($takenSeats)) {
            throw new SeatUnavailableException($takenSeats);
        }

        // --- Tạo bản ghi Holding trong transaction ---
        $pdo = $this->db::getInstance();
        $pdo->beginTransaction();

        try {
            $expiryTime = date('Y-m-d H:i:s',
                strtotime('+' . ValidationRules::HOLD_DURATION_MINUTES . ' minutes')
            );

            $ticketIds = [];
            foreach ($seatCodes as $seat) {
                $ticketIds[] = $this->ticketRepo->create([
                    'showtime_id'      => $showtimeId,
                    'user_id'          => $userId,
                    'seat_code'        => $seat,
                    'status'           => 'holding',
                    'hold_expiry_time' => $expiryTime,
                    'total_price'      => 0,  // Tính sau khi áp mã giảm giá
                    'version'          => 0,
                ]);
            }

            $pdo->commit();

            return new HoldResult(
                ticketIds:  $ticketIds,
                expiryTime: $expiryTime
            );

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // =========================================================
    // BƯỚC 2: Xác nhận thanh toán (Optimistic Locking)
    // =========================================================
    public function confirmPayment(array $ticketIds, int $userId, string $paymentMethod): bool
    {
        $pdo = $this->db::getInstance();
        $pdo->beginTransaction();

        try {
            foreach ($ticketIds as $ticketId) {
                $ticket = $this->ticketRepo->findById($ticketId);

                // Kiểm tra vé có thuộc về user hiện tại không
                if ($ticket->userId !== $userId) {
                    throw new BusinessException('Không có quyền xác nhận vé này.');
                }

                // Kiểm tra vé có hết hạn giữ chỗ chưa
                if ($ticket->isExpired()) {
                    throw new BusinessException(
                        'Phiên giữ chỗ đã hết hạn. Vui lòng chọn ghế lại.'
                    );
                }

                // --- Optimistic Locking ---
                $rowsAffected = $this->ticketRepo->updateStatusWithVersion(
                    id:              $ticketId,
                    newStatus:       'paid',
                    expectedVersion: $ticket->version
                );

                if ($rowsAffected === 0) {
                    throw new ConcurrencyException(
                        'Ghế ' . $ticket->seatCode . ' vừa được người khác đặt. Vui lòng chọn ghế khác.'
                    );
                }
            }

            $pdo->commit();
            return true;

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // =========================================================
    // BƯỚC 3: Background Job — Nhả ghế hết hạn
    // =========================================================
    public function releaseExpiredHolds(): int
    {
        return $this->ticketRepo->cancelExpiredHolds();
    }

    public function getUserTickets(int $userId): array
    {
        return $this->ticketRepo->findByUserId($userId);
    }
}
```

---

## 3. TicketRepository — Câu SQL Optimistic Locking

```php
<?php
// app/Models/Repository/Implementations/TicketRepository.php

class TicketRepository implements ITicketRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * Lấy danh sách ghế đang bị hold/paid trong một suất chiếu
     * @return string[] Danh sách seat_code đã bị chiếm
     */
    public function getActiveSeats(int $showtimeId, array $seatCodes): array
    {
        $placeholders = implode(',', array_fill(0, count($seatCodes), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT seat_code FROM tickets
             WHERE showtime_id = ?
               AND seat_code IN ($placeholders)
               AND status IN ('holding', 'paid')"
        );
        $stmt->execute([$showtimeId, ...$seatCodes]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Câu UPDATE với Optimistic Locking
     * Trả về số row bị ảnh hưởng:
     * - 1 → thành công
     * - 0 → version đã thay đổi (người khác đã thắng race condition)
     */
    public function updateStatusWithVersion(
        int    $id,
        string $newStatus,
        int    $expectedVersion
    ): int {
        $stmt = $this->pdo->prepare(
            "UPDATE tickets
             SET status  = :status,
                 version = version + 1
             WHERE id      = :id
               AND version = :version"
        );
        $stmt->execute([
            ':status'  => $newStatus,
            ':id'      => $id,
            ':version' => $expectedVersion,
        ]);
        return $stmt->rowCount();  // 0 = race condition thua
    }

    /**
     * Hủy tất cả vé hết hạn giữ chỗ (dùng cho Background Job)
     */
    public function cancelExpiredHolds(): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tickets
             SET status = 'cancelled'
             WHERE status = 'holding'
               AND hold_expiry_time < NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tickets
             (showtime_id, user_id, seat_code, status, hold_expiry_time, total_price, version)
             VALUES (:showtime_id, :user_id, :seat_code, :status, :hold_expiry_time, :total_price, :version)
             RETURNING id"
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?Ticket
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tickets WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Ticket::fromArray($row) : null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, s.show_date, s.start_time, m.title AS movie_title
             FROM tickets t
             JOIN showtimes s ON s.id = t.showtime_id
             JOIN movies m ON m.id = s.movie_id
             WHERE t.user_id = ?
             ORDER BY t.booked_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
```

---

## 4. PromotionService — Tính giảm giá

```php
<?php
// app/Models/Services/Implementations/PromotionService.php

class PromotionService implements IPromotionService
{
    public function __construct(
        private readonly IPromotionRepository $promoRepo
    ) {}

    public function applyPromotion(string $code, float $subtotal): PromotionResult
    {
        $promo = $this->promoRepo->findByCode(strtoupper(trim($code)));

        if (!$promo) {
            throw new BusinessException('Mã giảm giá không tồn tại.');
        }
        if (!$promo->isActive) {
            throw new BusinessException('Mã giảm giá đã bị vô hiệu hóa.');
        }
        if ($promo->expiresAt && strtotime($promo->expiresAt) < time()) {
            throw new BusinessException('Mã giảm giá đã hết hạn.');
        }
        if ($promo->maxUses !== null && $promo->usedCount >= $promo->maxUses) {
            throw new BusinessException('Mã giảm giá đã hết lượt sử dụng.');
        }

        // Tính tiền giảm
        $discount = match($promo->discountType) {
            'percent' => $subtotal * ($promo->discountValue / 100),
            'fixed'   => min($promo->discountValue, $subtotal), // không giảm âm
            default   => throw new BusinessException('Loại giảm giá không hợp lệ.'),
        };

        return new PromotionResult(
            code:       $code,
            discount:   $discount,
            totalPrice: max(0, $subtotal - $discount)
        );
    }

    public function validateCode(string $code): bool
    {
        try {
            $this->applyPromotion($code, 1);  // subtotal = 1 để test code hợp lệ
            return true;
        } catch (BusinessException) {
            return false;
        }
    }
}
```

---

## 5. PaymentService — Strategy Pattern

```php
<?php
// Strategy Interface
interface IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult;
}

// VNPay Strategy
class VNPayStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        // TODO: Tích hợp VNPay API
        // 1. Tạo VNPAY URL redirect
        // 2. Xử lý callback IPN
        return new PaymentResult(success: true, transactionId: 'VNP_' . uniqid());
    }
}

// MoMo Strategy
class MoMoStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        // TODO: Tích hợp MoMo API
        return new PaymentResult(success: true, transactionId: 'MOMO_' . uniqid());
    }
}

// Cash Strategy (thanh toán tại quầy)
class CashStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        return new PaymentResult(success: true, transactionId: 'CASH_' . uniqid());
    }
}

// PaymentService — chọn Strategy theo method
class PaymentService implements IPaymentService
{
    private array $strategies = [];

    public function __construct()
    {
        $this->strategies['vnpay']  = new VNPayStrategy();
        $this->strategies['momo']   = new MoMoStrategy();
        $this->strategies['cash']   = new CashStrategy();
    }

    public function process(string $method, PaymentRequest $request): PaymentResult
    {
        if (!isset($this->strategies[$method])) {
            throw new BusinessException("Phương thức thanh toán '$method' không được hỗ trợ.");
        }
        return $this->strategies[$method]->process($request);
    }
}
```

---

## 6. Background Job — Tự động nhả ghế hết hạn

```php
<?php
// app/Jobs/HoldExpiryJob.php

/**
 * Chạy qua cron job: * * * * * php /path/to/cinema/app/Jobs/run_job.php HoldExpiryJob
 * Hoặc: crontab -e → thêm: */1 * * * * php /var/www/cinema/artisan hold:expire
 */
class HoldExpiryJob
{
    public function __construct(
        private readonly ITicketService $ticketService
    ) {}

    public function run(): void
    {
        $released = $this->ticketService->releaseExpiredHolds();
        echo date('Y-m-d H:i:s') . " - Released {$released} expired holds.\n";
    }
}
```

**Cron entry (`crontab -e`):**
```bash
# Chạy mỗi phút để quét vé hết hạn giữ chỗ
* * * * * /usr/bin/php /var/www/cinema/app/Jobs/run_job.php >> /var/www/cinema/logs/hold_expiry.log 2>&1
```

---

## 7. Value Objects — Các lớp kết quả

```php
<?php
// app/Core/ValueObjects/HoldResult.php
class HoldResult
{
    public function __construct(
        public readonly array  $ticketIds,
        public readonly string $expiryTime
    ) {}

    public function getRemainingSeconds(): int
    {
        return max(0, strtotime($this->expiryTime) - time());
    }
}

// app/Core/ValueObjects/PromotionResult.php
class PromotionResult
{
    public function __construct(
        public readonly string $code,
        public readonly float  $discount,
        public readonly float  $totalPrice
    ) {}
}

// app/Core/ValueObjects/PaymentRequest.php
class PaymentRequest
{
    public function __construct(
        public readonly float  $amount,
        public readonly string $orderDescription,
        public readonly array  $ticketIds,
        public readonly int    $userId
    ) {}
}

// app/Core/ValueObjects/PaymentResult.php
class PaymentResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $transactionId,
        public readonly string $message = ''
    ) {}
}
```

---

## 8. Tóm tắt luồng đặt vé hoàn chỉnh

```
[1] User chọn ghế → POST /booking/hold
        │
        ▼
[2] BookingController::holdSeats()
    → Validate CSRF token
    → Gọi TicketService::holdSeats()
        │
        ├─ Check count ≤ 5
        ├─ Check ghế không bị 'holding'/'paid'
        └─ INSERT tickets (status='holding', expiry=+10min)
        │
        ▼
[3] Redirect → trang thanh toán (countdown 10 phút)
        │
        ▼
[4] User nhập mã giảm giá (AJAX) → POST /booking/apply-promo
    → PromotionService::applyPromotion()
        │
        ▼
[5] User chọn phương thức thanh toán → POST /payment/confirm
        │
        ▼
[6] PaymentController::confirm()
    → Gọi PaymentService::process(method, request)   [Strategy Pattern]
    → Nếu thành công → Gọi TicketService::confirmPayment()
        │
        ├─ Kiểm tra vé chưa hết hạn
        ├─ UPDATE với Optimistic Locking (version check)
        │   └─ rowCount = 0 → ConcurrencyException → báo lỗi
        └─ rowCount = 1 → Commit → Thành công
        │
        ▼
[7] Redirect → trang xác nhận đặt vé thành công
```
