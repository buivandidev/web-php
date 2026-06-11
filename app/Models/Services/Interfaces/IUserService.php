<?php
namespace App\Models\Services\Interfaces;

use App\Models\Domain\User;

interface IUserService
{
    public function authenticate(string $email, string $password): User;
    public function register(string $username, string $email, string $password): User;
}
