<?php
namespace App\Models\Repository\Interfaces;

use App\Models\Domain\User;

interface IUserRepository
{
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function create(array $data): int;
}
