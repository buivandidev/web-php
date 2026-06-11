<?php
namespace App\Models\Domain;

class Showtime extends BaseModel
{
    public int    $id;
    public int    $movieId;
    public int    $roomId;
    public string $showDate;
    public string $startTime;
    public float  $price;
    public string $createdAt;

    // Eager-loaded relations
    public ?Movie $movie = null;
    public ?Room  $room  = null;

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 0, ',', '.') . '₫';
    }
}
