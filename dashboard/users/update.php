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
require_once '../../includes/classes/users.php';
require_once '../../includes/classes/functions.php';


// Connexion à la base
$db = (new Database())->connect();
$users = new Users($db);

// Mise à jour du service
$id = trim($_POST['id']);
$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$role = trim($_POST['role']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$tel = trim($_POST['tel']);
try{
$updated = $users->update($id, $email, $password, $nom, $prenom, $tel, $role);

// Retour JSON
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur modifié avec succès'
    ]);
}catch(PDOException $e){
    if($e->getCode() == 23000){
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé par un autre utilisateur.'
        ]);
    }else{
        echo json_encode([
            'success' => false,
            'message' => 'Impossible de modifier l\'utilisateur'
        ]);
    }
}
