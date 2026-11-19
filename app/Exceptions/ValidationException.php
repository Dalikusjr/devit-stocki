<?php

namespace App\Exceptions;

class ValidationException extends \Exception
{
    public static function required(string $field): self
    {
        return new self("Le champ '{$field}' est obligatoire.");
    }

    public static function invalidValue(string $msg="Valeur Inavlid"): self
    {
        return new self($msg);
    }

    public static function emailInvalid(): self
    {
        return new self("Adresse email invalide.");
    }


    public static function emailExist(): self
    {
        return new self("Adresse email déja existe.");
    }

    public static function amountExceeded(): self
    {
        return new self("Le total des paiements ne doit pas dépasser le coût du projet.");
    }

    public static function password(): self
    {
        return new self("Le mot de passe doit contenir au moins 6 caractères.");
    }

    public static function id(): self
    {
        return new self("L'ID est invalide.");
    }

    public static function NotAllowed(string $msg = "Non Aloué"): self
    {
        return new self($msg);
    }

    public static function NotFound(string $msg = "Introuvable"): self
    {
        return new self($msg);
    }
}
