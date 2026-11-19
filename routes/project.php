<?php

use App\Controllers\ProjectController;

$router->get('/project', [
    ProjectController::class,
    'getProjects'
]);
$router->post('/project/create', [
    ProjectController::class,
    'createProject'
]);

$router->post('/project/installment', [
    ProjectController::class,
    'getInstallments'
]);

$router->post('/project/client', [
    ProjectController::class,
    'getClientForAutoComplete'
]);

$router->patch('/project/update', [
    ProjectController::class,
    'updateProject'
]);
$router->patch('/project/archive', [
    ProjectController::class,
    'archiveProject'
]);
$router->delete('/project/delete', [
    ProjectController::class,
    'deleteProject'
]);
