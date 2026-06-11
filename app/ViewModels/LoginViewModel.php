<?php
namespace App\ViewModels;

class LoginViewModel
{
    public string $email    = '';
    public string $password = '';

    // Validation errors
    public array $errors = [];

    public function validate(): bool
    {
        $this->errors = [];

        if (empty($this->email)) {
            $this->errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email không đúng định dạng.';
        }

        if (empty($this->password)) {
            $this->errors['password'] = 'Mật khẩu không được để trống.';
        }

        return empty($this->errors);
    }
}
