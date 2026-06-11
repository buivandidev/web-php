<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\ITicketService;
use App\Models\Services\Interfaces\IMovieService;
use App\Models\Services\Interfaces\IPromotionService;
use App\Core\Exceptions\SeatUnavailableException;
use App\Core\Exceptions\BusinessException;
use App\Core\Session;

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
        $this->validateCsrf();

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
