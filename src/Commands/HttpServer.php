<?php

namespace MyCode\Commands;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HttpServer extends Command
{
    protected static $defaultName = 'http-server';

    protected static $defaultDescription = 'Starts Http Server';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->start($io);

        return Command::SUCCESS;
    }

    private function start(SymfonyStyle $io)
    {
        global $app, $requestConverter;

        $server = new Server("0.0.0.0", 8080);

        $server->on("start", function(Server $server) use ($io) {
            $io->success("HTTP Server ready at http://127.0.0.1:8080");
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
