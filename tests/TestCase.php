<?php

namespace Tests;

use DI\Container;
use MyCode\Bootstrap\Dependencies;
use MyCode\Commands\GenerateJwtToken;
use MyCode\Commands\Migrate;
use MyCode\Commands\Seed;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use MyCode\Bootstrap\App as BootstrapApp;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $this->prepareApplicationCommands();

        // migration
        $this->runCommand('migrate', ['--fresh' => null, '--quiet' => null]);
        $this->runCommand('seed', ['--quiet' => null]);

        return $app;
    }

    public function prepareApplicationCommands()
    {
        global $application;

        $application = new Application();
        $application->add(new Migrate);
        $application->add(new Seed);
        $application->add(new GenerateJwtToken);
    }

    public function runCommand(string $commandName, $args = [])
    {
        global $application;

        $command = $application->find($commandName);
        $greetInput = new ArrayInput($args);
        $output = new ConsoleOutput;
        $command->run($greetInput, $output);
    }
}
