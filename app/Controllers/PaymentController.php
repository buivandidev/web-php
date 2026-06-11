<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\ITicketService;
use App\Models\Services\Interfaces\IPaymentService;
use App\Core\ValueObjects\PaymentRequest;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\ConcurrencyException;
use App\Core\Session;

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
        try {
            $confirmVM = $this->ticketService->buildConfirmViewModel($ticketIds);
            $this->render('payment.index', ['booking' => $confirmVM]);
        } catch (BusinessException $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect('/');
        }
    }

    // POST /payment/confirm
    public function confirm(): void
    {
        $this->requireLogin();
        $this->validateCsrf();

        $ticketIds     = Session::get('pending_ticket_ids') ?? [];
        $paymentMethod = $_POST['payment_method'] ?? '';
        $totalPrice    = isset($_POST['total_price']) ? (float)$_POST['total_price'] : null;
        $promotionCode = !empty($_POST['promotion_code']) ? trim($_POST['promotion_code']) : null;

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
                $paymentMethod,
                $totalPrice,
                $promotionCode
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
