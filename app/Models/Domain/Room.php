<?php
namespace App\Models\Domain;

class Room extends BaseModel
{
    public int    $id;
    public string $name;
    public int    $totalRows;
    public int    $seatsPerRow;

    public function getTotalSeats(): int
    {
        return $this->totalRows * $this->seatsPerRow;
    }
}
