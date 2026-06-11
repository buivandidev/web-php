<?php
namespace App\Models\Repository\Interfaces;

use App\Models\Domain\Ticket;
use App\Models\Domain\Showtime;

interface ITicketRepository
{
    public function getActiveSeats(int $showtimeId, array $seatCodes): array;
    public function getActiveTickets(int $showtimeId): array;
    public function updateStatusWithVersion(
        int     $id,
        string  $newStatus,
        int     $expectedVersion,
        ?float  $totalPrice = null,
        ?string $promotionCode = null
    ): int;
    public function cancelExpiredHolds(): int;
    public function create(array $data): int;
    public function findById(int $id): ?Ticket;
    public function findByUserId(int $userId): array;
    public function getShowtimeByTicketId(int $ticketId): ?Showtime;
}
