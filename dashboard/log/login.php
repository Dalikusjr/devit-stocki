<?php
// ==========================
// PHP INITIALISATION / LOGIQUE
// ==========================
session_start();
require_once "../../includes/classes/db.php";
require_once "../../includes/classes/login.php";
require_once "../../includes/classes/functions.php";

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = (new Database())->connect();
    $login = new Login($db);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $user = $login->login($email, $password);

    if ($user) {
        $_SESSION['username'] = $user['prenom'];
        redirect("../dashboard.php");
    } else {
        $_SESSION['error'] = "E-mail ou mot de passe incorrect.";
        redirect("../../index.php");
    }
}
