<?php
namespace App\Models\Services\Implementations;

use App\Models\Services\Interfaces\IPaymentService;
use App\Core\ValueObjects\PaymentRequest;
use App\Core\ValueObjects\PaymentResult;
use App\Core\Exceptions\BusinessException;

interface IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult;
}

class VNPayStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        // Giả lập tích hợp VNPay API
        return new PaymentResult(success: true, transactionId: 'VNP_' . uniqid());
    }
}

class MoMoStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        // Giả lập tích hợp MoMo API
        return new PaymentResult(success: true, transactionId: 'MOMO_' . uniqid());
    }
}

class CashStrategy implements IPaymentStrategy
{
    public function process(PaymentRequest $request): PaymentResult
    {
        return new PaymentResult(success: true, transactionId: 'CASH_' . uniqid());
    }
}

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
