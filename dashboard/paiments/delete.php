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
require_once "../../includes/classes/payments.php";


// Connexion à la base
$db = (new Database())->connect();
$payments = new Payments($db);

// Supprime le client
$idpay = intval($_POST['dueToDelete'] ?? 0);
$idproj = intval($_POST['idproject'] ?? 0);
try {
    $deleted = $payments->delete($idpay);
    if ($deleted) {
        $reste = $payments->calculerResteProjet($idproj);
        $stats_tranches = $payments->countTranches($idproj);
        echo json_encode([
            'success' => true,
            'tranches_paye' => $stats_tranches['tranches_paye'],
            'tranches_total' => $stats_tranches['tranches_total'],
            'reste' => $reste,
            'message' => 'Paiement supprimé avec succès'
        ]);
    } else {
        throw new Exception("Le paiement n'existe pas ou n'a pas pu être supprimé.");
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
