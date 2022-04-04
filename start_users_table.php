<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MyCode\DB\User;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$user = new User;
if ($user->createTable()) {
    echo "DB table created suiccessfully!" . PHP_EOL;
    exit;
}

echo "Failed to create DB table!" . PHP_EOL;