<?php

namespace App\Enums;

final class PaymentStatus
{
    public const PAID = 'paid';
    public const UNPAID = 'unpaid';

    private function __construct() {}

    public static function getValues(): array 
    {
        return [self::PAID,self::UNPAID];
    }
}
