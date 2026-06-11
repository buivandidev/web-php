<?php
namespace App\Core\ValueObjects;

class PaymentRequest
{
    public function __construct(
        public readonly float  $amount,
        public readonly string $orderDescription,
        public readonly array  $ticketIds,
        public readonly int    $userId
    ) {}
}
