<?php

namespace MyCode\Http\Middlewares;

use MyCode\Services\SessionTable;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class AuthorizationMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandler $handler)
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $is_login_route = in_array($route->getName(), ['login', 'login-handler']);

        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->getAttribute('session')['id']);

        if (!$is_login_route && !isset($session_data['user_id'])) {
            return (new Response)
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        if ($is_login_route && isset($session_data['user_id'])) {
            return (new Response)
                ->withHeader('Location', '/admin')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
