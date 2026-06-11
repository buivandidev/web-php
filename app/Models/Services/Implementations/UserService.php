<?php
namespace App\Models\Services\Implementations;

use App\Models\Services\Interfaces\IUserService;
use App\Models\Repository\Interfaces\IUserRepository;
use App\Models\Domain\User;
use App\Core\Exceptions\BusinessException;

class UserService implements IUserService
{
    public function __construct(private readonly IUserRepository $userRepo) {}

    public function authenticate(string $email, string $password): User
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            throw new BusinessException('Email hoặc mật khẩu không chính xác.');
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new BusinessException('Email hoặc mật khẩu không chính xác.');
        }

        return $user;
    }

    public function register(string $username, string $email, string $password): User
    {
        $existing = $this->userRepo->findByEmail($email);
        if ($existing) {
            throw new BusinessException('Email đã được sử dụng.');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->userRepo->create([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'role'          => 'user'
        ]);

        return $this->userRepo->findById($id);
    }
}
