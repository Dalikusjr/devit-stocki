<?php

require_once "../../config.php";


use App\Config\Auth;
use App\Config\Database;
use App\Helpers\Helpers;
use App\Logging\Logger;
use App\Validators\Validator;

try {
    Validator::validateMethod('POST');
    $db = Database::getInstance()->getConnection();
    $authRepo = new Auth($db);
    $authRepo->logout();
} catch (\Throwable $e) {
    Logger::logError($e);
}
// retour à la page de connexion dans tous les cas
Helpers::redirect("../login");
