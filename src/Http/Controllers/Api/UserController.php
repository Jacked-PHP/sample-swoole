<?php

namespace MyCode\Http\Controllers\Api;

use League\Plates\Engine;
use MyCode\DB\Models\User;
use MyCode\Services\SessionTable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $data = User::all()->map(function(User $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            unset($userData['created_at']);
            unset($userData['updated_at']);
            return $userData;
        });

        $response->getBody()->write(json_encode(['data' => $data]));
        return $response;
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
    }

    public function update(RequestInterface $request, ResponseInterface $response)
    {
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $args)
    {
    }
}