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
$email = trim($_POST['emailtmp']);
try {
    $client = $projects->getClient($email);
    if ($client) {
        echo json_encode([
            'success' => true,
            'nom' => $client['nom'],
            'tel' => $client['tel'],
            'adresse' => $client['adresse']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Client introuvable'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur technique : '.$e->getMessage()
    ]);
}
