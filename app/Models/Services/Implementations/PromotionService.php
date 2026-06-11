<?php
namespace App\Models\Services\Implementations;

use App\Models\Services\Interfaces\IPromotionService;
use App\Models\Repository\Interfaces\IPromotionRepository;
use App\Core\Exceptions\BusinessException;
use App\Core\ValueObjects\PromotionResult;

class PromotionService implements IPromotionService
{
    public function __construct(
        private readonly IPromotionRepository $promoRepo
    ) {}

    public function applyPromotion(string $code, float $subtotal): PromotionResult
    {
        $promo = $this->promoRepo->findByCode(strtoupper(trim($code)));

        if (!$promo) {
            throw new BusinessException('Mã giảm giá không tồn tại.');
        }
        if (!$promo->isActive) {
            throw new BusinessException('Mã giảm giá đã bị vô hiệu hóa.');
        }
        if ($promo->expiresAt && strtotime($promo->expiresAt) < time()) {
            throw new BusinessException('Mã giảm giá đã hết hạn.');
        }
        if ($promo->maxUses !== null && $promo->usedCount >= $promo->maxUses) {
            throw new BusinessException('Mã giảm giá đã hết lượt sử dụng.');
        }

        $discount = match($promo->discountType) {
            'percent' => $subtotal * ($promo->discountValue / 100),
            'fixed'   => min($promo->discountValue, $subtotal),
            default   => throw new BusinessException('Loại giảm giá không hợp lệ.'),
        };

        return new PromotionResult(
            code:       $code,
            discount:   $discount,
            totalPrice: max(0.0, $subtotal - $discount)
        );
    }

    public function validateCode(string $code): bool
    {
        try {
            $this->applyPromotion($code, 1);
            return true;
        } catch (BusinessException) {
            return false;
        }
    }
}
