<?php
namespace App\Jobs;

use App\Models\Services\Interfaces\ITicketService;

class HoldExpiryJob
{
    public function __construct(
        private readonly ITicketService $ticketService
    ) {}

    public function run(): void
    {
        $released = $this->ticketService->releaseExpiredHolds();
        echo date('Y-m-d H:i:s') . " - Released {$released} expired holds.\n";
    }
}
