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
require_once '../../includes/classes/db.php';
require_once "../../includes/classes/clients.php";


// Connexion à la base
$db = (new Database())->connect();
$clients = new Clients($db);

// Supprime le client
$id = trim($_POST['clientToDelete']);
try {
    $deleted = $clients->delete($id);
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer le client'
    ]);
}
