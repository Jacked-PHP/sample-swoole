<?php

namespace MyCode\Bootstrap;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Slim\App as SlimApp;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;

class SwooleServer
{
    public static function start(SlimApp $app, SwooleServerRequestConverter $requestConverter)
    {
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
    }
}