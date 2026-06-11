<?php
namespace App\Models\Services\Interfaces;

use App\Models\Domain\Movie;
use App\ViewModels\SeatMapViewModel;

interface IMovieService
{
    public function getNowShowing(): array;
    public function getComingSoon(): array;
    public function getFiltered(?string $genre, string $status): array;
    public function getAll(): array;
    public function getDetail(int $movieId): Movie;
    public function getShowtimesByDate(int $movieId, string $date): array;
    public function getSeatMapViewModel(int $showtimeId): SeatMapViewModel;
    public function getDashboardStats(): array;
}
