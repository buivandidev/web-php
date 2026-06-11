<?php
namespace App\Models\Repository\Implementations;

use App\Models\Repository\Interfaces\IMovieRepository;
use App\Models\Domain\Movie;
use PDO;

class MovieRepository implements IMovieRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findById(int $id): ?Movie
    {
        $stmt = $this->pdo->prepare('SELECT * FROM movies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Movie::fromArray($row) : null;
    }

    public function getFiltered(?string $genre, string $status): array
    {
        $sql = 'SELECT * FROM movies WHERE status = :status';
        $params = [':status' => $status];
        if ($genre) {
            $sql .= ' AND genre = :genre';
            $params[':genre'] = $genre;
        }
        $sql .= ' ORDER BY id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => Movie::fromArray($row), $rows);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM movies ORDER BY id DESC');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => Movie::fromArray($row), $rows);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO movies (title, poster_url, genre, status, duration_minutes, description, age_rating)
             VALUES (:title, :poster_url, :genre, :status, :duration_minutes, :description, :age_rating)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }
}
