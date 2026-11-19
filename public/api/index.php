<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php';

use App\Router\Router;

$router = new Router();

require '../../routes/api.php';

$router->dispatch();