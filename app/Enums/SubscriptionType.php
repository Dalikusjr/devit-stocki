<?php

namespace App\Enums;

final class SubscriptionType
{
    public const MAINTAIN = 'maintain';
    public const PROJECT = 'project';
    public const STOCKI_PREMIUM = 'stocki_premium';
    public const STOCKI = 'stocki';

    private function __construct() {}

    public static function getValues(): array
    {
        return [self::MAINTAIN, self::PROJECT, self::STOCKI_PREMIUM, self::STOCKI];
    }
}
