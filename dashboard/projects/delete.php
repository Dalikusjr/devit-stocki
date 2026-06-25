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
require_once "../../includes/classes/projects.php";


// Connexion à la base
$db = (new Database())->connect();
$projects = new Projects($db);

// Supprime le patient
$id = trim($_POST['projToDelete']);
try{
$deleted = $projects->delete($id);
    echo json_encode([
        'success' => true,
        'message' => 'Projet supprimé avec succès'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer le projet'
    ]);
}
