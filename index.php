<?php

require __DIR__ . '/vendor/autoload.php';

use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$psr17Factory = new Psr17Factory;
$requestConverter = new SwooleServerRequestConverter(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$app = new App($psr17Factory);
$app->addRoutingMiddleware();
$app->get('/', function(RequestInterface $request, ResponseInterface $response, $args){
    $templates = new League\Plates\Engine(__DIR__ . '/');
    $response->getBody()->write($templates->render('view1', ['name' => 'Something else!']));
    return $response;
});

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