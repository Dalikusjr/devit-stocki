<?php
// ======================
// FONCTIONS UTILES
// ======================

/**
 * Redirige vers une URL donnée et arrête l'exécution du script.
 *
 * 
 */
function redirect($url)
{
    header("Location: $url");
    exit();
}

/**
 * Vérifie si l'utilisateur est connecté.
 *
 * 
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function formatDateForMySQL($date) {
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
