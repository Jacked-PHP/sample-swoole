<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
use MyCode\DB\User;
use MyCode\Services\SessionTable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController
{
    public function login(RequestInterface $request, ResponseInterface $response, $args)
    {
        $templates = new Engine(ROOT_DIR . '/views');
        $response->getBody()->write($templates->render('login', ['message' => '']));
        return $response;
    }

    public function loginHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        global $app;

        $data = $request->getParsedBody();

        // TODO: validation

        $user = current((new User)->get('email', $data['email']));

        // TODO: verify if the user was found

        if (!password_verify($data['password'], $user['password'])) {
            $app->getContainer()->get('logger')->info('Wrong password!');
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate!')
                ->withStatus(302);
        }

        $session_table = SessionTable::getInstance();
        $session_table->set($request->session['id'], [
            'id' => $request->session['id'],
            'user_id' => $user['id'],
        ]);

        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        // TODO: validation

        $session_table = SessionTable::getInstance();
        $session_table->set($request->session['id'], [
            'id' => $request->session['id'],
        ]);

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}