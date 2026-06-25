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
require_once "../../includes/classes/clients.php";
require_once "../../includes/classes/payments.php";

// Connexion à la base
$db = (new Database())->connect();
$clients = new Clients($db);
$payments = new Payments($db);

// Récupération de tous les services
$rows = $clients->getAll();
$counts = $payments->countProjPerClient();
$filteredRows = []; // Nouveau tableau pour stocker les clients avec projets

foreach($rows as $client){
    $nb = $counts[$client['idclient']] ?? 0;
    
    if ($nb > 0) {
        $client['nbprojects'] = $nb;
        $filteredRows[] = $client; // On n'ajoute que si nb > 0
    }
}

// Retour JSON
echo json_encode([
    "data" => $filteredRows
]);
