<?php
namespace App\Models\Domain;

class Promotion extends BaseModel
{
    public int     $id;
    public string  $code;
    public string  $discountType;   // 'percent' | 'fixed'
    public float   $discountValue;
    public ?int    $maxUses = null;
    public int     $usedCount = 0;
    public ?string $expiresAt = null;
    public bool    $isActive = true;
}
