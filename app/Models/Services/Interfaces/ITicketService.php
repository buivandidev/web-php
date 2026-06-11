<?php
namespace App\Models\Services\Interfaces;

use App\Core\ValueObjects\HoldResult;
use App\ViewModels\BookingConfirmViewModel;

interface ITicketService
{
    public function holdSeats(int $userId, int $showtimeId, array $seatCodes): HoldResult;
    public function confirmPayment(array $ticketIds, int $userId, string $paymentMethod, ?float $totalPrice = null, ?string $promotionCode = null): bool;
    public function releaseExpiredHolds(): int;     // Trả về số vé đã hủy
    public function getUserTickets(int $userId): array;
    public function buildConfirmViewModel(array $ticketIds): BookingConfirmViewModel;
}
