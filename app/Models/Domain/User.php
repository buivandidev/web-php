<?php
namespace App\Models\Domain;

class User extends BaseModel
{
    public int     $id;
    public string  $username;
    public string  $email;
    public string  $passwordHash;   // bcrypt — KHÔNG bao giờ expose ra View
    public string  $role;           // 'admin' | 'user'
    public string  $createdAt;
    public string  $updatedAt;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
