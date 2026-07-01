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

$id = intval($_POST['id']);
$idproj  = intval($_POST['idproj']);
$idclient = intval($_POST['idclient']);
$montant = floatval($_POST['montant']);
$status  = trim($_POST['status'] ?? 'inpayer');

try {

    $success = $maintains->update($id, $idproj, $idclient, $montant, $status);
    if ($success) {
        $historic = new Historic($db);
        if ($status === 'payer') {
            $historic->add($id, $montant, 'maintenance', $idclient);
        } else {
            $historic->deleteByType($id,'maintenance');
        }
    }
    // Retour JSON
    echo json_encode([
        'success' => true,
        'message' => 'Abonnement modifiée avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifiée l\'abonnement' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
