<?php
// erreurs journalisées dans logs/error.log (config.php), jamais affichées : la réponse reste du JSON propre
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once '../../config.php';

use App\Router\Router;
use App\Helpers\Helpers;
use App\Helpers\Response;
use App\Exceptions\RouteException;

// L'API n'est accessible qu'aux utilisateurs connectés
session_start();
if (!Helpers::isUserLoggedIn()) {
    http_response_code(401);
    Response::failed(null, 'Session expirée, veuillez vous reconnecter.');
}
session_write_close();

$router = new Router();

require '../../routes/api.php';

try {
    $router->dispatch();
} catch (RouteException $e) {
    http_response_code(404);
    Response::failed(null, 'Lien invalide');
}
