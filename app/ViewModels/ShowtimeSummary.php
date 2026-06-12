<?php
namespace App\ViewModels;

class ShowtimeSummary
{
    public int    $id;
    public string $showDate;
    public string $startTime;
    public string $formattedPrice;
    public string $roomName;
    public int    $availableSeats;
}
