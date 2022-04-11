<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
use MyCode\DB\User;
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