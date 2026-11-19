<?php

use App\Controllers\ClientController;
$router->get('/client', [
    ClientController::class,
    'getClients'
]);
$router->post('/client/create', [
    ClientController::class,
    'createClient'
]);
$router->patch('/client/update', [
    ClientController::class,
    'updateClient'
]);
$router->delete('/client/delete', [
    ClientController::class,
    'deleteClient'
]);
