<?php

namespace Tests;

use DI\Container;
use League\Flysystem\Filesystem;
use Mockery;
use MyCode\Bootstrap\Dependencies;
use MyCode\Commands\GenerateFactory;
use MyCode\Commands\GenerateJwtToken;
use MyCode\Commands\Migrate;
use MyCode\Commands\Seed;
use MyCode\Services\Session;
use MyCode\Services\SessionTable;
use Nekofar\Slim\Test\TestResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use MyCode\Bootstrap\App as BootstrapApp;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

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
        $application->add(new GenerateFactory);
    }

    public function runCommand(string $commandName, $args = []): CommandTester
    {
        global $application;

        $command = $application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($args);
        return $tester;
    }

    public function getSessionCookieFromResponse(TestResponse $response): array
    {
        $cookie = current($response->getHeader('Set-Cookie'));
        parse_str($cookie, $parsedCookie);
        $parsedCookie = current(explode(';', current($parsedCookie)));
        $parsedCookie = Session::parseCookie($parsedCookie);
        return SessionTable::getInstance()->get($parsedCookie['id']);
    }

    public function getCookieParams(TestResponse $response): array
    {
        $parsedCookie = explode('=', current($response->getHeader('Set-Cookie')));
        $cookieKey = $parsedCookie[0];
        unset($parsedCookie[0]);
        $cookie = current(explode(';', $parsedCookie[1]));
        return [$cookieKey => $cookie];
    }

    public function mockFilesystem()
    {
        global $app;

        $container = $app->getContainer();

        $container->set('filesystem', function() {
            return Mockery::mock(Filesystem::class);
        });
    }
}
