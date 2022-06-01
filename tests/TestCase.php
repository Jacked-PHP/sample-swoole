<?php

namespace Tests;

use DI\Container;
use MyCode\Bootstrap\Dependencies;
use MyCode\DB\Migration;
use MyCode\DB\Seed;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use MyCode\Bootstrap\App as BootstrapApp;

class TestCase extends BaseTestCase
{
    public function getApp(): App
    {
        global $app;
        $psr17Factory = new Psr17Factory;
        $app = new App($psr17Factory, new Container());
        $app->addRoutingMiddleware();
        Dependencies::start($app);
        BootstrapApp::registerRoutes($app);

        // migration
        Migration::handle($app, true);
        Seed::handle($app);

        return $app;
    }
}
