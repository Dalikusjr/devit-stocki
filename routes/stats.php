<?php

use App\Controllers\StatsController;

$router->get('/stats', [
    StatsController::class,
    'getStats'
]);
