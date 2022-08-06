<?php

namespace MyCode\Commands;

use Exception;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use MyCode\DB\Models\Token;
use MyCode\DB\Models\User;
use MyCode\Services\JwtToken;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WebSocketServer extends Command
{
    protected static $defaultName = 'ws-server';

    protected static $defaultDescription = 'Starts WebSocket Server';

    protected Table $userTable;

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription)
            ->setDefinition([
                new InputOption('port', null, InputOption::VALUE_OPTIONAL, 'Specify the Port for the Websocket Server.', 8080),
                new InputOption('http', null, InputOption::VALUE_NONE, 'Specify that the Websocket Server will also Serve HTTP Requests.'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->start(
            io: new SymfonyStyle($input, $output),
            port: $input->getOption('port'),
            http: $input->getOption('http')
        );

        return Command::SUCCESS;
    }

    private function start(SymfonyStyle $io, int $port, bool $http)
    {
        global $app, $requestConverter;

        $this->startUserTable();

        $app->getContainer()->set('ws-context', [
            'port' => $port,
        ]);

        $server = new Server("0.0.0.0", $port);

        $server->on("start", function(Server $server) use ($io, $http) {
            $io->success("WebSocket Server ready at ws://127.0.0.1:" . $server->port);
            if ($http) {
                $io->info('This server is also serving HTTP requests!');
            }
        });

        if ($http) {
            $server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
                $psr7Request = $requestConverter->createFromSwoole($request);
                $psr7Response = $app->handle($psr7Request);
                $converter = new SwooleResponseConverter($response);
                $converter->send($psr7Response);
            });
        }

        $server->on('open', function(Server $server, Request $request) use ($io) {
            if (
                !isset($request->get['token'])
                || !$this->identifyUser($request->get['token'], $request->fd)
            ) {
                $server->disconnect($request->fd, 1003, 'Unauthorized connection!');
                $io->error('Failed to connect: ' . $request->fd);
                return;
            }

            $io->info('Connection open: ' . $request->fd);
        });

        $server->on('disconnect', function(Server $server, int $fd) {
            if ($this->userTable->exists($fd)) {
                $this->userTable->del($fd);
            }
            // TODO: some further heart beat strategy will be necessary to make sure we clean this table.
        });

        $server->on('message', function(Server $server, Frame $frame) use ($io) {
            $io->info('Message: ' . $frame->data);

            foreach ($server->connections as $fd) {
                if (!$server->isEstablished($fd)) {
                    continue;
                }
                $server->push($fd, json_encode([
                    'user' => $this->userTable->get($frame->fd, 'user_name'),
                    'message' => $frame->data,
                ]));
            }
        });

        $server->set([
            'document_root' => ROOT_DIR . '/public',
            'enable_static_handler' => true,
            'static_handler_locations' => ['/js'],
        ]);

        $server->start();
    }

    private function identifyUser(string $token, int $fd): bool
    {
        global $app;
        $logger = $app->getContainer()->get('logger');

        try {
            $tokenRecord = Token::where('token', $token)->first()->consume();
        } catch (Exception $e) {
            $logger->error('Invalid token: ' . $e->getMessage());
            return false;
        }

        $tokenDecoded = JwtToken::decodeJwtToken($token, $tokenRecord->name);
        if (!isset($tokenDecoded['user_id'])) {
            $logger->error('Decoded token doesn\'t have user id.');
            return false;
        }

        $user = User::find($tokenDecoded['user_id']);
        if (null === $user) {
            $logger->error('User not found: ' . $tokenDecoded['user_id']);
            return false;
        }

        $logger->info('User identified: ' . $tokenDecoded['user_id']);

        return $this->userTable->set($fd, [
            'user_id' => $tokenDecoded['user_id'],
            'user_name' => $user->name,
        ]);
    }

    private function startUserTable()
    {
        // the id of the row will be the "fd"
        $userTable = new Table(1024);
        $userTable->column('user_id', Table::TYPE_INT, 4);
        $userTable->column('user_name', Table::TYPE_STRING, 40);
        $userTable->create();
        $this->userTable = $userTable;
    }
}
