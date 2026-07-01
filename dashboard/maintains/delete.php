<?php
// ==========================
// TRAITEMENT PHP / SUPPRESSION SERVICE
// ==========================

// Affiche les erreurs pour debug
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Type de contenu JSON
header('Content-Type: application/json');

// Inclure classes Database et Patient
require_once "../../includes/classes/db.php";
require_once "../../includes/classes/maintains.php";


// Connexion à la base
$db = (new Database())->connect();
$maintains = new Maintains($db);

// Supprime l'abonnement
$id = trim($_POST['abonnToDelete']);
try {
    $maintains->delete($id);
    echo json_encode([
        'success' => true,
        'message' => 'Abonnement supprimé avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer cet abonnement : ' . $e->getMessage()
    ]);
}
