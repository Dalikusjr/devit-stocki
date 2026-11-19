<?php
ini_set('log_errors', 1);
ini_set('display_errors', 0); // production : erreurs dans logs/error.log uniquement
ini_set('error_log', __DIR__ . '/logs/error.log');

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();