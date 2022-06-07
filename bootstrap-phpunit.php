<?php

use Dotenv\Dotenv;

include_once __DIR__ . '/vendor/autoload.php';

const ROOT_DIR = __DIR__;

global $app, $application;

$dotenv = Dotenv::createImmutable(__DIR__, '.env.testing');
$dotenv->load();