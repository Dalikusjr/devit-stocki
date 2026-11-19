<?php

namespace App\Helpers;

use DateTime;

class Helpers
{
    public static function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
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
