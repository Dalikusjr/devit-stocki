<?php

namespace App\Security;

class PasswordHasher
{
    public static function hashPassword(string $passwordRaw): string
    {
        return password_hash($passwordRaw, PASSWORD_DEFAULT);
    }
}
