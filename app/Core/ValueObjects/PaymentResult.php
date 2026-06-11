<?php
namespace App\Core\ValueObjects;

class PaymentResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $transactionId,
        public readonly string $message = ''
    ) {}
}
