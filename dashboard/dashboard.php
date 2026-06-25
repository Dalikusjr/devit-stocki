<?php
// ==========================
// PHP INITIALISATION
// ==========================
session_start();

require_once '../includes/classes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['username'])) {
    redirect('../index.php');
}

// ==========================
// INCLUDES PRINCIPAUX
// ==========================
include '../includes/header.php';

// ==========================
// GESTION DES PAGES
// ==========================
$page = $_GET['page'] ?? 'dash';

$pagesAutorisees = [
    'dash',
    'projects',
    'paiments',
    'maintain',
    'abonn',
    'abonnvie',
    'users',
    'clients',
    'listeprojects'
];

if (in_array($page, $pagesAutorisees)) {
    include __DIR__ . "/pages/$page.php";
} else {
    include __DIR__ . "/pages/404.php";
}

// ==========================
// FOOTER & MODALS
// ==========================
include '../includes/modals.php';
include '../includes/footer.php';


