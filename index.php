<?php

const ROOT_DIR = __DIR__;

require __DIR__ . '/vendor/autoload.php';

use Slim\App;
use Dotenv\Dotenv;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use MyCode\Http\Controllers\HomeController;
use MyCode\Http\Middlewares\CheckUsersExistenceMiddleware;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$psr17Factory = new Psr17Factory;
$requestConverter = new SwooleServerRequestConverter(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$app = new App($psr17Factory);
$app->addRoutingMiddleware();
$app->get('/', HomeController::class . ':welcome');
$app->get('/users', HomeController::class . ':showUsers');
$app->get('/user/{id}', HomeController::class . ':showUser')->add(new CheckUsersExistenceMiddleware);

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