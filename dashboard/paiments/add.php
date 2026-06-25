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
require_once '../../includes/classes/payments.php';


// Connexion à la base
$db = (new Database())->connect();
$payments = new Payments($db);

// Mise à jour du service
$idproj  = intval($_POST['idproject']);
$idpay   = 0;
$comment = trim($_POST['comment'] ?? '');
$montant = floatval($_POST['montant']);
$status  = trim($_POST['status'] ?? 'inpayer');
$dateech = trim($_POST['dateech'] ?? '');
$datepay = !empty($_POST['datepay']) ? trim($_POST['datepay']) : null;
try {
    if ($payments->estMontantExcedant($idproj, $idpay, $montant)) {
        throw new Exception("Le total des paiements est supérieur au cout du projet.");
    }
    $added = $payments->add($montant, $comment, $dateech, $status, $idproj, $datepay);
    $idpay = $db->lastInsertId();

    if ($added) {
        $tranche = $payments->getPay($idpay);
        $tranchesStats = $payments->countTranches($idproj);
        $tranche["tranches_paye"] = $tranchesStats["tranches_paye"];
        $tranche["tranches_total"] = $tranchesStats["tranches_total"];
        $tranche['reste'] = $payments->calculerResteProjet($idproj);
        // Retour JSON
        echo json_encode([
            'success' => true,
            'tranche' => $tranche,
            'message' => 'Tranche ajoutée avec succès'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible d\'ajouter la Tranche'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
