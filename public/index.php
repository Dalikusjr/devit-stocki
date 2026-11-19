<?php

require_once "../config.php";

use App\Helpers\Helpers;
// ==========================
// PHP INITIALISATION
// ==========================
session_start();

// Vérification de l'authentification
if (!Helpers::isUserLoggedIn()) {
    Helpers::redirect('login');
}

// …/public/ ou …/public/index.php : adresse propre du tableau de bord
if (!isset($_GET['page'])) {
    Helpers::redirect('p-dashboard');
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
    'listeprojects',
    'stats',
    'profil',
    'parametres'
];

// La page "paiments" se trouve dans le dossier "payments"
$dossiers = ['paiments' => 'payments'];
$fichierPage = __DIR__ . '/' . ($dossiers[$page] ?? $page) . "/$page.php";

// Pages réservées aux administrateurs
$pagesAdmin = ['users'];

// Le script de la page (assets/js/<page>.js) n'est chargé que si la page est affichée
$pageScript = false;
if (in_array($page, $pagesAdmin) && !Helpers::isAdmin()) {
    include __DIR__ . "/error/403.php";
} elseif (in_array($page, $pagesAutorisees) && is_file($fichierPage)) {
    include $fichierPage;
    $pageScript = true;
} else {
    include __DIR__ . "/error/404.php";
}

// ==========================
// FOOTER & MODALS
// ==========================
include __DIR__ . '/main/modals.php';
include __DIR__ . '/main/footer.php';
