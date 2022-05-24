<?php

namespace MyCode\Bootstrap;

use DI\Container;
use Exception;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use MyCode\DB\Migration;
use MyCode\DB\Models\User;
use MyCode\DB\Seed;
use MyCode\Events\EventInterface;
use MyCode\Events\UserLogin;
use MyCode\Events\UserLoginFail;
use MyCode\Events\UserLogout;
use MyCode\Services\Events;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App as SlimApp;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

class App
{
    public static function start()
    {
        [$app, $requestConverter] = App::prepareSlimApp();

        Dependencies::start($app);
        self::registerEvents($app);

        (require ROOT_DIR . '/src/routes.php')($app);

        if (self::processCommands($app)) {
            return;
        }

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

    private static function processCommands(SlimApp $app): bool
    {
        $input = self::getConsoleInput();

        switch ($input->getArgument('action')) {

            case 'migrate':
                Migration::handle($app, $input);
                return true;

            case 'seed':
                Seed::handle($app);
                return true;
        }

        return false;
    }

    private static function getConsoleInput(): InputInterface
    {
        global $argv;

        $output = new ConsoleOutput;

        $definition = new InputDefinition([
            new InputArgument('action', InputArgument::OPTIONAL, 'Action to be taken.'),
            new InputOption('fresh', null, InputOption::VALUE_NONE, 'Make migration running fresh', null),
        ]);

        try {
            return new ArgvInput($argv, $definition);
        } catch (Exception $e) {
            $output->writeln('');
            $output->writeln('<error>There was an error while starting application: ' . $e->getMessage() . '</error>');
            $output->writeln('');
            exit(1);
        }
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