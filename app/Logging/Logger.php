<?php

namespace App\Logging;

class Logger
{
    public static function logError(\Throwable $e): void
    {
        error_log(sprintf(
            "[%s] %s in %s:%d",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }
    public static function logData(mixed $data): void
    {
        error_log(sprintf(
            $data
        ));
    }
}
