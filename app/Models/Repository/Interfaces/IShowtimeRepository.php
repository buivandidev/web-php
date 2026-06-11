<?php
namespace App\Models\Repository\Interfaces;

use App\Models\Domain\Showtime;

interface IShowtimeRepository
{
    public function getByMovieAndDate(int $movieId, string $date): array;
    public function findById(int $id): ?Showtime;
}
