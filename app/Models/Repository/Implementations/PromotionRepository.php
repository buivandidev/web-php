<?php
namespace App\Models\Repository\Implementations;

use App\Models\Repository\Interfaces\IPromotionRepository;
use App\Models\Domain\Promotion;
use PDO;

class PromotionRepository implements IPromotionRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findByCode(string $code): ?Promotion
    {
        $stmt = $this->pdo->prepare('SELECT * FROM promotions WHERE code = ?');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $promo = new Promotion();
        $promo->id = $row['id'];
        $promo->code = $row['code'];
        $promo->discountType = $row['discount_type'];
        $promo->discountValue = (float)$row['discount_value'];
        $promo->maxUses = $row['max_uses'] !== null ? (int)$row['max_uses'] : null;
        $promo->usedCount = (int)$row['used_count'];
        $promo->expiresAt = $row['expires_at'];
        $promo->isActive = (bool)$row['is_active'];

        return $promo;
    }

    public function incrementUsedCount(string $code): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE promotions SET used_count = used_count + 1 WHERE code = ?'
        );
        $stmt->execute([$code]);
    }
}
