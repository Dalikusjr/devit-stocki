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
require_once "../../includes/classes/clients.php";


// Connexion à la base
$db = (new Database())->connect();
$clients = new Clients($db);

// Ajoute le patient
$nom = trim($_POST['nom']);
$adresse = trim($_POST['adresse']);
$email = trim($_POST['email']);
$tel = trim($_POST['tel']);
try {
    $added = $clients->add($email, $adresse, $nom, $tel);
    echo json_encode([
        'success' => true,
        'message' => 'Client ajouté avec succès'
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé par un autre client.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Impossible d\'ajouter le client'
        ]);
    }
}
