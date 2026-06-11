<?php
namespace App\Core\ValueObjects;

class PromotionResult
{
    public function __construct(
        public readonly string $code,
        public readonly float  $discount,
        public readonly float  $totalPrice
    ) {}
}
