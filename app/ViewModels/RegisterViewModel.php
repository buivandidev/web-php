<?php
namespace App\ViewModels;

use App\Core\ValidationRules;

class RegisterViewModel
{
    public string $username        = '';
    public string $email           = '';
    public string $password        = '';
    public string $confirmPassword = '';
    public array  $errors          = [];

    public function validate(): bool
    {
        $this->errors = [];

        if (strlen($this->username) < ValidationRules::USERNAME_MIN_LENGTH) {
            $this->errors['username'] = 'Tên người dùng tối thiểu ' . ValidationRules::USERNAME_MIN_LENGTH . ' ký tự.';
        } elseif (strlen($this->username) > ValidationRules::USERNAME_MAX_LENGTH) {
            $this->errors['username'] = 'Tên người dùng tối đa ' . ValidationRules::USERNAME_MAX_LENGTH . ' ký tự.';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email không đúng định dạng.';
        }

        if (strlen($this->password) < ValidationRules::PASSWORD_MIN_LENGTH) {
            $this->errors['password'] = 'Mật khẩu tối thiểu ' . ValidationRules::PASSWORD_MIN_LENGTH . ' ký tự.';
        }

        if ($this->password !== $this->confirmPassword) {
            $this->errors['confirmPassword'] = 'Mật khẩu xác nhận không khớp.';
        }

        return empty($this->errors);
    }
}
