<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
use MyCode\DB\Models\User;
use MyCode\Services\Events;
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

        $user = User::where('email', $data['email'])->first();

        // TODO: verify if the user was found

        if (!password_verify($data['password'], $user->password)) {
            Events::dispatch(LOGIN_FAILED_EVENT, 'Login failed for email: ' . $data['email']);
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate!')
                ->withStatus(302);
        }

        Events::dispatch(LOGIN_EVENT, json_encode(['user_id' => $user->id]));

        $session_table = SessionTable::getInstance();
        $session_table->set($request->session['id'], [
            'id' => $request->session['id'],
            'user_id' => $user->id,
        ]);

        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        // TODO: validation

        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->session['id']);

        Events::dispatch(LOGOUT_EVENT, json_encode(['user_id' => $session_data['user_id']]));

        $session_table->set($request->session['id'], [
            'id' => $request->session['id'],
        ]);

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}