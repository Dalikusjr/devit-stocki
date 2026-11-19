<?php

namespace App\Exceptions;

class RouteException extends \Exception
{
    public static function RouteNotFoundException(string $msg="Lien invalide"): self
    {
        return new self($msg);
    }
}
