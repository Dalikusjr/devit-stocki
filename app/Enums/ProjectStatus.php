<?php

namespace App\Enums;

final class ProjectStatus
{
    public const ACTIVE = 'active';
    public const COMPLETED = 'completed';
    public const ARCHIVED = 'archived';

    private function __construct() {}

    public static function getValues(): array
    {
        return [self::ACTIVE, self::COMPLETED, self::ARCHIVED];
    }
}
