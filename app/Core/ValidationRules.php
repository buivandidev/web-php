<?php
namespace App\Core;

class ValidationRules
{
    // Vé
    public const MAX_TICKETS_PER_BOOKING = 5;
    public const MIN_TICKETS_PER_BOOKING = 1;

    // Hold time
    public const HOLD_DURATION_MINUTES = 10;

    // Mật khẩu
    public const PASSWORD_MIN_LENGTH = 8;

    // Username
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 100;
}
