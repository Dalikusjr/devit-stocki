<?php

namespace App\Validators;

use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;

class Validator
{
    public static function validateOperation(mixed $queryRes, string $msg = 'Opération non réussie'): void
    {
        if (!$queryRes) {
            throw DatabaseException::operationFailed($msg);
        }
    }

    public static function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::emailInvalid();
        }
    }

    public static function validatePassword(string $password): void
    {
        if (strlen($password) < 6) {
            throw ValidationException::password();
        }
    }

    // Règles d'un nouveau mot de passe : 8 caractères, une minuscule, un chiffre, symbole ou espace
    public static function validateNewPassword(string $password): void
    {
        if (mb_strlen($password) < 8 || !preg_match('/\p{Ll}/u', $password) || !preg_match('/[^\p{L}]/u', $password)) {
            throw ValidationException::invalidValue("Le mot de passe doit contenir au moins 8 caractères, dont une minuscule et un chiffre, un symbole ou un espace.");
        }
    }

    public static function validateRequiredFields(array $fields): void
    {
        foreach ($fields as $name => $field) {
            if (trim((string)$field) === '') {
                throw  ValidationException::required($name);
            }
        }
    }

    public static function validatePositiveNumbers(array $fields): void
    {
        foreach ($fields as $name => $field) {
            $value = trim((string) $field);
            if (!is_numeric($value) || (float)$value < 0) {
                throw ValidationException::invalidValue("Le champ \"$name\" doit contenir un nombre supérieur ou égal à 0.");
            }
        }
    }

    public static function validateDate(string $date, string $name): void
    {
        $d = \DateTime::createFromFormat('!Y-m-d', $date);
        if ($d === false || $d->format('Y-m-d') !== $date) {
            throw ValidationException::invalidValue("Le champ \"$name\" doit être une date valide.");
        }
    }

    public static function validateMaxLength(string $value, int $max, string $name): void
    {
        if (mb_strlen($value) > $max) {
            throw ValidationException::invalidValue("Le champ \"$name\" ne doit pas dépasser $max caractères.");
        }
    }

    public static function validateValidId(string $id): void
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            throw ValidationException::id();
        }
    }

    public static function validateMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper(trim($method))) {
            throw new \Exception("Méthode de requête non autorisée.");
        }
    }

    public static function validateValueInEnum(string $value,array $enum): void
    {
        if (!in_array($value,$enum,true)) {
            throw ValidationException::invalidValue("$value est invalide choix");
        }
    }
}
