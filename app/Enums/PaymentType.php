<?php

namespace App\Enums;

final class PaymentType
{
    public const MONTHLY = 'mensuel';
    public const QUARTERLY = 'trimestriel';
    public const ANNUAL = 'annuel';

    private function __construct() {}

    public static function getValues(): array
    {
        return [self::MONTHLY, self::QUARTERLY,self::ANNUAL];
    }
}
