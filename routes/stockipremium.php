<?php

use App\Controllers\StockiPremiumController;

$router->get('/stockipremium', [
    StockiPremiumController::class,
    'getStockiPremiums'
]);
$router->get('/stockipremium/clients', [
    StockiPremiumController::class,
    'getClientsForAutoComplete'
]);
$router->get('/stockipremium/history', [
    StockiPremiumController::class,
    'getStockiPremiumHistorics'
]);
$router->get('/stockipremium/installments', [
    StockiPremiumController::class,
    'getInstallments'
]);
$router->post('/stockipremium/create', [
    StockiPremiumController::class,
    'createStockiPremium'
]);
$router->patch('/stockipremium/update', [
    StockiPremiumController::class,
    'updateStockiPremium'
]);
$router->patch('/stockipremium/pay', [
    StockiPremiumController::class,
    'payInstallment'
]);
$router->delete('/stockipremium/delete', [
    StockiPremiumController::class,
    'deleteStockiPremium'
]);
