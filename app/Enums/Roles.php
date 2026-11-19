<?php

namespace App\Enums;

final class Roles
{
    public const ADMIN = 'admin';

    private function __construct() {}

    public static function getValues(): array
    {
        return [self::ADMIN];
    }
}
