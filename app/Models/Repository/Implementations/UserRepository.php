<?php
namespace App\Models\Repository\Implementations;

use App\Models\Repository\Interfaces\IUserRepository;
use App\Models\Domain\User;
use PDO;

class UserRepository implements IUserRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }
}
