<?php
namespace App\Core;

class CsrfProtection
{
    private const TOKEN_KEY = 'csrf_token';

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    public static function validate(string $token): bool
    {
        return hash_equals(
            Session::get(self::TOKEN_KEY) ?? '',
            $token
        );
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::generate() . '">';
    }
}

// Global helper function for View
namespace {
    use App\Core\CsrfProtection;

    if (!function_exists('csrf_field')) {
        function csrf_field(): string
        {
            return CsrfProtection::field();
        }
    }
}
