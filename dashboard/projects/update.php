<?php
// ==========================
// TRAITEMENT PHP / LOGIQUE
// ==========================

// Affiche les erreurs pour debug
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Type de contenu JSON
header('Content-Type: application/json');

// Inclure classes Database et patient
require_once "../../includes/classes/db.php";
require_once "../../includes/classes/projects.php";


// Connexion à la base
$db = (new Database())->connect();
$projects = new Projects($db);

// Ajoute le patient
$idproj = trim($_POST['idproj']);
$titre = trim($_POST['titre']);
$datedebut = trim($_POST['datedebut']);
$daterenouv = trim($_POST['daterenouv']);
$couttot = trim($_POST['couttot']);
$acces = trim($_POST['acces'] );
try {
    $added = $projects->update($idproj,$titre, $datedebut, $daterenouv, $couttot, $acces);
    echo json_encode([
        'success' => true,
        'message' => 'Projet modifié avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifié le projet'.$e->getMessage()
    ]);
}
