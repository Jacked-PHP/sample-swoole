<?php

namespace MyCode\Bootstrap;

use DI\Container;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App as SlimApp;

class App
{
    public static function start()
    {
        [$app, $requestConverter] = App::prepareSlimApp();

        Dependencies::start($app);

        (require ROOT_DIR . '/src/routes.php')($app);

        SwooleServer::start($app, $requestConverter);
    }

    private static function prepareSlimApp()
    {
        global $app;

        $psr17Factory = new Psr17Factory;
        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );
        $app = new SlimApp($psr17Factory, new Container());
        $app->addRoutingMiddleware();
        return [$app, $requestConverter];
    }
}