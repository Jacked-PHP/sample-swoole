<?php

const ROOT_DIR = __DIR__;

require __DIR__ . '/vendor/autoload.php';

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MyCode\Http\Middlewares\SessionMiddleware;
use Slim\App;
use Dotenv\Dotenv;
use Slim\Routing\RouteCollectorProxy;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use MyCode\Http\Controllers\HomeController;
use MyCode\Http\Middlewares\CheckUsersExistenceMiddleware;

// --------------------------------------
// Environment Variables
// --------------------------------------

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --------------------------------------
// Slim App
// --------------------------------------

$psr17Factory = new Psr17Factory;
$requestConverter = new SwooleServerRequestConverter(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$app = new App($psr17Factory, new Container());
$app->addRoutingMiddleware();
$container = $app->getContainer();

// --------------------------------------
// Container
// --------------------------------------

$container->set('logger', function() {
    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/' . $_ENV['LOG_STORAGE'], Logger::DEBUG));
    return $logger;
});

// --------------------------------------
// Routes
// --------------------------------------

$app->get('/', HomeController::class . ':welcome');
$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('', HomeController::class . ':showUsers')->setName('show-users');
    $group->get('/{id:[0-9]+}', HomeController::class . ':showUser')->add(new 
        CheckUsersExistenceMiddleware)->setName('show-user');
})->add(new SessionMiddleware);

// --------------------------------------
// OpenSwoole
// --------------------------------------

$server = new Server("0.0.0.0", 8080);
$server->on("start", function(Server $server) {
    echo "HTTP Server ready at http://127.0.0.1:8080" . PHP_EOL;
});
$server->on('request', function(Request $request, Response $response) use ($app, $requestConverter) {
    $psr7Request = $requestConverter->createFromSwoole($request);
    $psr7Response = $app->handle($psr7Request);
    $converter = new SwooleResponseConverter($response);
    $converter->send($psr7Response);
});
$server->start();