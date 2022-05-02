<?php

namespace MyCode\Bootstrap;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\App as SlimApp;

class Dependencies
{
    public static function start(SlimApp $app)
    {
        self::registerLogger($app);
    }

    private static function registerLogger(SlimApp $app)
    {
        $app->getContainer()->set('logger', function() {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'], Logger::DEBUG));
            return $logger;
        });
    }
}