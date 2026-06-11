# 03 — Domain Models & ViewModels

> **Dành cho AI Agent:** Tất cả Domain Model nằm trong `app/Models/Domain/`. Tất cả ViewModel nằm trong `app/ViewModels/`. **KHÔNG BAO GIỜ** truyền Domain Model trực tiếp xuống View.

---

## 1. Base Model

```php
<?php
// app/Models/Domain/BaseModel.php

abstract class BaseModel
{
    /**
     * Map array (từ DB fetch) sang properties của class.
     * Dùng: $movie = Movie::fromArray($row);
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            // Chuyển snake_case → camelCase: movie_id → movieId
            $property = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists($instance, $property)) {
                $instance->$property = $value;
            }
        }
        return $instance;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
```

---

## 2. Domain Models (Entities)

### `User`

```php
<?php
// app/Models/Domain/User.php

class User extends BaseModel
{
    public int     $id;
    public string  $username;
    public string  $email;
    public string  $passwordHash;   // bcrypt — KHÔNG bao giờ expose ra View
    public string  $role;           // 'admin' | 'user'
    public string  $createdAt;
    public string  $updatedAt;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

### `Movie`

```php
<?php
// app/Models/Domain/Movie.php

class Movie extends BaseModel
{
    public int    $id;
    public string $title;
    public string $posterUrl;
    public string $genre;
    public string $status;           // 'now_showing' | 'coming_soon' | 'ended'
    public int    $durationMinutes;
    public string $description;
    public string $ageRating;        // 'P' | 'C13' | 'C16' | 'C18'
    public string $createdAt;

    public function isNowShowing(): bool
    {
        return $this->status === 'now_showing';
    }

    public function getFormattedDuration(): string
    {
        $h = intdiv($this->durationMinutes, 60);
        $m = $this->durationMinutes % 60;
        return $h > 0 ? "{$h}h {$m}p" : "{$m}p";
    }
}
```

### `Room`

```php
<?php
// app/Models/Domain/Room.php

class Room extends BaseModel
{
    public int    $id;
    public string $name;
    public int    $totalRows;
    public int    $seatsPerRow;

    public function getTotalSeats(): int
    {
        return $this->totalRows * $this->seatsPerRow;
    }
}
```

### `Showtime`

```php
<?php
// app/Models/Domain/Showtime.php

class Showtime extends BaseModel
{
    public int    $id;
    public int    $movieId;
    public int    $roomId;
    public string $showDate;
    public string $startTime;
    public float  $price;
    public string $createdAt;

    // Eager-loaded relations (set thủ công sau khi JOIN)
    public ?Movie $movie = null;
    public ?Room  $room  = null;

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 0, ',', '.') . '₫';
    }
}
```

### `Ticket`

```php
<?php
// app/Models/Domain/Ticket.php

class Ticket extends BaseModel
{
    public int     $id;
    public int     $showtimeId;
    public int     $userId;
    public string  $seatCode;
    public string  $status;           // 'holding' | 'paid' | 'cancelled'
    public ?string $holdExpiryTime;   // NULL khi paid/cancelled
    public float   $totalPrice;
    public ?string $promotionCode;
    public int     $version;          // Optimistic Locking
    public string  $bookedAt;

    public function isHolding(): bool
    {
        return $this->status === 'holding';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isExpired(): bool
    {
        return $this->isHolding()
            && $this->holdExpiryTime !== null
            && strtotime($this->holdExpiryTime) < time();
    }
}
```

---

## 3. ViewModels (DTO — Data Transfer Object)

> **Quy tắc:** ViewModel chỉ chứa đúng những field cần thiết cho View đó. Không hơn, không kém.

### `LoginViewModel`

```php
<?php
// app/ViewModels/LoginViewModel.php

class LoginViewModel
{
    public string $email    = '';
    public string $password = '';

    // Validation errors — được set bởi Controller sau khi validate
    public array $errors = [];

    public function validate(): bool
    {
        $this->errors = [];

        if (empty($this->email)) {
            $this->errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email không đúng định dạng.';
        }

        if (empty($this->password)) {
            $this->errors['password'] = 'Mật khẩu không được để trống.';
        }

        return empty($this->errors);
    }
}
```

### `RegisterViewModel`

```php
<?php
// app/ViewModels/RegisterViewModel.php

class RegisterViewModel
{
    public string $username        = '';
    public string $email           = '';
    public string $password        = '';
    public string $confirmPassword = '';
    public array  $errors          = [];

    public function validate(): bool
    {
        $this->errors = [];

        if (strlen($this->username) < 3) {
            $this->errors['username'] = 'Tên người dùng tối thiểu 3 ký tự.';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email không đúng định dạng.';
        }
        if (strlen($this->password) < 8) {
            $this->errors['password'] = 'Mật khẩu tối thiểu 8 ký tự.';
        }
        if ($this->password !== $this->confirmPassword) {
            $this->errors['confirmPassword'] = 'Mật khẩu xác nhận không khớp.';
        }

        return empty($this->errors);
    }
}
```

### `MovieDetailViewModel`

```php
<?php
// app/ViewModels/MovieDetailViewModel.php

class MovieDetailViewModel
{
    public int    $id;
    public string $title;
    public string $posterUrl;
    public string $genre;
    public string $status;
    public string $formattedDuration;   // VD: "2h 15p"
    public string $description;
    public string $ageRating;

    /** @var ShowtimeSummary[] */
    public array $showtimes = [];

    // Factory method — map từ Domain Model
    public static function fromMovie(Movie $movie, array $showtimes = []): self
    {
        $vm = new self();
        $vm->id                = $movie->id;
        $vm->title             = $movie->title;
        $vm->posterUrl         = $movie->posterUrl;
        $vm->genre             = $movie->genre;
        $vm->status            = $movie->status;
        $vm->formattedDuration = $movie->getFormattedDuration();
        $vm->description       = $movie->description;
        $vm->ageRating         = $movie->ageRating;
        $vm->showtimes         = $showtimes;
        return $vm;
    }
}

class ShowtimeSummary
{
    public int    $id;
    public string $showDate;
    public string $startTime;
    public string $formattedPrice;
    public string $roomName;
    public int    $availableSeats;
}
```

### `SeatMapViewModel`

```php
<?php
// app/ViewModels/SeatMapViewModel.php

class SeatMapViewModel
{
    public int    $showtimeId;
    public string $movieTitle;
    public string $showDate;
    public string $startTime;
    public string $roomName;
    public float  $pricePerSeat;
    public int    $totalRows;
    public int    $seatsPerRow;

    /**
     * Map: ['A1' => 'available', 'A2' => 'holding', 'B3' => 'paid']
     * @var array<string, string>
     */
    public array $seatStatuses = [];

    public function getSeatStatus(string $seatCode): string
    {
        return $this->seatStatuses[$seatCode] ?? 'available';
    }
}
```

### `BookingConfirmViewModel`

```php
<?php
// app/ViewModels/BookingConfirmViewModel.php

class BookingConfirmViewModel
{
    public string  $movieTitle;
    public string  $showDate;
    public string  $startTime;
    public string  $roomName;

    /** @var string[] */
    public array   $selectedSeats;      // ['A1', 'A2']

    public int     $quantity;           // Số ghế
    public float   $subtotal;           // Giá gốc
    public float   $discount;           // Số tiền giảm
    public float   $totalPrice;         // Thành tiền
    public string  $holdExpiryTime;     // Thời gian hết hạn giữ chỗ (countdown)
    public ?string $promotionCode;

    /** @var int[] Danh sách ticket ID đang giữ */
    public array   $ticketIds;
}
```

---

## 4. Validation — Data Annotations qua PHP Constants

Định nghĩa các rule tập trung để dùng lại:

```php
<?php
// app/Core/ValidationRules.php

class ValidationRules
{
    // Vé
    public const MAX_TICKETS_PER_BOOKING = 5;
    public const MIN_TICKETS_PER_BOOKING = 1;

    // Hold time
    public const HOLD_DURATION_MINUTES = 10;

    // Mật khẩu
    public const PASSWORD_MIN_LENGTH = 8;

    // Username
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 100;
}
```

**Sử dụng trong Service:**

```php
if (count($seatCodes) > ValidationRules::MAX_TICKETS_PER_BOOKING) {
    throw new BusinessException(
        'Chỉ được đặt tối đa ' . ValidationRules::MAX_TICKETS_PER_BOOKING . ' vé mỗi lần.'
    );
}
```

---

## 5. Custom Exception Classes

```php
<?php
// app/Core/Exceptions/BusinessException.php
class BusinessException extends \RuntimeException {}

// app/Core/Exceptions/SeatUnavailableException.php
class SeatUnavailableException extends BusinessException
{
    public function __construct(
        public readonly array $unavailableSeats
    ) {
        parent::__construct(
            'Ghế ' . implode(', ', $unavailableSeats) . ' đã có người đặt.'
        );
    }
}

// app/Core/Exceptions/ConcurrencyException.php
class ConcurrencyException extends BusinessException {}

// app/Core/Exceptions/NotFoundException.php
class NotFoundException extends \RuntimeException {}
```
