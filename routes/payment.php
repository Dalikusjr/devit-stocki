<?php

use App\Controllers\PaymentController;

$router->get('/payment/clients', [
    PaymentController::class,
    'getClientsForPaymentDashboard'
]);
$router->get('/payment/projects', [
    PaymentController::class,
    'getProjectsByClient'
]);
$router->get('/payment/installment', [
    PaymentController::class,
    'getPayment'
]);
$router->post('/payment/create', [
    PaymentController::class,
    'createPayment'
]);
$router->patch('/payment/update', [
    PaymentController::class,
    'updatePayment'
]);
$router->delete('/payment/delete', [
    PaymentController::class,
    'deletePayment'
]);
