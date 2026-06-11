<?php
namespace App\Core\ValueObjects;

class HoldResult
{
    public function __construct(
        public readonly array  $ticketIds,
        public readonly string $expiryTime
    ) {}

    public function getRemainingSeconds(): int
    {
        return max(0, strtotime($this->expiryTime) - time());
    }
}
