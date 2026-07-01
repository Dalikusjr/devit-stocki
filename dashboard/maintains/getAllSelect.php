<?php
// ==========================
// TRAITEMENT PHP / RÉCUPÉRATION DE TOUS LES SERVICES
// ==========================

// Affichage des erreurs pour debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Type de contenu JSON
header('Content-Type: application/json');

// Inclusion des classes Database et Service
require_once "../../includes/classes/db.php";
require_once "../../includes/classes/projects.php";

// Connexion à la base
$db = (new Database())->connect();
$projects = new Projects($db);
$search = $_GET['term'] ?? '';
// Récupération de tous les services
$rows = $projects->getAll();
if (!empty($search)) {
    $rows = array_filter($rows, function($item) use ($search) {
        return stripos($item['titre'], $search) !== false; // Cherche le terme dans le titre
    });
}
// Retour JSON
echo json_encode($rows);
