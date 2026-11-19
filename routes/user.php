<?php

use App\Controllers\UserController;
$router->get('/user', [
    UserController::class,
    'getUsers'
]);
$router->post('/user/create', [
    UserController::class,
    'createUser'
]);
$router->patch('/user/update', [
    UserController::class,
    'updateUser'
]);
$router->delete('/user/delete', [
    UserController::class,
    'deleteUser'
]);
