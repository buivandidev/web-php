<?php
namespace App\ViewModels;

class SeatMapViewModel
{
    public int    $showtimeId;
    public string $movieTitle;
    public string $showDate;
    public string $startTime;
    public string $roomName;
    public float  $pricePerSeat;
    public int    $totalRows;
    public int    $seatsPerRow;

    /**
     * Map: ['A1' => 'available', 'A2' => 'holding', 'B3' => 'paid']
     * @var array<string, string>
     */
    public array $seatStatuses = [];

    public function getSeatStatus(string $seatCode): string
    {
        return $this->seatStatuses[$seatCode] ?? 'available';
    }
}
