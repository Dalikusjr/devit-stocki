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
require_once "../../includes/classes/historic.php";

// Connexion à la base
$db = (new Database())->connect();
$historic = new Historic($db);

$id = intval($_POST['id']);
try {
    // Récupération de tous les services
    $rows = $historic->getAllById($id);
    // Retour JSON
    echo json_encode([
        'data' => $rows
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
