<?php

use App\Controllers\StockiController;

$router->get('/stocki', [
    StockiController::class,
    'getStockis'
]);
$router->get('/stocki/clients', [
    StockiController::class,
    'getClientsForAutoComplete'
]);
$router->get('/stocki/history', [
    StockiController::class,
    'getStockiHistorics'
]);
$router->post('/stocki/create', [
    StockiController::class,
    'createStocki'
]);
$router->patch('/stocki/update', [
    StockiController::class,
    'updateStocki'
]);
$router->patch('/stocki/updatestatus', [
    StockiController::class,
    'updateStockiStatus'
]);
$router->delete('/stocki/delete', [
    StockiController::class,
    'deleteStocki'
]);
