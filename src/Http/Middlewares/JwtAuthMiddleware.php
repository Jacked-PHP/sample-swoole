<?php

namespace MyCode\Http\Middlewares;

use MyCode\Services\JwtToken;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class JwtAuthMiddleware
{
    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $auth = JwtToken::getToken($request);

        if (empty($auth)) {
            $factory = new Psr17Factory();
            $steam = $factory->createStream(json_encode([
                'status' => 'unauthorized',
                'message' => 'Unauthorized Procedure!',
            ]));
            return (new Response)->withBody($steam)->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
