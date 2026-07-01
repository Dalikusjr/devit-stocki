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
require_once "../../includes/classes/clients.php";
require_once "../../includes/classes/payments.php";


// Connexion à la base
$db = (new Database())->connect();
$projects = new Projects($db);
$clients = new Clients($db);


// Ajoute le patient
$titre = trim($_POST['titre']);
$clnom = trim($_POST['clnom']);
$email = trim($_POST['email']);
$adresse = trim($_POST['adresse']);
$cltel = trim($_POST['tel']);
$datedebut = trim($_POST['datedebut']);
$daterenouv = trim($_POST['daterenouv']);
$couttot = trim($_POST['couttot']);
$avancepaye = trim($_POST['avancepaye']);
$comment = trim($_POST['comment'] );
$acces = trim($_POST['acces'] );
try {
    $client = $clients->getByEmail($email);
    if ($client) {
        $idclient = $client['idclient'];
    } else {
        $idclient = $clients->add($email, $clnom, $cltel, $adresse);
        $idclient = $db->lastInsertId();
    }
    $added = $projects->add($titre, $datedebut, $daterenouv, $couttot, $acces, $idclient);
    $idproj = $db->lastInsertId();
    if ($avancepaye > 0) {
        $dateech = date('Y-m-d');
        $datepay = date('Y-m-d');
        $status = "payer";
        $payments = new Payments($db);
        $payments->add($avancepaye, $comment, $dateech, $status, $idproj,$datepay);
    }
    echo json_encode([
        'success' => true,
        'message' => 'Projet ajouté avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible d\'ajouter le projet'.$e->getMessage()
    ]);
}
