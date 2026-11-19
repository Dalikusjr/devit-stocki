<?php

use App\Controllers\ProfileController;

$router->get('/profile', [
    ProfileController::class,
    'getProfile'
]);
$router->patch('/profile/update', [
    ProfileController::class,
    'updateProfile'
]);
$router->patch('/profile/password', [
    ProfileController::class,
    'changePassword'
]);
