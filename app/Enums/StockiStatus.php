<?php

namespace App\Enums;

final class StockiStatus
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';

    private function __construct() {}

    public static function getValues(): array
    {
        return [self::PENDING, self::COMPLETED];
    }
}
