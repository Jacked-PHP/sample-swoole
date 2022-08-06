<?php

namespace MyCode\Bootstrap;

use DI\Container;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use MyCode\Commands\GenerateFactory;
use MyCode\Commands\GenerateJwtToken;
use MyCode\Commands\HttpServer;
use MyCode\Commands\Migrate;
use MyCode\Commands\Seed;
use MyCode\Commands\WebSocketServer;
use MyCode\Events\EventInterface;
use MyCode\Events\UserLogin;
use MyCode\Events\UserLoginFail;
use MyCode\Events\UserLogout;
use MyCode\Services\Events;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;
use Symfony\Component\Console\Application;

class App
{
    public static function start()
    {
        $app = App::prepareSlimApp();

        Dependencies::start($app);
        self::registerEvents($app);
        self::registerRoutes($app);

        self::processCommands();
    }

    public static function registerRoutes(SlimApp $app)
    {
        (require ROOT_DIR . '/src/routes.php')($app);

        $app->group('/api', function(RouteCollectorProxy $group) {
            (require ROOT_DIR . '/src/api-routes.php')($group);
        });
    }

    private static function prepareSlimApp(): SlimApp
    {
        global $app, $requestConverter;

        $psr17Factory = new Psr17Factory;
        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );
        $app = new SlimApp($psr17Factory, new Container());
        $app->addRoutingMiddleware();
        return $app;
    }

    private static function processCommands(): void
    {
        $application = new Application();

        $application->add(new HttpServer);
        $application->add(new WebSocketServer);
        $application->add(new Migrate);
        $application->add(new Seed);
        $application->add(new GenerateJwtToken);
        $application->add(new GenerateFactory);

        $application->run();
    }

    private static function registerEvents(SlimApp $app)
    {
        $container = $app->getContainer();

        Events::addListener(UserLogin::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('User successful login: ' . $event->user->name);
        });

        Events::addListener(UserLogout::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('User logout: ' . $event->user->name);
        });

        Events::addListener(UserLoginFail::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('Login attempt fail: ' . $event->email);
        });
    }
}