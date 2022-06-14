<?php

namespace MyCode\Http\Middlewares;

use Exception;
use MyCode\DB\User;
use MyCode\Services\Session;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandler $handler)
    {
        $request = $request->withAttribute('session', Session::startSession($request));
        $response = $handler->handle($request);
        Session::addCookiesToResponse($request, $response);
        return $response;
    }
}
