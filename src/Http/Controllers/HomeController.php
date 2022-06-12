<?php

namespace MyCode\Http\Controllers;

use League\Plates\Engine;
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
        $response->getBody()->write($templates->render('home', ['name' => 'Something else!']));
        return $response;
    }
}