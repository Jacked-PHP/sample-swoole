<?php

namespace MyCode\Bootstrap;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use MyCode\Services\Events;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Slim\App as SlimApp;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Swoole\Table;
use Swoole\Timer;

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
        self::events($app);
        $server->start();
    }

    private static function events(SlimApp $app)
    {
        // events table
        $table = new Table(1024);
        $table->column('event_key', Table::TYPE_STRING, 40);
        $table->column('event_data', Table::TYPE_STRING, 250);
        $table->create();
        $app->getContainer()->set('events-table', $table);

        // timer
        Timer::tick(1000, function() use ($table) {
            $daemonEvents = Events::getEvents();

            foreach($table as $key => $event) {
                if (!isset($daemonEvents[$event['event_key']])) {
                    continue;
                }

                foreach ($daemonEvents[$event['event_key']] as $handler) {
                    $handler($event['event_data']);
                }

                $table->del($key);
            }
        });
    }
}