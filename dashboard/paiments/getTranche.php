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
require_once "../../includes/classes/payments.php";


// Connexion à la base
$db = (new Database())->connect();
$payments = new Payments($db);

// Mise à jour du service
$idpay = intval($_POST['editingTranche']??0);
try {
    $row = $payments->getPay($idpay);
    // Retour JSON
    echo json_encode([
        "success" => true,
        "data" => $row
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
