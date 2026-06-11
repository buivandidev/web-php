<?php
namespace App\Models\Repository\Interfaces;

use App\Models\Domain\Promotion;

interface IPromotionRepository
{
    public function findByCode(string $code): ?Promotion;
    public function incrementUsedCount(string $code): void;
}
