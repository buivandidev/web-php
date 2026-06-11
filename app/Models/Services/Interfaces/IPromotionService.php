<?php
namespace App\Models\Services\Interfaces;

use App\Core\ValueObjects\PromotionResult;

interface IPromotionService
{
    public function applyPromotion(string $code, float $subtotal): PromotionResult;
    public function validateCode(string $code): bool;
}
