<?php

namespace App\Helpers;

use DateTime;

class Helpers
{
    public static function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // Administrateur = utilisateur connecté avec users.is_admin à 1 (lu en base : un retrait de droits s'applique tout de suite)
    public static function isAdmin(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        if (!self::isUserLoggedIn()) {
            return $cache = false;
        }
        $db = \App\Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE iduser=:id LIMIT 1");
        $stmt->execute([':id' => (int) $_SESSION['user_id']]);
        return $cache = (bool) $stmt->fetchColumn();
    }

    // Utilisateur connecté (sans le mot de passe), lu en base une fois par requête
    public static function currentUser(): ?array
    {
        static $user = false;
        if ($user !== false) {
            return $user;
        }
        if (!self::isUserLoggedIn()) {
            return $user = null;
        }
        $db = \App\Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT iduser, email, nom, prenom, role, tel, is_admin FROM users WHERE iduser=:id LIMIT 1");
        $stmt->execute([':id' => (int) $_SESSION['user_id']]);
        return $user = ($stmt->fetch(\PDO::FETCH_ASSOC) ?: null);
    }

    // Initiales pour l'avatar : « Mohamed ali Bettaieb » -> « MB »
    public static function initials(?array $user): string
    {
        if (!$user) {
            return '?';
        }
        $first = mb_substr(trim($user['prenom'] ?? ''), 0, 1);
        $last = mb_substr(trim($user['nom'] ?? ''), 0, 1);
        return mb_strtoupper(($first . $last) ?: '?');
    }

    public static function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    public static function monthly(): array
    {
        return [
            'name'  => 'mensuelle',
            'start' => date('Y-m-01 00:00:00'),
            'end'   => date('Y-m-01 00:00:00', strtotime('first day of next month')),
        ];
    }

    public static function formatDateForMySQL($date)
    {
        // Tente de créer un objet DateTime à partir de plusieurs formats possibles
        $formats = ["d/m/Y", "d-m-Y", "Y/m/d", "Y-m-d"];
        foreach ($formats as $fmt) {
            $d = DateTime::createFromFormat($fmt, $date);
            if ($d !== false) {
                return $d->format("Y-m-d"); // Format MySQL
            }
        }
        // Si aucun format valide
        return false;
    }
}
