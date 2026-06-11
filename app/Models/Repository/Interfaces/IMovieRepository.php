<?php
namespace App\Models\Repository\Interfaces;

use App\Models\Domain\Movie;

interface IMovieRepository
{
    public function findById(int $id): ?Movie;
    public function getFiltered(?string $genre, string $status): array;
    public function getAll(): array;
    public function create(array $data): int;
}
