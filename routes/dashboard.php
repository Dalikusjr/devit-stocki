<?php

use App\Controllers\DashboardController;

$router->get('/dashboard', [
    DashboardController::class,
    'getDashboard'
]);
