<?php

require_once "../../config.php";

use App\Config\Database;
use App\Config\Auth;
use App\Validators\Validator;
use App\Helpers\Helpers;
use App\Exceptions\ValidationException;
use App\Logging\Logger;

session_start();

try {
    Validator::validateMethod('POST');

    $db = Database::getInstance()->getConnection();

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    Validator::validateRequiredFields([
        'Email' => $email,
        'Mot de passe' => $password
    ]);

    Validator::validateEmail($email);
    Validator::validatePassword($password);

    $authRepo = new Auth($db);
    $user = $authRepo->login($email, $password);

    if (!$user) {
        throw new \Exception("Email ou mot de passe incorrect.");
    }

    session_regenerate_id(true);
    $_SESSION['username'] = $user['prenom'];
    $_SESSION['user_id'] = $user['iduser'];
    $_SESSION['role'] = $user['role'];
    Helpers::redirect("../p-dashboard");
} catch (\Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
    // l'e-mail saisi est réaffiché (jamais le mot de passe)
    $_SESSION['old_email'] = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 100);
    Logger::logError($e);
    Helpers::redirect("../login");
}
