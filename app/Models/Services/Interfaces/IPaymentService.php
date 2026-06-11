<?php
namespace App\Models\Services\Interfaces;

use App\Core\ValueObjects\PaymentRequest;
use App\Core\ValueObjects\PaymentResult;

interface IPaymentService
{
    public function process(string $method, PaymentRequest $request): PaymentResult;
}
