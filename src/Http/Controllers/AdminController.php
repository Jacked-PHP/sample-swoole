<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
use MyCode\DB\Models\User;
use MyCode\Services\JwtToken;
use MyCode\Services\SessionTable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminController
{
    public function admin(RequestInterface $request, ResponseInterface $response, $args)
    {
        global $app;
        $container = $app->getContainer();

        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->getAttribute('session')['id']);
        $user = User::find($session_data['user_id']);

        $viewData = [
            'user_name' => $user->name,
            'ws_context' => $container->has('ws-context') ?
                array_merge($container->get('ws-context'), [
                    'token' => JwtToken::create(
                        name: uniqid(),
                        userId: $user->id,
                        expire: null,
                        useLimit: 1
                    )->token
                ]) : null,
        ];

        $templates = new Engine(ROOT_DIR . '/views');
        $response->getBody()->write($templates->render('admin', $viewData));
        return $response;
    }
}