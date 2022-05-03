<?php

namespace MyCode\Bootstrap;

use Illuminate\Database\Capsule\Manager as DbCapsule;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\App as SlimApp;

class Dependencies
{
    public static function start(SlimApp $app)
    {
        self::registerLogger($app);
        self::registerDbCapsule($app);
    }

    private static function registerLogger(SlimApp $app)
    {
        $app->getContainer()->set('logger', function() {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'], Logger::DEBUG));
            return $logger;
        });
    }

    private static function registerDbCapsule(SlimApp $app)
    {
        $container = $app->getContainer();

        $container->set('db', function () {
            $capsule = new DbCapsule;
            $capsule->addConnection([
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
                'collation' => $_ENV['DB_COLLATION'] ?? 'utf8_unicode_ci',
                'prefix' => $_ENV['DB_PREFIX'] ?? '',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        });

        // start db
        $container->get('db');
    }
}