<?php

const ROOT_DIR = __DIR__;

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MyCode\Bootstrap\App;

global $app, $requestConverter;

// --------------------------------------
// Environment Variables
// --------------------------------------

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --------------------------------------
// OpenSwoole
// --------------------------------------

App::start();
