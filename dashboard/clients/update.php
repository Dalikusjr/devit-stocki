<?php
// ==========================
// TRAITEMENT PHP / MISE À JOUR D’UN SERVICE
// ==========================

// Affichage des erreurs pour debug
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Type de contenu JSON
header('Content-Type: application/json');

// Inclusion des classes Database et Service
require_once '../../includes/classes/db.php';
require_once '../../includes/classes/clients.php';
require_once '../../includes/classes/functions.php';


// Connexion à la base
$db = (new Database())->connect();
$clients = new Clients($db);

// Mise à jour du service
$id = trim($_POST['id']);
$nom = trim($_POST['nom']);
$adresse = trim($_POST['adresse']);
$email = trim($_POST['email']);
$tel = trim($_POST['tel']);
try {
    $updated = $clients->update($id, $email, $nom, $tel, $adresse);

    // Retour JSON
    echo json_encode([
        'success' => true,
        'message' => 'Client modifié avec succès'
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé par un autre Client.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Impossible de modifier le client'
        ]);
    }
}
