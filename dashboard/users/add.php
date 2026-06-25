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
require_once "../../includes/classes/users.php";


// Connexion à la base
$db = (new Database())->connect();
$users = new Users($db);

// Ajoute le patient
$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$role = trim($_POST['role']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$tel = trim($_POST['tel']);
try {
    $added = $users->add($email, $password, $nom, $prenom, $tel, $role);
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur ajouté avec succès'
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé par un autre utilisateur.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Impossible d\'ajouter l\'utilisateur'
        ]);
    }
}
