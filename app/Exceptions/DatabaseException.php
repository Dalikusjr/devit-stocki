<?php

namespace App\Exceptions;

class DatabaseException extends \Exception
{
    public static function operationFailed(string $msg="Failure d'opération"): self
    {
        return new self($msg);
    }
}
