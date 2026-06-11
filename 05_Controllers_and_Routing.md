# 05 — Controllers & Routing

> **Dành cho AI Agent:** Controller là "Cảnh sát giao thông" — chỉ điều hướng, không xử lý logic. Mọi tính toán thuộc về Service (file 04).

---

## 1. Base Controller

```php
<?php
// app/Controllers/BaseController.php

abstract class BaseController
{
    public function __construct(
        protected readonly Container $container
    ) {}

    // ── Render View ──────────────────────────────────────────
    protected function render(string $view, array $data = []): void
    {
        extract($data);  // $data['title'] → $title trong view
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: $viewPath");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require VIEW_PATH . '/layouts/main.php';   // $content inject vào layout
    }

    // ── Redirect ─────────────────────────────────────────────
    protected function redirect(string $url): never
    {
        header("Location: $url");
        exit;
    }

    // ── JSON Response (cho AJAX) ─────────────────────────────
    protected function json(mixed $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Lấy user đang đăng nhập ──────────────────────────────
    protected function getCurrentUserId(): ?int
    {
        return Session::get('user_id');
    }

    // ── Kiểm tra đăng nhập ───────────────────────────────────
    protected function requireLogin(): void
    {
        if (!$this->getCurrentUserId()) {
            $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }

    // ── Kiểm tra role Admin ───────────────────────────────────
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (Session::get('user_role') !== 'admin') {
            http_response_code(403);
            $this->render('errors.403');
            exit;
        }
    }

    // ── CSRF validation ───────────────────────────────────────
    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!CsrfProtection::validate($token)) {
            http_response_code(403);
            $this->json(['error' => 'CSRF token không hợp lệ.'], 403);
        }
    }
}
```

---

## 2. HomeController

```php
<?php
// app/Controllers/HomeController.php

class HomeController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /
    public function index(): void
    {
        $nowShowing = $this->movieService->getNowShowing();
        $comingSoon = $this->movieService->getComingSoon();

        $this->render('home.index', [
            'nowShowing' => $nowShowing,
            'comingSoon' => $comingSoon,
            'pageTitle'  => 'CinemaX — Đặt vé trực tuyến',
        ]);
    }
}
```

---

## 3. MovieController

```php
<?php
// app/Controllers/MovieController.php

class MovieController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /movies
    public function index(): void
    {
        $genre  = $_GET['genre'] ?? null;
        $status = $_GET['status'] ?? 'now_showing';
        $movies = $this->movieService->getFiltered($genre, $status);

        $this->render('movie.index', compact('movies', 'genre', 'status'));
    }

    // GET /movies/{id}
    public function detail(int $id): void
    {
        $movie     = $this->movieService->getDetail($id);
        $date      = $_GET['date'] ?? date('Y-m-d');
        $showtimes = $this->movieService->getShowtimesByDate($id, $date);

        $viewModel = MovieDetailViewModel::fromMovie($movie, $showtimes);

        $this->render('movie.detail', ['movie' => $viewModel, 'selectedDate' => $date]);
    }
}
```

---

## 4. BookingController

```php
<?php
// app/Controllers/BookingController.php

class BookingController extends BaseController
{
    private ITicketService    $ticketService;
    private IMovieService     $movieService;
    private IPromotionService $promotionService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->ticketService    = $container->make(ITicketService::class);
        $this->movieService     = $container->make(IMovieService::class);
        $this->promotionService = $container->make(IPromotionService::class);
    }

    // GET /booking/{showtimeId}
    // Hiển thị sơ đồ ghế
    public function seatMap(int $showtimeId): void
    {
        $this->requireLogin();

        $seatMapVM = $this->movieService->getSeatMapViewModel($showtimeId);
        $this->render('booking.seat_map', ['seatMap' => $seatMapVM]);
    }

    // POST /booking/hold
    // Giữ ghế — bắt buộc validate CSRF
    public function holdSeats(): void
    {
        $this->requireLogin();
        $this->validateCsrf();   // [ValidateAntiForgeryToken] tương đương

        $showtimeId = (int) ($_POST['showtime_id'] ?? 0);
        $seatCodes  = $_POST['seat_codes'] ?? [];   // ['A1', 'A2']

        if (!is_array($seatCodes) || empty($seatCodes)) {
            $this->json(['error' => 'Vui lòng chọn ít nhất 1 ghế.'], 422);
        }

        try {
            $holdResult = $this->ticketService->holdSeats(
                $this->getCurrentUserId(),
                $showtimeId,
                $seatCodes
            );

            // Lưu ticketIds vào session để trang thanh toán dùng
            Session::set('pending_ticket_ids', $holdResult->ticketIds);

            $this->json([
                'success'      => true,
                'redirectUrl'  => '/payment',
                'expiryTime'   => $holdResult->expiryTime,
                'remainingSeconds' => $holdResult->getRemainingSeconds(),
            ]);

        } catch (SeatUnavailableException $e) {
            $this->json(['error' => $e->getMessage()], 409);
        } catch (BusinessException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    // POST /booking/apply-promo  (AJAX)
    public function applyPromo(): void
    {
        $this->requireLogin();
        $this->validateCsrf();

        $code     = trim($_POST['code'] ?? '');
        $subtotal = (float) ($_POST['subtotal'] ?? 0);

        try {
            $result = $this->promotionService->applyPromotion($code, $subtotal);
            $this->json([
                'success'    => true,
                'discount'   => $result->discount,
                'totalPrice' => $result->totalPrice,
            ]);
        } catch (BusinessException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
```

---

## 5. PaymentController

```php
<?php
// app/Controllers/PaymentController.php

class PaymentController extends BaseController
{
    private ITicketService  $ticketService;
    private IPaymentService $paymentService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->ticketService  = $container->make(ITicketService::class);
        $this->paymentService = $container->make(IPaymentService::class);
    }

    // GET /payment
    public function index(): void
    {
        $this->requireLogin();

        $ticketIds = Session::get('pending_ticket_ids') ?? [];
        if (empty($ticketIds)) {
            $this->redirect('/');
        }

        // Build ViewModel từ ticketIds
        $confirmVM = $this->ticketService->buildConfirmViewModel($ticketIds);
        $this->render('payment.index', ['booking' => $confirmVM]);
    }

    // POST /payment/confirm
    public function confirm(): void
    {
        $this->requireLogin();
        $this->validateCsrf();

        $ticketIds     = Session::get('pending_ticket_ids') ?? [];
        $paymentMethod = $_POST['payment_method'] ?? '';

        if (empty($ticketIds)) {
            $this->redirect('/');
        }

        try {
            // Bước 1: Xử lý thanh toán qua Strategy Pattern
            $paymentResult = $this->paymentService->process(
                $paymentMethod,
                new PaymentRequest(
                    amount:           (float) ($_POST['total_price'] ?? 0),
                    orderDescription: 'Đặt vé xem phim CinemaX',
                    ticketIds:        $ticketIds,
                    userId:           $this->getCurrentUserId()
                )
            );

            if (!$paymentResult->success) {
                throw new BusinessException('Thanh toán không thành công. Vui lòng thử lại.');
            }

            // Bước 2: Xác nhận thanh toán + Optimistic Locking
            $this->ticketService->confirmPayment(
                $ticketIds,
                $this->getCurrentUserId(),
                $paymentMethod
            );

            // Clear session
            Session::remove('pending_ticket_ids');

            $this->redirect('/payment/success?txn=' . $paymentResult->transactionId);

        } catch (ConcurrencyException $e) {
            // Race condition: ghế vừa bị người khác lấy
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/movies');

        } catch (BusinessException $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/payment');
        }
    }

    // GET /payment/success
    public function success(): void
    {
        $this->requireLogin();
        $txnId = $_GET['txn'] ?? '';
        $this->render('payment.success', ['transactionId' => $txnId]);
    }
}
```

---

## 6. AuthController

```php
<?php
// app/Controllers/AuthController.php

class AuthController extends BaseController
{
    private IUserService $userService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userService = $container->make(IUserService::class);
    }

    // GET /login
    public function loginForm(): void
    {
        if ($this->getCurrentUserId()) $this->redirect('/');
        $vm = new LoginViewModel();
        $this->render('auth.login', ['vm' => $vm]);
    }

    // POST /login
    public function login(): void
    {
        $this->validateCsrf();

        $vm           = new LoginViewModel();
        $vm->email    = trim($_POST['email']    ?? '');
        $vm->password = trim($_POST['password'] ?? '');

        if (!$vm->validate()) {
            $this->render('auth.login', ['vm' => $vm]);
            return;
        }

        try {
            $user = $this->userService->authenticate($vm->email, $vm->password);
            Session::set('user_id',   $user->id);
            Session::set('user_role', $user->role);
            Session::regenerate();  // Chống session fixation

            $redirect = $_GET['redirect'] ?? '/';
            $this->redirect($redirect);

        } catch (BusinessException $e) {
            $vm->errors['general'] = $e->getMessage();
            $this->render('auth.login', ['vm' => $vm]);
        }
    }

    // POST /logout
    public function logout(): void
    {
        $this->validateCsrf();
        Session::destroy();
        $this->redirect('/login');
    }
}
```

---

## 7. AdminController

```php
<?php
// app/Controllers/AdminController.php

class AdminController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /admin/dashboard  [Authorize: admin]
    public function dashboard(): void
    {
        $this->requireAdmin();   // ← [Authorize(Roles = "admin")] tương đương

        $stats = $this->movieService->getDashboardStats();
        $this->render('admin.dashboard', ['stats' => $stats]);
    }

    // GET /admin/movies [Authorize: admin]
    public function movies(): void
    {
        $this->requireAdmin();
        $movies = $this->movieService->getAll();
        $this->render('admin.movies.index', compact('movies'));
    }

    // POST /admin/movies  [Authorize: admin]
    public function storeMovie(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        // TODO: Validate + upload poster + gọi movieService->create()
    }
}
```

---

## 8. Bảng tổng hợp Routes & Bảo mật

| Method | URL | Controller@Action | Auth | CSRF |
|--------|-----|-------------------|------|------|
| GET | `/` | HomeController@index | — | — |
| GET | `/movies` | MovieController@index | — | — |
| GET | `/movies/{id}` | MovieController@detail | — | — |
| GET | `/booking/{showtimeId}` | BookingController@seatMap | ✅ Login | — |
| POST | `/booking/hold` | BookingController@holdSeats | ✅ Login | ✅ |
| POST | `/booking/apply-promo` | BookingController@applyPromo | ✅ Login | ✅ |
| GET | `/payment` | PaymentController@index | ✅ Login | — |
| POST | `/payment/confirm` | PaymentController@confirm | ✅ Login | ✅ |
| GET | `/payment/success` | PaymentController@success | ✅ Login | — |
| GET | `/login` | AuthController@loginForm | — | — |
| POST | `/login` | AuthController@login | — | ✅ |
| POST | `/logout` | AuthController@logout | ✅ Login | ✅ |
| GET | `/admin/*` | AdminController@* | ✅ Admin | — |
| POST | `/admin/*` | AdminController@* | ✅ Admin | ✅ |

---

## 9. CSRF Protection Helper

```php
<?php
// app/Core/CsrfProtection.php

class CsrfProtection
{
    private const TOKEN_KEY = 'csrf_token';

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    public static function validate(string $token): bool
    {
        return hash_equals(
            Session::get(self::TOKEN_KEY) ?? '',
            $token
        );
    }

    // Dùng trong View: <?= csrf_field() ?>
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::generate() . '">';
    }
}

// Helper function dùng trong View
function csrf_field(): string {
    return CsrfProtection::field();
}
```
