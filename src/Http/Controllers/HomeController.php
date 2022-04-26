<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
use MyCode\DB\User;
use MyCode\Services\SessionTable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeController
{
    public function __construct(
        protected ContainerInterface $container
    ) {}

    public function welcome(RequestInterface $request, ResponseInterface $response, $args)
    {
        $templates = new Engine(ROOT_DIR . '/views');
        $response->getBody()->write($templates->render('view1', ['name' => 'Something else!']));
        return $response;
    }

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

    public function admin(RequestInterface $request, ResponseInterface $response, $args)
    {
        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->session['id']);
        $user = current((new User)->find($session_data['user_id']));

        $templates = new Engine(ROOT_DIR . '/views');
        $response->getBody()->write($templates->render('admin', ['user_name' => $user['name']]));
        return $response;
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

    public function showUsers(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $templates = new Engine(ROOT_DIR . '/views');
        $users = (new User)->getAll();
        $response->getBody()->write($templates->render('view2', ['users' => $users]));
        return $response;
    }

    public function showUser(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->container->get('logger')->info(json_encode($request->session));

        $templates = new Engine(ROOT_DIR . '/views');
        $user = (new User)->find($args['id']);
        $response->getBody()->write($templates->render('view3', ['user' => current($user)]));
        return $response;
    }
}