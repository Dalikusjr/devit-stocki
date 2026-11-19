<?php

require_once "../config.php";

use App\Helpers\Helpers;
// ==========================
// PHP INITIALISATION
// ==========================
session_start();

// Vérification de l'authentification
if (!Helpers::isUserLoggedIn()) {
    Helpers::redirect('auth/auth.php');
}

// ==========================
// INCLUDES PRINCIPAUX
// ==========================
include __DIR__ . '/main/header.php';

// ==========================
// GESTION DES PAGES
// ==========================
$page = $_GET['page'] ?? 'dashboard';

$pagesAutorisees = [
    'dashboard',
    'projects',
    'paiments',
    'maintains',
    'abonn',
    'abonnvie',
    'users',
    'clients',
    'listeprojects'
];

if (in_array($page, $pagesAutorisees)) {
    include __DIR__ . "/$page/$page.php";
} else {
    include __DIR__ . "/error/404.php";
}

// ==========================
// FOOTER & MODALS
// ==========================
include __DIR__ . '/main/modals.php';
include __DIR__ . '/main/footer.php';
