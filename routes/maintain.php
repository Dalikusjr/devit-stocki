<?php

use App\Controllers\MaintainController;
$router->get('/maintain', [
    MaintainController::class,
    'getMaintains'
]);
$router->get('/maintain/autoComplete', [
    MaintainController::class,
    'getProjectsForAutoComplete'
]);
$router->get('/maintain/history', [
    MaintainController::class,
    'getMaintainHistorics'
]);
$router->post('/maintain/create', [
    MaintainController::class,
    'createMaintain'
]);
$router->patch('/maintain/update', [
    MaintainController::class,
    'updateMaintain'
]);
$router->patch('/maintain/updatestatus', [
    MaintainController::class,
    'updateMaintainStatus'
]);
$router->delete('/maintain/delete', [
    MaintainController::class,
    'deleteMaintain'
]);
