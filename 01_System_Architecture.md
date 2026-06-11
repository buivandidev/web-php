# 01 — Kiến trúc Hệ thống & Cấu hình

> **Dành cho AI Agent:** Đọc file này ĐẦU TIÊN trước khi viết bất kỳ dòng code nào. Đây là "la bàn" định hướng toàn bộ dự án.

---

## 1. Mô hình kiến trúc: MVC thuần PHP

Dự án áp dụng **MVC (Model - View - Controller)** tự xây dựng (custom), **không dùng framework** (không Laravel, không Symfony). Mọi routing, DI, và lifecycle đều tự viết tay.

### Nguyên tắc cốt lõi: **Thin Controller — Fat Model**

| Thành phần | Trách nhiệm | KHÔNG được làm |
|------------|-------------|----------------|
| **Controller** | Nhận `$_GET`/`$_POST`, gọi Service, trả View | Viết SQL, chứa logic tính tiền, dùng `new Service()` |
| **Model / Service** | Toàn bộ Business Logic & Data Access | Trả HTML, echo trực tiếp |
| **View** | Hiển thị dữ liệu đã được xử lý | Gọi DB, chứa logic phức tạp |

---

## 2. Tech Stack

| Thành phần | Công nghệ | Phiên bản tối thiểu |
|------------|-----------|----------------------|
| Ngôn ngữ   | PHP       | 8.1+                 |
| Database   | PostgreSQL | 14+                 |
| DB Driver  | PDO (PHP Data Objects) | — |
| Frontend   | Bootstrap  | 5.3                 |
| Web Server | Apache / Nginx + PHP-FPM | — |
| Template   | PHP thuần (không Twig, không Blade) | — |

---

## 3. Cấu trúc thư mục chuẩn

```
cinema/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── MovieController.php
│   │   ├── BookingController.php
│   │   ├── PaymentController.php
│   │   └── AdminController.php
│   │
│   ├── Models/
│   │   ├── Domain/                  # Entities — ánh xạ 1-1 với bảng DB
│   │   │   ├── User.php
│   │   │   ├── Movie.php
│   │   │   ├── Showtime.php
│   │   │   ├── Room.php
│   │   │   └── Ticket.php
│   │   │
│   │   ├── Repository/              # Data Access Layer
│   │   │   ├── Interfaces/
│   │   │   │   ├── IMovieRepository.php
│   │   │   │   ├── IShowtimeRepository.php
│   │   │   │   └── ITicketRepository.php
│   │   │   └── Implementations/
│   │   │       ├── MovieRepository.php
│   │   │       ├── ShowtimeRepository.php
│   │   │       └── TicketRepository.php
│   │   │
│   │   └── Services/                # Business Logic Layer
│   │       ├── Interfaces/
│   │       │   ├── IMovieService.php
│   │       │   ├── ITicketService.php
│   │       │   ├── IPromotionService.php
│   │       │   └── IPaymentService.php
│   │       └── Implementations/
│   │           ├── MovieService.php
│   │           ├── TicketService.php
│   │           ├── PromotionService.php
│   │           └── PaymentService.php
│   │
│   ├── ViewModels/                  # DTO — dữ liệu an toàn truyền xuống View
│   │   ├── MovieDetailViewModel.php
│   │   ├── SeatMapViewModel.php
│   │   ├── LoginViewModel.php
│   │   └── BookingConfirmViewModel.php
│   │
│   └── Core/                        # Infrastructure
│       ├── Database.php             # Singleton PDO connection
│       ├── Router.php               # Custom router
│       ├── Container.php            # Simple DI Container
│       ├── Session.php              # Session wrapper
│       └── CsrfProtection.php       # CSRF token helper
│
├── views/
│   ├── layouts/
│   │   ├── main.php                 # Dark mode master layout
│   │   └── admin.php
│   ├── home/
│   ├── movie/
│   ├── booking/
│   ├── payment/
│   ├── admin/
│   └── partials/
│       ├── seat_map.php             # Component ghế ngồi tái sử dụng
│       ├── navbar.php
│       └── flash_message.php
│
├── public/                          # Document Root — chỉ file này public
│   ├── index.php                    # Front Controller duy nhất
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css
│   │   └── js/
│   │       └── seat_selector.js     # JS xử lý chọn ghế (AJAX)
│   └── uploads/
│
├── config/
│   ├── database.php                 # DB credentials
│   ├── app.php                      # App settings, timezone
│   └── routes.php                   # Định nghĩa tất cả routes
│
├── migrations/                      # SQL migration files
│   ├── 001_create_users.sql
│   ├── 002_create_movies.sql
│   ├── 003_create_rooms.sql
│   ├── 004_create_showtimes.sql
│   └── 005_create_tickets.sql
│
└── tests/
    ├── Unit/
    └── Integration/
```

> **Quy tắc Document Root:** Toàn bộ request phải đi qua `public/index.php`. Thư mục `app/`, `config/`, `migrations/` phải nằm ngoài document root, không accessible trực tiếp qua browser.

---

## 4. Front Controller & Routing

Mọi request đều qua `public/index.php`:

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php'; // hoặc custom autoloader

$container = require_once __DIR__ . '/../config/app.php';
$router    = require_once __DIR__ . '/../config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
```

**Cấu hình routes (`config/routes.php`):**

```php
<?php
// config/routes.php

$router->get('/',                         'HomeController@index');
$router->get('/movies',                   'MovieController@index');
$router->get('/movies/{id}',              'MovieController@detail');
$router->get('/booking/{showtimeId}',     'BookingController@seatMap');
$router->post('/booking/hold',            'BookingController@holdSeats');
$router->post('/payment/confirm',         'PaymentController@confirm');
$router->get('/admin/dashboard',          'AdminController@dashboard');
$router->post('/admin/movies',            'AdminController@storeMovie');

return $router;
```

---

## 5. Dependency Injection Container

Controller **không bao giờ** dùng `new` để khởi tạo Service. Dùng DI Container:

```php
<?php
// app/Core/Container.php

class Container
{
    private array $bindings = [];

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function make(string $abstract): mixed
    {
        if (!isset($this->bindings[$abstract])) {
            throw new \RuntimeException("No binding for: $abstract");
        }
        return ($this->bindings[$abstract])($this);
    }
}
```

**Đăng ký binding (`config/app.php`):**

```php
<?php
$container = new Container();

$container->bind(Database::class, fn() => Database::getInstance());

$container->bind(IMovieRepository::class,
    fn($c) => new MovieRepository($c->make(Database::class)));

$container->bind(ITicketRepository::class,
    fn($c) => new TicketRepository($c->make(Database::class)));

$container->bind(ITicketService::class,
    fn($c) => new TicketService($c->make(ITicketRepository::class)));

$container->bind(IMovieService::class,
    fn($c) => new MovieService($c->make(IMovieRepository::class)));

return $container;
```

---

## 6. Design Patterns bắt buộc áp dụng

| Pattern | Áp dụng tại | Mục đích |
|---------|------------|---------|
| **Repository Pattern** | `app/Models/Repository/` | Tách biệt logic truy vấn DB khỏi Business Logic |
| **Dependency Injection** | `Container.php` + tất cả Controller | Loose coupling, dễ test |
| **ViewModel / DTO** | `app/ViewModels/` | Bảo vệ Domain Model, tránh lộ thông tin nhạy cảm |
| **Strategy Pattern** | `PaymentService.php` | Hỗ trợ nhiều cổng thanh toán không cần sửa core code |
| **Singleton** | `Database.php` | Chỉ tạo 1 PDO connection duy nhất trong toàn request |
| **Front Controller** | `public/index.php` | Một điểm vào duy nhất, tập trung xử lý middleware |

---

## 7. Autoloading

Sử dụng PSR-4 autoloading (qua Composer hoặc tự viết):

```json
// composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

Hoặc custom autoloader nếu không dùng Composer:

```php
spl_autoload_register(function (string $class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require_once $path;
});
```

---

## 8. Environment & Cấu hình Database

```php
<?php
// config/database.php
return [
    'driver'   => 'pgsql',
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'port'     => $_ENV['DB_PORT']     ?? '5432',
    'dbname'   => $_ENV['DB_NAME']     ?? 'cinema_db',
    'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8',
];
```

```php
<?php
// app/Core/Database.php — Singleton PDO

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require CONFIG_PATH . '/database.php';
            $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}";
            self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
```
