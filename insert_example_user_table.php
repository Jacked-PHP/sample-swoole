<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MyCode\DB\User;

global $argv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$name = 'Savio';
if ($argv[1]) {
    $name = $argv[1];
}

$email = 'savio@example.com';
if ($argv[2]) {
    $email = $argv[2];
}

$user = new User;
$result = $user->insert([
    'name' => $name,
    'email' => $email,
    'password' => 'secret',
]);
if ($result) {
    echo "Record inserted suiccessfully!" . PHP_EOL;
    exit;
}

echo "Failed to insert record!" . PHP_EOL;