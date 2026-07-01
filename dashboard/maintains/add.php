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
require_once '../../includes/classes/maintains.php';
require_once '../../includes/classes/historic.php';


// Connexion à la base
$db = (new Database())->connect();
$maintains = new Maintains($db);


$idproj  = intval($_POST['idproj']);
$idclient = intval($_POST['idclient']);
$montant = floatval($_POST['montant']);
$status  = trim($_POST['status'] ?? 'inpayer');

try {

    $success=$maintains->add($idproj, $idclient, $montant, $status);
    
    if($success && $status==='payer'){
        $id_pay = $db->lastInsertId();
        $historic = new Historic($db);
        $historic->add($id_pay, 'maintenance' , $idclient);
    }
    echo json_encode([
        'success' => true,
        'message' => 'Abonnement ajoutée avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible d\'ajouter l\'abonnement'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
