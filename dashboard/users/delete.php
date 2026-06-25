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
require_once "../../includes/classes/users.php";


// Connexion à la base
$db = (new Database())->connect();
$users = new Users($db);

// Supprime le patient
$id = trim($_POST['userToDelete']);
$deleted = $users->delete($id);

// Retour JSON
if ($deleted) {
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer l\'utilisateur'
    ]);
}
